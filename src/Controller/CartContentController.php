<?php

namespace App\Controller;

use App\Entity\CartContent;
use App\Form\CartContentType;
use App\Repository\CartContentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale}/cart/content")
 */
class CartContentController extends AbstractController
{

    /**
     * @Route("/{id}/edit", name="cart_content_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, CartContent $cartContent, TranslatorInterface $translator): Response
    {
        // User will only access his carts
        if ($cartContent->getCart()->getUser() !== $this->getUser() && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            $this->addFlash('error', $translator->trans('flash.notUrCart'));
            return $this->redirectToRoute('cart_index');
        }
        // Can only edit unpaid cart
        if ($cartContent->getCart()->getStatus() === true){
            $this->addFlash('error', $translator->trans('flash.cartAlreadyPurchased'));
            return $this->redirectToRoute('cart_index');
        }
        // Save the intial value of quantity before creating the form
        $initialQuantity = $cartContent->getQuantity();
        // Makd sure the form can add the remaining stock to its total
        $form = $this->createForm(CartContentType::class, $cartContent ,['stock' => $cartContent->getProduct()->getStock() + $cartContent->getQuantity()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Calculate the total before having the product in the cart
            $initialStock = $cartContent->getProduct()->getStock() + $initialQuantity;
            // Update the remaining stock
            $remainingStock = $initialStock - $cartContent->getQuantity();
            dump($remainingStock);
            dump($initialStock);
            dump($initialQuantity);
            dump($cartContent->getQuantity());
            // Update the product with its new stock
            $cartContent->getProduct()->setStock($remainingStock);
            $entityManager = $this->getDoctrine()->getManager();
            // update the product
            $entityManager->persist($cartContent->getProduct());
            $entityManager->flush();
            // Update the CartContent
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success',$translator->trans('flash.cartUpdate'));
            return $this->redirectToRoute('cart_content_edit',['id' => $cartContent->getId()] );
        } elseif ($form->isSubmitted()){
            $this->addFlash('error',$translator->trans('flash.formNotValid'));
        }

        return $this->render('cart_content/edit.html.twig', [
            'cart_content' => $cartContent,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="cart_content_delete", methods={"DELETE"})
     */
    public function delete(Request $request, CartContent $cartContent, TranslatorInterface $translator): Response
    {
        // User will only access his carts
        if ($cartContent->getCart()->getUser() !== $this->getUser()) {
            $this->addFlash('error', $translator->trans('flash.notUrCart'));
            return $this->redirectToRoute('cart_index');
        }
        if ($this->isCsrfTokenValid('delete'.$cartContent->getId(), $request->request->get('_token'))) {
            // delete the cart
            $entityManager = $this->getDoctrine()->getManager();
            // Update the product affected by the deletion of the CartContent
            $updatedProduct = $cartContent->getProduct()->setStock($cartContent->getQuantity() + $cartContent->getProduct()->getStock());
            $entityManager->persist($updatedProduct);
            $entityManager->flush();
            // Remove the cartContent
            $entityManager->remove($cartContent);
            $entityManager->flush();
            $this->addFlash('success',$translator->trans('flash.cartDeleted'));
        }

        return $this->redirectToRoute('cart_index');
    }
}
