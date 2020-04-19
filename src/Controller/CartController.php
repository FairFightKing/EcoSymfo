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
        $cart = $cartRepository->findOneBy(['user' => $this->getUser(), 'Status' => false]);
        if ($cart === null && $this->getUser()){
            $cart = new Cart();
            $cart->setUser($this->getUser());
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
        if ($this->isCsrfTokenValid('delete'.$cart->getId(), $request->request->get('_token'))) {
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
        if ($cart) {
            if ($cart->getStatus() === false) {
                $entityManager = $this->getDoctrine()->getManager();
                $cart->setStatus(true);
                $cart->setPurchasedAt(new \DateTime());
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
