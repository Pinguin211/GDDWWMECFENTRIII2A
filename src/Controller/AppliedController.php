<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Service\CheckerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppliedController extends AbstractController
{
    #[Route('/postuler', name: 'app_applied')]
    public function applied(EntityManagerInterface $entityManager, CheckerInterface $checker): Response
    {
        if (!$checker->checkData($_POST, 'array', ['id']) ||
            !($offer = $entityManager->getRepository(Offer::class)->findOneBy(['id'=>$_POST['id']])))
            return $this->redirectToRoute('app_message', ['title' => 'Erreur 404', 'message' => "Cette page n'existe pas", 'redirect_app' => 'app_annonces']);
        if (!$this->getUser())
            return $this->redirectToRoute('app_message', ['title' => 'Connectez vous', 'message' => "Vous devez être connecter pour postuler"]);
        if (!($candidate = $this->getUser()->getCandidate($entityManager)))
            return $this->redirectToRoute('app_message', ['title' => 'Méfie Toi', 'message' => "Vous devez être un candidat pour postuler"]);
        if (!$candidate->isActivated())
            return $this->redirectToRoute('app_message', ['title' => 'Méfie Toi', 'message' => "Votre profil doit être valider pour postuler"]);
        if ($offer->candidateAlreadyApplied($candidate))
            return $this->redirectToRoute('app_message', ['title' => 'Méfie Toi', 'message' => "Vous ne pouvez pas postuler plusieurs fois à la meme offre", 'redirect_app' => 'app_annonces']);
        $offer->appliedThisOffer($candidate, $entityManager);
        $opt = [
            'title' => 'Candidature soumise',
            'message' => "Votre candidature à bien était reçu elle sera confirmé avant d'être envoyé au recruteur",];
        if (isset($_POST['now_url']) && $_POST['now_url'])
            $opt = array_merge($opt, ['redirect_app' => $_POST['now_url'], 'is_url' => 1]);
        else
            $opt = array_merge($opt, ['redirect_app' => 'app_annonces']);
        return $this->redirectToRoute('app_message', $opt);

    }
}
