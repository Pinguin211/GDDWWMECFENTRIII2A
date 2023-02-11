<?php

namespace App\Controller;

use App\Entity\Location;
use App\Entity\Offer;
use App\Form\OfferType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AddOfferController extends AbstractController
{
    #[Route('/add_offer', name: 'app_add_offer')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {

        $this->redirectToRoute('app_signup');
        if (!($user = $this->getUser()) || !($recruter = $user->getRecruter($entityManager)) || !$recruter->isActivated())
            return $this->redirectToRoute('app_message', ['title' => 'Accés refusé',
                'message' => "Vous n'avez pas les droit requis"]);

        $offer = new Offer();
        $form = $this->createForm(OfferType::class, $offer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $loc_id = $form->get('location_id')->getData();
            $loc_type = $form->get('location_type')->getData();
            if ($loc_type == 1)
            {
                if (($address = $recruter->getAddress()))
                    $loc = new Location($address);
                else
                    return $this->redirectToRoute('app_message', ['title' => 'Completer Profil', 'message' => "Vous devez completer votre profil pour poster cette annonce", 'redirect_app' => 'app_profil']);
            }
            else
                $loc = new Location($entityManager->getRepository(Location::getClassByType($loc_type))->findOneBy(['id' => $loc_id]));
            $entityManager->persist($loc);
            $entityManager->flush();
            $offer->setLocation($loc);
            $offer->setPostDate(new \DateTime());
            $offer->setValidated(false);
            $offer->setArchived(false);
            $offer->setPoster($recruter);
            $entityManager->persist($offer);
            $entityManager->flush();
            return $this->redirectToRoute('app_message', [
                'title' => 'Annonce Soumise',
                'message' => "Votre annonces a était pris en compte, un consultant la validera dans les plus bref délais",
                'redirect_app' => 'app_annonces']);


        }


        return $this->render('add_offer/index.html.twig', [
            'form' => $form,
        ]);
    }
}
