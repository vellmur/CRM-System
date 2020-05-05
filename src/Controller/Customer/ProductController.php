<?php

namespace App\Controller\Customer;

use App\Entity\Customer\Product;
use App\Form\Customer\ProductType;
use App\Manager\MemberEmailManager;
use App\Manager\ProductManager;
use App\Service\Mail\Sender;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends AbstractController
{
    private $manager;

    /**
     * ProductController constructor.
     * @param ProductManager $manager
     */
    public function __construct(ProductManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param $category
     * @param $isPos
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function add(Request $request, PaginatorInterface $paginator, $category, $isPos)
    {
        $client = $this->getUser()->getClient();

        $product = new Product();
        $product->setClient($client);

        // Pre-set category and isPos for empty data
        if (!$request->request->get('product')) {
            $product->setCategory($category);
            $product->setIsPos($isPos);
        }

        $form = $this->createForm(ProductType::class, $product, [
            'client' => $client,
            'isTopForm' => true
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$request->isXmlHttpRequest()) {
                if ($form->isValid()) {
                    $tags = isset($request->request->get('product')['tag']) ? $request->request->get('product')['tag'] : '';
                    $this->manager->createProduct($product, $tags);

                    return $this->redirectToRoute('customer_product_add', [
                        'id' => $product->getId(),
                        'category' => $product->getCategory(),
                        'isPos' => $product->isPos()
                    ]);
                }
            } else {
                return new JsonResponse([
                    'template' => $this->renderView('customer/product/form.html.twig', [
                        'form' => $form->createView()
                    ])
                ]);
            }
        }

        $products = $paginator->paginate($this->manager->searchProducts($client), $request->query->getInt('page', 1), 20);

        $forms = [];

        foreach ($products as $product) {
            $listForm = $this->createForm(ProductType::class, $product, [
                'client' => $client
            ]);

            $listForm->remove('name');
            $listForm->remove('weight');
            $listForm->remove('payByItem');

            array_push($forms, $listForm->createView());
        }

        return $this->render('customer/product/add.html.twig', [
            'form' => $form->createView(),
            'products' => $products,
            'forms' => $forms
        ]);
    }

    /**
     * @param Request $request
     * @param Product $product
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function edit(Request $request, Product $product)
    {
        $client = $this->getUser()->getClient();

        $form = $this->createForm(ProductType::class, $product, [
            'client' => $client,
            'isTopForm' => true
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$request->isXmlHttpRequest()) {
                if ($form->isValid()) {
                    $tags = isset($request->request->get('product')['tag']) ? $request->request->get('product')['tag'] : '';
                    $this->manager->updateProduct($product, $tags);

                    return $this->redirectToRoute('customer_product_edit', ['id' => $product->getId()]);
                }
            } else {
                return new JsonResponse([
                    'template' => $this->renderView('customer/product/form.html.twig', [
                        'form' => $form->createView()
                    ])
                ]);
            }
        }

        return $this->render('customer/product/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product
        ]);
    }

    /**
     * @param Product $product
     * @return JsonResponse
     */
    public function delete(Product $product)
    {
        $this->manager->removeProduct($product);

        return new JsonResponse([
            'redirect' => $this->generateUrl('customer_products_search'),
            'status' => 'success'
        ], 202);
    }

    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param SerializerInterface $serializer
     * @param $category
     * @param $searchText
     * @return JsonResponse|Response
     */
    public function search(Request $request, PaginatorInterface $paginator, SerializerInterface $serializer, $category, $searchText)
    {
        $client = $this->getUser()->getClient();

        $query = $this->manager->searchProducts($client, $category, $searchText);
        $products = $paginator->paginate($query, $request->query->getInt('page', 1), 20);

        $forms = [];

        foreach ($products as $product) {
            $form = $this->createForm(ProductType::class, $product, [
                'client' => $client
            ]);

            $form->remove('name');
            $form->remove('weight');
            $form->remove('payByItem');

            array_push($forms, $form->createView());
        }

        if (!$request->isXMLHttpRequest()) {
            return $this->render('customer/product/search.html.twig', [
                'products' => $products,
                'forms' => $forms
            ]);
        } else {
            $template = $this->render('customer/product/table.html.twig', [
                'products' => $products,
                'forms' => $forms
            ])->getContent();

            return new JsonResponse(
                $serializer->serialize([
                    'template' => $template,
                    'counter' => count($forms)
                ], 'json'), 200);
        }
    }

    /**
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return JsonResponse|Response
     */
    public function searchProducts(Request $request, SerializerInterface $serializer)
    {
        if ($request->isXMLHttpRequest()) {
            $client = $this->getUser()->getClient();
            $products = $this->manager->searchProducts($client,'all' , $request->request->get('search'))->getResult();

            $result = [];

            foreach ($products as $product) {
                $result['names'][] = $product->getName();
                $result['values'][$product->getName()] = $product->getId();
                $result['prices'][$product->getId()] = $product->getPrice();
            }

            return new JsonResponse($serializer->serialize(['products' => $result], 'json'), 200);
        }

        return new Response('Request not valid', 400);
    }

    /**
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param $category
     * @return JsonResponse|Response
     */
    public function pricing(Request $request, SerializerInterface $serializer, $category)
    {
        $client = $this->getUser()->getClient();

        $products = $this->manager->getProductsPricing($client, $category);

        if (!$request->isXMLHttpRequest()) {
            return $this->render('customer/product/pricing.html.twig', [
                'products' => $products,
                'counter' => count($products)
            ]);
        } else {
            $template = $this->render('customer/product/pricing_list.html.twig', ['products' => $products])->getContent();

            return new JsonResponse(
                $serializer->serialize([
                    'template' => $template,
                    'counter' =>  count($products)
                ], 'json'), 200);
        }
    }

    /**
     * @param Request $request
     * @param Product $product
     * @return JsonResponse|Response
     */
    public function updatePrice(Request $request, Product $product)
    {
        if ($request->isXMLHttpRequest()) {
            $price = $request->request->get('pricing')['price'];

            if ($price) {
                $product->setPrice($price);
                $this->manager->flush();

                return new JsonResponse(['status' => 'success'], 202);
            }
        }

        return new Response('Request not valid', 400);
    }

    /**
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Product $product
     * @return JsonResponse|Response
     */
    public function update(Request $request, SerializerInterface $serializer, Product $product)
    {
        if ($request->isXMLHttpRequest()) {
            $form = $this->createForm(ProductType::class, $product, [
                'client' => $product->getClient()
            ]);

            $form->remove('name');
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->manager->flush();

                return new JsonResponse(['code' => 202, 'status' => 'success'], 202);
            } else {
                return new JsonResponse($serializer->serialize(['error' => $form], 'json'), 500);
            }
        }

        return new Response('Request not valid', 400);
    }

    /**
     * @param Request $request
     * @param MemberEmailManager $memberEmailManager
     * @param Sender $sender
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function pricingSend(Request $request, MemberEmailManager $memberEmailManager, Sender $sender)
    {
        $client = $this->getUser()->getClient();
        $pricing = $request->request->get('pricing');
        $products = $this->manager->getProductsPricing($client, $pricing['category']);

        $body = $this->render('customer/product/email_list.html.twig', ['products' => $products]);
        $log = $memberEmailManager->createLog($client, 'Product pricing', $body);

        $recipient = $memberEmailManager->createRecipient($log,null, $pricing['email']);

        $mailer->sendSoftwareMail($recipient, 'customer/product/email_list.html.twig', [
            'products' => $products
        ]);

        return $this->redirectToRoute('products_pricing');
    }
}