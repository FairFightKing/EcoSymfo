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
use Symfony\Contracts\Translation\TranslatorInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/{_locale}/user/{id}", name="user_account")
     */
    public function index(User $user = null, Request $request, GuardAuthenticatorHandler $guardHandler, AppAuthAuthenticator $authenticator, TranslatorInterface $translator)
    {
        // User will only have access to the same ID as his acc id
        if ($user !== $this->getUser()) {
            $this->addFlash('error', $translator->trans('flash.notUrAcc'));
            return $this->redirectToRoute('product_index');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $entityManager = $this->getDoctrine()->getManager();

        if ($form->isSubmitted() && $form->isValid()) {

            $picture = $form->get('Picture')->getData();

            // this condition is needed because the 'Picture' field is not required
            // so the image file must be processed only when a file is uploaded
            if ($picture) {
                $newFilename = uniqid().'.'.$picture->guessExtension();

                // Move the file to the directory where pictures are stored
                try {
                    $picture->move(
                        $this->getParameter('upload_dir'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $this->addFlash('error', $translator->trans('flash.uploadFile'));
                }

                // updates the 'pictureFileName' property to store the image file name
                // instead of its contents
                $user->setPicture($newFilename);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success',$translator->trans('flash.profileUpdated'));
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
