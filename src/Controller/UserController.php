<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use App\Security\AppAuthAuthenticator;

class UserController extends AbstractController
{
    /**
     * @Route("/user/{id}", name="user_account")
     */
    public function index(User $user = null, Request $request, GuardAuthenticatorHandler $guardHandler, AppAuthAuthenticator $authenticator)
    {

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $entityManager = $this->getDoctrine()->getManager();

        if ($form->isSubmitted() && $form->isValid()) {

            $photo = $form->get('Picture')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($photo) {
                $newFilename = uniqid().'.'.$photo->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $photo->move(
                        $this->getParameter('upload_dir'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $this->addFlash('error', "Impossible d'uplaoder le fichier");
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $user->setPicture($newFilename);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }
        $carts = $entityManager->getRepository(Cart::class)->findByUser($user);

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'carts' => $carts,
            'form' => $form->createView()
        ]);
    }
}
