<?php

namespace App\Controller;

use App\Entity\CartContent;
use App\Form\CartContentType;
use App\Repository\CartContentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/cart/content")
 */
class CartContentController extends AbstractController
{
    /**
     * @Route("/", name="cart_content_index", methods={"GET"})
     */
    public function index(CartContentRepository $cartContentRepository): Response
    {
        return $this->render('cart_content/index.html.twig', [
            'cart_contents' => $cartContentRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="cart_content_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $cartContent = new CartContent();
        $form = $this->createForm(CartContentType::class, $cartContent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($cartContent);
            $entityManager->flush();

            return $this->redirectToRoute('cart_content_index');
        }

        return $this->render('cart_content/new.html.twig', [
            'cart_content' => $cartContent,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="cart_content_show", methods={"GET"})
     */
    public function show(CartContent $cartContent): Response
    {
        return $this->render('cart_content/show.html.twig', [
            'cart_content' => $cartContent,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="cart_content_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, CartContent $cartContent): Response
    {
        $form = $this->createForm(CartContentType::class, $cartContent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('cart_content_index');
        }

        return $this->render('cart_content/edit.html.twig', [
            'cart_content' => $cartContent,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="cart_content_delete", methods={"DELETE"})
     */
    public function delete(Request $request, CartContent $cartContent): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cartContent->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($cartContent);
            $entityManager->flush();
        }

        return $this->redirectToRoute('cart_content_index');
    }
}
