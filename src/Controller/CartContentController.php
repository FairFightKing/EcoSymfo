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
     * @Route("/{id}/edit", name="cart_content_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, CartContent $cartContent): Response
    {
        $form = $this->createForm(CartContentType::class, $cartContent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success','Cart updated');
            return $this->redirectToRoute('cart_content_index');
        } elseif ($form->isSubmitted()){
            $this->addFlash('error','form not valid');
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
            $this->addFlash('success','cart deleted');
        }

        return $this->redirectToRoute('cart_index');
    }
}
