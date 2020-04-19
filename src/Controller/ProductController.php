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

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/", name="product_index", methods={"GET","POST"})
     */
    public function index(ProductRepository $productRepository, Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $picture = $form->get('Picture')->getData();

            if ($picture) {
                $newFilename = uniqid().'.'.$picture->guessExtension();

                try {
                    $picture->move(
                        $this->getParameter('upload_dir'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', "Unable to upload file");
                }
                $product->setPicture($newFilename);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash('success','created new product');

            return $this->redirectToRoute('product_index');
        }elseif ($form->isSubmitted()){
            $this->addFlash('error','form not valid');
        }
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/new", name="product_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $picture = $form->get('Picture')->getData();

            if ($picture) {
                $newFilename = uniqid().'.'.$picture->guessExtension();

                try {
                    $picture->move(
                        $this->getParameter('upload_dir'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', "Unable to upload file");
                }
                $product->setPicture($newFilename);
            }


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash('success','created new product');

            return $this->redirectToRoute('product_index');
        }elseif($form->isSubmitted()){
            $this->addFlash('error','form not valid');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_show", methods={"GET","POST"})
     */
    public function show(Product $product, Request $request): Response
    {
        $cartContent = new CartContent();
        $form = $this->createForm(CartContentType::class, $cartContent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $cart = $entityManager->getRepository(Cart::class)->findOneBy(['user' => $this->getUser(), 'Status' => false]);
            if ($cart === null && $this->getUser()){
                $cart = new Cart();
                $cart->setUser($this->getUser());
                $entityManager->persist($cart);
                $entityManager->flush();
            }
            $cartContent->setCart($cart);
            $cartContent->setAddedAt(new \DateTime());
            $cartContent->setProduct($product);
            $entityManager->persist($cartContent);
            $entityManager->flush();

            $this->addFlash('success', 'Product Added to cart.');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Form is not valid.');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="product_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success','Product updated');
            return $this->redirectToRoute('product_index');
        }elseif($form->isSubmitted()){
            $this->addFlash('error','form not valid');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($product);
            $entityManager->flush();
            $this->addFlash('success','Product deleted');
        }

        return $this->redirectToRoute('product_index');
    }
}
