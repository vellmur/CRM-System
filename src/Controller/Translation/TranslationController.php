<?php

namespace App\Controller\Translation;

use App\Entity\Translation\TranslationDomain;
use App\Entity\Translation\TranslationKey;
use App\Entity\Translation\TranslationLocale;
use App\Form\Translation\AddKeyType;
use App\Form\Translation\AddTranslationType;
use App\Form\Translation\KeysCollection;
use App\Form\Translation\TranslationCollection;
use App\Manager\TranslationManager;
use App\Service\CommandRunner;
use App\Service\CountryList;
use App\Service\Translation\DbLoader;
use App\Service\Translation\TranslationService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TranslationController extends AbstractController
{
    private $manager;

    private $service;

    private $countryList;

    /**
     * TranslationController constructor.
     * @param TranslationManager $manager
     * @param TranslationService $service
     * @param CountryList $countryList
     */
    public function __construct(TranslationManager $manager, TranslationService $service, CountryList $countryList)
    {
        $this->manager = $manager;
        $this->service = $service;
        $this->countryList = $countryList;
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function list(Request $request)
    {
        $existingLanguages = $this->countryList->getLanguagesByLocales($this->manager->getExistingLocales());
        $translations = $this->countryList->getNonExistentTranslations($existingLanguages);

        $form = $this->createForm(AddTranslationType::class, null, ['translations' => $translations]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $locale = $form->get('translation')->getData();
                $fileCreated = $this->service->createTranslationFiles($locale, $this->manager->getTranslationDomains());

                if (false === $fileCreated) {
                    throw new \Exception('Translation file was not created and not found.');
                }

                $translationCreated = $this->manager->createTranslations($locale);

                if (false === $translationCreated) {
                    throw new \Exception('Translation database records was not created and not found.');
                }

                return $this->redirectToRoute('master_translation');
            } catch (\Throwable $throwable) {
                $error = $throwable->getMessage() . ' in file ' . $throwable->getFile() . ' on line ' . $throwable->getLine();
                $form->get('translation')->addError(new FormError($error));
            }
        }

        $domains = $this->manager->getTranslationDomains();
        $sharedTranslations = $this->manager->getSharedTranslations();

        return $this->render('master/translation/edit_translations.html.twig', [
            'form' => $form->createView(),
            'languages' => $existingLanguages,
            'domains' => $domains,
            'sharedTranslations' => $sharedTranslations
        ]);
    }

    /**
     * @ParamConverter("locale", class="App\Entity\Translation\TranslationLocale",
     *     options={"mapping": {"locale" = "code"}}
     * )
     * @ParamConverter("domain", class="App\Entity\Translation\TranslationDomain",
     *     options={"mapping": {"domain" = "domain"}}
     * )
     * @param Request $request
     * @param AuthorizationCheckerInterface $checker
     * @param DbLoader $dbLoader
     * @param TranslationLocale $locale
     * @param TranslationDomain $domain
     * @param CommandRunner $commandRunner
     * @return Response
     */
    public function translate(
        Request $request,
        AuthorizationCheckerInterface $checker,
        DbLoader $dbLoader,
        TranslationLocale $locale,
        TranslationDomain $domain,
        CommandRunner $commandRunner
    ) {
        if ($locale->getCode() == 'en' && $checker->isGranted('ROLE_ADMIN') == false) {
            throw new AccessDeniedException();
        }

        $translations = $this->manager->loadTranslations($locale, $domain);
        $form = $this->createForm(TranslationCollection::class, ['translations' => $translations]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->updateTranslations($locale, $domain, $form->getData());

            // Clear cache in order to update translation cache
            $commandRunner->runCommand('app:cache:clear', [
                true,
                'translations'
            ]);

            return $this->redirect($request->getUri());
        }

        $language = $this->countryList->getLanguageByLocale($locale->getCode());
        $originalLabels = $dbLoader->load(null, 'en', $domain->getDomain())->all()[$domain->getDomain()];

        $template = $checker->isGranted('ROLE_ADMIN') === true
            ? 'master/translation/translation.html.twig'
            : 'account/profile/translation/translate.html.twig';

        return $this->render($template, [
            'form' => $form->createView(),
            'language' => $language,
            'originalLabels' => $originalLabels
        ]);
    }

    /**
     * @param AuthorizationCheckerInterface $checker
     * @return Response|AccessDeniedException
     */
    public function keys(AuthorizationCheckerInterface $checker)
    {
        if (false === $checker->isGranted('ROLE_ADMIN')) {
            return new AccessDeniedException();
        }

        $domains = $this->manager->getTranslationDomains();

        return $this->render('master/translation/keys.html.twig', [
            'domains' => $domains
        ]);
    }

    /**
     * @ParamConverter("domain", class="App\Entity\Translation\TranslationDomain",
     *     options={"mapping": {"domain" = "domain"}}
     * )
     *
     * @param Request $request
     * @param AuthorizationCheckerInterface $checker
     * @param $domain
     * @return RedirectResponse|Response|AccessDeniedException
     */
    public function editKeys(Request $request, AuthorizationCheckerInterface $checker, TranslationDomain $domain)
    {
        if (false === $checker->isGranted('ROLE_ADMIN')) {
            return new AccessDeniedException();
        }

        $key = new TranslationKey();
        $key->setDomain($domain);

        $form = $this->createForm(AddKeyType::class, $key);
        $form->handleRequest($request);

        // Add new key
        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->manager->addTranslationKey($key, $form->get('translation')->getData());

            if ($result instanceof \Exception) {
                $form->get('key')->addError(new FormError($result->getMessage()));
            } else {
                return $this->redirectToRoute('master_translation_edit_keys', [
                    'domain' => $domain->getDomain()
                ]);
            }
        }

        $keys = $this->manager->loadKeys($domain);
        $keysForm = $this->createForm(KeysCollection::class, [
            'keys' => $keys
        ]);

        return $this->render('master/translation/edit_keys.html.twig', [
            'form' => $form->createView(),
            'keysForm' => $keysForm->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param AuthorizationCheckerInterface $checker
     * @param TranslationKey $key
     * @return JsonResponse|AccessDeniedException
     * @throws \Exception
     */
    public function updateKey(Request $request, AuthorizationCheckerInterface $checker, TranslationKey $key)
    {
        if (false === $checker->isGranted('ROLE_ADMIN') || $request->getMethod() !== 'POST') {
            return new AccessDeniedException();
        }

        $key->setKey($request->request->get('key'));
        $this->manager->updateTranslationKey($key, $request->request->get('translation'));

        return new JsonResponse();
    }

    /**
     * @param AuthorizationCheckerInterface $checker
     * @param Request $request
     * @param TranslationKey $key
     * @return JsonResponse|AccessDeniedException
     * @throws \Exception
     */
    public function deleteKey(AuthorizationCheckerInterface $checker, Request $request, TranslationKey $key)
    {
        if (false === $checker->isGranted('ROLE_ADMIN') || $request->getMethod() !== 'DELETE') {
            return new AccessDeniedException();
        }

        $this->manager->removeTranslationKey($key);

        return new JsonResponse();
    }

    /**
     * * @ParamConverter("locale", class="App\Entity\Translation\TranslationLocale",
     *     options={"mapping": {"locale" = "code"}}
     * )
     * @ParamConverter("domain", class="App\Entity\Translation\TranslationDomain",
     *     options={"mapping": {"domain" = "domain"}}
     * )
     *
     * @param Request $request
     * @param AuthorizationCheckerInterface $checker
     * @param TranslationLocale $locale
     * @param TranslationDomain $domain
     * @return JsonResponse|AccessDeniedException
     */
    public function shareTranslation(
        Request $request,
        AuthorizationCheckerInterface $checker,
        TranslationLocale $locale, TranslationDomain $domain
    ) {
        if (false === $checker->isGranted('ROLE_ADMIN') || $request->getMethod() !== 'POST') {
            return new AccessDeniedException();
        }

        $isShared = filter_var($request->request->get('isShared'), FILTER_VALIDATE_BOOLEAN); ;
        $this->manager->shareTranslation($locale, $domain, $isShared);

        return new JsonResponse();
    }

    /**
     * @return Response
     */
    public function userTranslations()
    {
        $languages = $this->countryList->getLocales();
        $sharedTranslations = $this->manager->getSharedTranslation();

        return $this->render('account/profile/translation/translation.html.twig', [
            'languages' => $languages,
            'translations' => $sharedTranslations
        ]);
    }
}