<?php

namespace App\Controller\Master;

use App\Entity\Master\Post;
use App\Form\Master\PostType;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends AbstractController
{
    private $em;

    /**
     * BlogController constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @return RedirectResponse|Response
     */
    public function index()
    {
        $posts = $this->em->getRepository(Post::class)->findBy(['isActive' => true], ['createdAt' => 'DESC']);

        return $this->render('home/blog/blog.html.twig', [
            'posts' => $posts,
            'bodyClasses' => 'collection-type-blog'
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function updateForm()
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        $formTemplate = $this->renderView('blog/post_form.html.twig', [
            'form' => $form->createView()
        ]);

        return new JsonResponse(['form' => $formTemplate]);
    }

    /**
     * @return RedirectResponse|Response
     */
    public function list()
    {
        $posts = $this->em->getRepository(Post::class)->findBy([], ['createdAt' => 'DESC']);

        return $this->render('master/blog/list.html.twig', [
            'posts' => $posts,
            'bodyClasses' => 'collection-type-blog'
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        // If request is created in order to get updated form
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            return new JsonResponse($this->renderView('blog/post_form.html.twig', [
                'form' => $form->createView()
            ]));
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = new Slugify();
            $post->setSlug($slug->slugify($post->getTitle(), ['lowercase' => false]));

            $this->em->persist($post);
            $this->em->flush();

            return $this->redirectToRoute('master_blog');
        }

        return $this->render('master/blog/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param Post $post
     * @return Response
     */
    public function edit(Request $request, Post $post)
    {
        $form = $this->createForm(PostType::class, $post);

        // If request is created in order to get updated form
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            return new JsonResponse($this->renderView('blog/post_form.html.twig', [
                'form' => $form->createView()
            ]));
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = new Slugify();
            $post->setSlug($slug->slugify($post->getTitle(), ['lowercase' => false]));

            $this->em->persist($post);
            $this->em->flush();

            return $this->redirectToRoute('master_post_edit', ['id' => $post->getId()]);
        }

        return $this->render('master/blog/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param $slug
     * @return Response
     */
    public function show($slug)
    {
        $post = $this->em->getRepository(Post::class)->findOneBy(['slug' => $slug]);

        if (!$post) {
            return new Response('Post was not found!', 404);
        }

        return $this->render('home/blog/post.html.twig', [
            'post' => $post,
            'bodyClasses' => 'collection-type-blog'
        ]);
    }

    /**
     * @param Request $request
     * @param Post $post
     * @return Response
     */
    public function delete(Request $request, Post $post)
    {
        if ($request->isXMLHttpRequest()) {
            try {
                $this->em->remove($post);
                $this->em->flush();

                return new JsonResponse(['code' => 202, 'status' => 'success'], 202);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => $e], 'json', 500);
            }
        }

        return new Response('Request not valid', 400);
    }
}