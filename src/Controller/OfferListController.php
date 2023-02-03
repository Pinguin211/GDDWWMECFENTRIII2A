<?php

namespace App\Controller;

use App\Entity\Offer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class OfferListController extends AbstractController
{
    #[Route('/annonces', name: 'app_annonces')]
    public function index(AuthenticationUtils $authenticationUtils, EntityManagerInterface $entityManager): Response
    {
        //Login form
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastEmail = $authenticationUtils->getLastUsername();

        //GetOffeer
        $offers = $entityManager->getRepository(Offer::class)->findBy([],[],10);


        return $this->render('offer_list/index.html.twig', [
            'error' => $error,
            'last_email' => $lastEmail,
            'offers' => $offers,
            'entitymanager' => $entityManager,
        ]);
    }
}
