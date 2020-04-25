<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SuperAdminController extends AbstractController
{
    /**
     * @Route("/{_locale}/superadmin", name="super_admin")
     */
    public function index()
    {
        // Just to make sure noone else get here
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        $entityManager = $this->getDoctrine()->getManager();
        // Request all the Users and ordered by ID wich the higher equal the sooner
        $users = $entityManager->getRepository(User::class)->findBy([],['id' => 'DESC']);
        // Request all the unpaid carts
        $unpaidCarts = $entityManager->getRepository(Cart::class)->findBy(['Status' => false]);

        return $this->render('super_admin/index.html.twig', [
            'users' => $users,
            'unpaidCarts' => $unpaidCarts,
            'controller_name' => 'SuperAdminController',
        ]);
    }
}
