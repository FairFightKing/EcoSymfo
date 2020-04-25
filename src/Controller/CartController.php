<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Form\CartType;
use App\Repository\CartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale}/cart")
 */
class CartController extends AbstractController
{
    /**
     * @Route("/", name="cart_index", methods={"GET"})
     */
    public function index(CartRepository $cartRepository): Response
    {
        // Request the cart that is not paid yet by this user
        $cart = $cartRepository->findOneBy(['user' => $this->getUser(), 'Status' => false]);
        // If theres none, create a new empty one
        if ($cart === null && $this->getUser()){
            $cart = new Cart();
            //link the user to the new cart
            $cart->setUser($this->getUser());
            // save the cart
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($cart);
            $entityManager->flush();
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    /**
     * @Route("/{id}", name="cart_show", methods={"GET"})
     */
    public function show(Cart $cart): Response
    {
        return $this->render('cart/show.html.twig', [
            'cart' => $cart,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="cart_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Cart $cart, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(CartType::class, $cart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // update the cart
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success',$translator->trans('flash.cartUpdated'));
            return $this->redirectToRoute('cart_index');
        } elseif ($form->isSubmitted()){
            $this->addFlash('error',$translator->trans('flash.formNotValid'));
        }

        return $this->render('cart/edit.html.twig', [
            'cart' => $cart,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="cart_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Cart $cart, TranslatorInterface $translator): Response
    {
        // Verification that only a user can delete a cart
        $this->denyAccessUnlessGranted('ROLE_USER');
        if ($this->isCsrfTokenValid('delete'.$cart->getId(), $request->request->get('_token'))) {
            // remove the cart
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($cart);
            $entityManager->flush();
            $this->addFlash('success',$translator->trans('flash.formNotValid'));
        }

        return $this->redirectToRoute('cart_index');
    }

    /**
     * @Route("/purchase/{id}", name="cart_purchase",methods={"GET"})
     */
    public function purchase(Cart $cart = null, TranslatorInterface $translator): Response
    {
        // If a cart exist and if it unpaid
        if ($cart) {
            if ($cart->getStatus() === false) {
                $entityManager = $this->getDoctrine()->getManager();
                // change the status of the cart to be paid and the time its been paid and update database
                $cart->setStatus(true);
                $cart->setPurchasedAt(new \DateTime());
                // save the changes
                $entityManager->persist($cart);
                $entityManager->flush();
                $this->addFlash('success', $translator->trans('flash.purchaseOk'));

            } else {
                $this->addFlash('error', $translator->trans('flash.cartAlreadyPurchased'));
            }
        } else {
            $this->addFlash('error', $translator->trans('flash.notACart'));
        }

        return $this->redirectToRoute('cart_index');
    }
}
