<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartContent;
use App\Entity\Product;
use App\Form\CartContentType;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale}/product")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/", name="product_index", methods={"GET","POST"})
     */
    public function index(ProductRepository $productRepository, Request $request, TranslatorInterface $translator): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $this->isGranted('ROLE_ADMIN')) {
            $picture = $form->get('Picture')->getData();
            // this condition is needed because the 'Picture' field is not required
            // so the image file must be processed only when a file is uploaded
            // if the picture field is filled, rename the file and move it to the right directory
            if ($picture) {
                $newFilename = uniqid().'.'.$picture->guessExtension();
                // Move the file to the directory where pictures are stored

                try {
                    $picture->move(
                        $this->getParameter('upload_dir'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error',$translator->trans('flash.uploadFile'));
                }
                // updates the 'pictureFileName' property to store the image file name
                // instead of its contents
                $product->setPicture($newFilename);
            }
            // save the product
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash('success',$translator->trans('flash.newProduct'));

            return $this->redirectToRoute('product_index');
        }elseif ($form->isSubmitted()){
            $this->addFlash('error',$translator->trans('flash.formNotValid'));
        }
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_show", methods={"GET","POST"})
     */
    public function show(Product $product, Request $request, TranslatorInterface $translator): Response
    {
        $cartContent = new CartContent();
        $form = $this->createForm(CartContentType::class, $cartContent);
        $entityManager = $this->getDoctrine()->getManager();
        if ($this->getUser()) {
            // Request the unpaid cart form the user
            $cart = $entityManager->getRepository(Cart::class)->findOneBy(['user' => $this->getUser(), 'Status' => false]);
            // Verify that we got a cart
            if ($cart !== null) {
                // Request the product inside that unpaidcart
                $hasProductInUnpaidCart = $entityManager->getRepository(CartContent::class)->findOneBy(['Cart' => $cart, 'Product' => $product]);
                // If we found the product inside the cart
                if ($hasProductInUnpaidCart !== null) {
                    // Change form to update the Cart
                    $form = $this->createForm(CartContentType::class, $hasProductInUnpaidCart);
                    $updateCart = true;
                } else {
                    $updateCart = false;
                }
            }
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($hasProductInUnpaidCart !== null) {
                $this->getDoctrine()->getManager()->flush();
            } else{
                // If theres none, create a new empty one
                if ($cart === null && $this->getUser()){
                    $cart = new Cart();
                    $cart->setUser($this->getUser());
                    $entityManager->persist($cart);
                    $entityManager->flush();
                }
                // Create a new row with the corresponding fields
                $cartContent->setCart($cart);
                $cartContent->setAddedAt(new \DateTime());
                $cartContent->setProduct($product);
                // save the cart content
                $entityManager->persist($cartContent);
                $entityManager->flush();

                $this->addFlash('success', $translator->trans('flash.productAdd'));
            }
            $this->addFlash('success', $translator->trans('flash.cartUpdate'));

        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', $translator->trans('flash.formNotValid'));
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'updateCart' => $updateCart,
            'cart_content' => $hasProductInUnpaidCart,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="product_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Product $product, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // update the product
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success',$translator->trans('flash.ProductUpdated'));
            return $this->redirectToRoute('product_index');
        }elseif($form->isSubmitted()){
            $this->addFlash('error',$translator->trans('flash.formNotValid'));
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Product $product, TranslatorInterface $translator): Response
    {
        // Verification that only an admin can delete a product
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            // delete the product
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($product);
            $entityManager->flush();
            $this->addFlash('success',$translator->trans('flash.productDeleted'));
        }

        return $this->redirectToRoute('product_index');
    }
}
