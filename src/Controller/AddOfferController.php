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
    #[Route('/ajouter_annonce', name: 'app_add_offer')]
    public function add_offer(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!($user = $this->getUser()) || !($recruter = $user->getRecruter($entityManager)) || !$recruter->isActivated())
            return $this->redirectToRoute('app_message', ['title' => 'Accés refusé',
                'message' => "Vous n'avez pas les droit requis"]);

        $offer = new Offer();
        $form = $this->createForm(OfferType::class, $offer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $loc_id = (int)$form->get('location_id')->getData();
            $loc_type = (int)$form->get('location_type')->getData();
            if ($loc_type === 1)
            {
                if (($address = $recruter->getAddress()))
                    $loc = new Location($address);
                else
                    return $this->redirectToRoute('app_message', ['title' => 'Completer Profil', 'message' => "Vous devez completer votre profil pour poster cette annonce", 'redirect_app' => 'app_profil']);
            }
            else
            {
                if (($type_loc = $entityManager->getRepository(Location::getClassByType($loc_type))->findOneBy(['id' => $loc_id])))
                    $loc = new Location($type_loc);
                else
                    return $this->redirectToRoute('app_message', ['title' => 'Erreur Data', 'message' => "Recharger le formulaire.", 'redirect_app' => 'app_add_offer']);
            }
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
                'message' => "Votre annonces a était pris en compte, un consultant la validera dans les plus bref délais,
                attention une fois l'annonce validé il n'est plus possible de la modifier.",
                'redirect_app' => 'app_annonces']);
        }


        return $this->render('add_offer/index.html.twig', [
            'form' => $form,
            'pageTitle' => 'Ajouter une annonce',
            'button' => 'Soumettre'
        ]);
    }


    #[Route('/modifier_annonce', name: 'app_mod_offer')]
    public function mod_offer(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!isset($_GET['id']) ||
            !($offer = $entityManager->getRepository(Offer::class)->findOneBy(['id' => $_GET['id']])) || //Si l'offre existe
            !($user = $this->getUser()) || //Si l'user est bien connecté
            !($recruter = $user->getRecruter($entityManager)) || //Si c'est bien un recruteur
            !$recruter->isActivated() || //Si son compte est bien activé
            $offer->getPoster()->getId() !== $recruter->getId() || //Si l'annonce lui appartient
            $offer->isValidated()) //Si l'annonce est validé
            return $this->redirectToRoute('app_message', ['title' => 'Accés refusé',
                'message' => "Vous n'avez pas les droit requis"]);

        $form = $this->createForm(OfferType::class, $offer);
        $form->handleRequest($request);
        $location = $offer->getLocation();
        if (!$form->isSubmitted())
            $form->get('location_type')->setData($location->getType());
        if ($form->isSubmitted() && $form->isValid())
        {
            $loc_id = $form->get('location_id')->getData();
            $loc_type = $form->get('location_type')->getData();
            if ((int)$loc_id !== $location->getTypeId() || (int)$loc_type !== $location->getType())
            {
                $location->setType((int)$loc_type);
                $location->setTypeId((int)$loc_id);
            }
            $entityManager->persist($offer);
            $entityManager->flush();
            return $this->redirectToRoute('app_message', [
                'title' => 'Annonce Modifié',
                'message' => "Votre annonces a était pris en compte, un consultant la validera dans les plus bref délais,
                attention une fois l'annonce validé il n'est plus possible de la modifier.",
                'redirect_app' => 'app_annonces']);
        }
        return $this->render('add_offer/index.html.twig', [
            'form' => $form,
            'pageTitle' => 'Modifier une annonce',
            'button' => 'Modifier'
        ]);
    }
}
