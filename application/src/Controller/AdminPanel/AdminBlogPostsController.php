<?php

namespace App\Controller\AdminPanel;

use App\Entity\BlogPosts;
use App\Form\FormBlogPostsManageType;
use App\Repository\BlogPostsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminBlogPostsController extends AbstractController
{
    /**
     * Show all blog posts
     *
     * @param BlogPostsRepository $blogPostsRepository
     *
     * @return Response
     */
    public function blogPosts(BlogPostsRepository $blogPostsRepository): Response
    {
        return $this->render('adminPanel/blog/adminBlogPosts.html.twig', [
            'page_name' => 'Blog Posts',
            'blog_posts' => $blogPostsRepository->findAll()
        ]);
    }

    /**
     * Blog post delete
     *
     * @param integer $blogPostId
     * @param BlogPostsRepository $blogPostsRepository
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     */
    public function blogPostsDelete(
        int $blogPostId,
        BlogPostsRepository $blogPostsRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $entityManager->remove($blogPostsRepository->find($blogPostId));
        $entityManager->flush();

        return $this->redirectToRoute(
            'blogPosts'
        );
    }

    /**
     * Edit & Manage Blog Posts
     *
     * @param BlogPostsRepository $blogPostsRepository
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param integer|null $blogPostId
     * @param ValidatorInterface $validator
     *
     * @return Response
     */
    public function blogPostsManage(
        BlogPostsRepository $blogPostsRepository,
        EntityManagerInterface $entityManager,
        Request $request,
        int $blogPostId = null,
        ValidatorInterface $validator
    ): Response {
        if ($blogPostId) {
            $blogPostsRepository->find($blogPostId)
            ?? throw $this->createNotFoundException('The Blog Post does not exist');
        }

        $blogPost = $blogPostId ? $blogPostsRepository->find($blogPostId) : new BlogPosts();

        $blogPostForm = $this->createForm(FormBlogPostsManageType::class, $blogPost);
        $blogPostForm->handleRequest($request);
        if ($blogPostForm->isSubmitted() && $blogPostForm->isValid()) {
            $blogPostData = $blogPostForm->getData();

            $entityManager->persist($blogPostData);
            $entityManager->flush();

            return $this->redirectToRoute(
                'blogPosts'
            );
        }

        if ($blogPostForm->isSubmitted() && !$blogPostForm->isValid()) {
            $errors = $validator->validate($blogPostForm->getData());
            if (count($errors) > 0) {
                $errorMessages = $errors;
            }
        }

        return $this->render('adminPanel/blog/adminBlogPostsManage.html.twig', [
            'page_name' => 'Blog Posts Manage',
            'form'      => $blogPostForm,
            'errors'    => $errorMessages ?? null
        ]);
    }
}
