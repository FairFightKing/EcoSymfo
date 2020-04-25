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
    public function show(Cart $cart = null, TranslatorInterface $translator): Response
    {
        if ($cart) {
            // User will only access his carts
            if ($cart->getUser() !== $this->getUser() && !$this->isGranted('ROLE_SUPER_ADMIN')) {
                $this->addFlash('error', $translator->trans('flash.notUrCart'));
                return $this->redirectToRoute('cart_index');
            }
            return $this->render('cart/show.html.twig', [
                'cart' => $cart,
            ]);
        } else {
            $this->addFlash('error', $translator->trans('cart.empty'));
            return $this->redirectToRoute('cart_index');
        }
    }

    /**
     * @Route("/{id}", name="cart_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Cart $cart, TranslatorInterface $translator): Response
    {
        // User will only have access to the same cart as his account carts
        if ($cart->getUser() !== $this->getUser() && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            $this->addFlash('error', $translator->trans('flash.notUrCart'));
            return $this->redirectToRoute('cart_index');
        }
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
            if ($cart->getStatus() === false && $cart->getUser() === $this->getUser()) {
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
