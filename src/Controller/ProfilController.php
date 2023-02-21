<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Admin;
use App\Entity\AppliedCandidate;
use App\Entity\Candidate;
use App\Entity\City;
use App\Entity\Consultant;
use App\Entity\Offer;
use App\Entity\Recruter;
use App\Entity\User;
use App\Service\CheckerInterface;
use App\Service\PathInterface;
use App\Service\RolesInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{

    private const KEY_RECRUTER = 'recruter';
    private const KEY_CANDIDATE = 'candidate';
    private const KEY_USER = 'user';
    private const KEY_ADMIN = 'admin';
    private const KEY_CONSULTANT = 'consultant';


    #[Route('/profil', name: 'app_profil')]
    public function index(): Response
    {
        if (!($user = $this->getUser()))
            return $this->redirectToRoute('app_message', ['title' => 'Connecter Vous', 'message' => "Vous devez être connecter pour accéder a cette page"]);

        $user_info = ['id' => $user->getId(), 'password' => $user->getPassword(), 'roles' => $user->getRoles()];

        return $this->render('profil/index.html.twig', [
            'user_info' => json_encode($user_info),
        ]);
    }

    ////////////////////////////////////////////////////////////////
    ///  ONGLET PROFIL

    #[Route('/profil_get_user')]
    public function getUserInformation(EntityManagerInterface $entityManager, CheckerInterface $checker): Response
    {
        try {
            if (!$checker->checkData($_POST, 'array', ['id', 'password']))
                throw new Exception('Error Data', 422);
            $user = self::getUserByIdPassword($_POST['id'], $_POST['password'], $entityManager);
            return new JsonResponse(json_encode(self::getProfiles($user, $entityManager)));
        } catch (Exception $exception) {
            return new Response($exception->getMessage(), $exception->getCode());
        }
    }
    private static function getProfiles(User $user, EntityManagerInterface $entityManager)
    {
        return [
            self::KEY_USER => $user->getValueAsArray(),
            self::KEY_CANDIDATE => self::getProfile($user, $entityManager, 'Candidate'),
            self::KEY_RECRUTER => self::getProfile($user, $entityManager, 'Recruter'),
        ];

    }
    private static function getProfile(User $user, EntityManagerInterface $entityManager, string $profile)
    {
        $func = "get$profile";
        $profile = $user->$func($entityManager);
        if (!$profile)
            return $profile;
        else
            return $profile->getValueAsArray();
    }

    #[Route('/profil_update_cv')]
    public function updateCv(EntityManagerInterface $entityManager, CheckerInterface $checker, PathInterface $path)
    {
        try {
            if (!$checker->checkData($_FILES, 'array', ['file']))
                throw new Exception('Pas de cv transmit', 204);
            if (!$checker->checkData($_POST, 'array', ['id', 'password']))
                throw new Exception('Error Data', 422);
            if (!$checker->checkUploadedFile($_FILES['file'], 2000000, ['.pdf'], ['application/pdf']))
                throw new Exception("Le fichier n'est pas au bon format", 422);
            $candidate = self::getRoledUserByPassWord(RolesInterface::ROLE_CANDIDATE, $_POST['id'], $_POST['password'], $entityManager);
            move_uploaded_file($_FILES['file']['tmp_name'], $path->getUserCvDirPath() . $candidate->getId() . '.pdf');
            $candidate->setCvId($candidate->getId());
            $entityManager->flush();
            return new Response("CV bien mis à jour", 200);
        } catch (Exception $exception) {
            return new Response($exception->getMessage(),$exception->getCode());
        }
    }


    #[Route('/profil_update_candidate_recruter')]
    public function updateCandidateRecruter(EntityManagerInterface $entityManager, CheckerInterface $checker)
    {
        try {
            if (!$checker->checkData($_POST, 'array', ['id', 'password', 'info']))
                throw new Exception('Error Data', 422);
            $user = self::getUserByIdPassword($_POST['id'], $_POST['password'], $entityManager);
            $info = json_decode($_POST['info'], true);
            if (!$checker->checkData($info, 'array', [self::KEY_RECRUTER, self::KEY_CANDIDATE]))
                throw new Exception('Error Data 1', 422);
            if (self::updateUserData($user, $entityManager, $info, $checker))
                return new Response('Données bien mis à jours', 200);
            else
                return new Response('Pas de données mettre à jours', 200);
        } catch (Exception $exception) {
            return new Response($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @throws Exception
     */
    private static function updateUserData(User $user, EntityManagerInterface $entityManager, array $info, CheckerInterface $checker): bool
    {
        $up = 0;
        if (($recruter = $user->getRecruter($entityManager))) {
            if (!$checker->checkData($info[self::KEY_RECRUTER], 'array', [Recruter::KEY_ADDRESS, Recruter::KEY_COMPANY_NAME]))
                throw new Exception('Error Data 2', 422);
            $up += self::updateRecruter($recruter, $info[self::KEY_RECRUTER], $entityManager, $checker);
        }
        if (($candidate = $user->getCandidate($entityManager))) {
            if (!$checker->checkData($info[self::KEY_CANDIDATE], 'array', [Candidate::KEY_FIRST_NAME, Candidate::KEY_LAST_NAME]))
                throw new Exception('Error Data 3', 422);
            $up += self::updateCandidate($candidate, $info[self::KEY_CANDIDATE], $checker);
        }
        if ($up > 0) {
            $entityManager->flush();
            return true;
        } else
            return false;
    }

    /**
     * @throws Exception
     */
    private static function updateRecruter(Recruter $recruter, array $info, EntityManagerInterface $entityManager, CheckerInterface $checker): int
    {
        //Check des valeurs reçues
        if (!$checker->checkData($info[Recruter::KEY_COMPANY_NAME], 'string'))
            throw new Exception("Nom de l'entreprise, incomplet ou erroné, données non mis à jours", 422);
        if (!$checker->checkData(($address = $info[Recruter::KEY_ADDRESS]), 'array', [Address::KEY_STREET_NAME, Address::KEY_NUMBER, Address::KEY_CITY_ID]) ||
            !$checker->checkData($address[Address::KEY_NUMBER], 'numeric') ||
            !$checker->checkData($address[Address::KEY_CITY_ID], 'numeric') ||
            !$checker->checkData($address[Address::KEY_STREET_NAME], 'string'))
            throw new Exception("Adresse incomplete ou erroné, données non mis à jours", 422);
        if (!($new_city = $entityManager->getRepository(City::class)->findOneBy(['id' => $address[Address::KEY_CITY_ID]])))
            throw new Exception("Error Bad city_id", 404);

        //Execution des comparaisons et des changements à faire
        $up = 0;
        if ($info[Recruter::KEY_COMPANY_NAME] !== $recruter->getCompanyName()) {
            $up++;
            $recruter->setCompanyName($info[Recruter::KEY_COMPANY_NAME]);
        }
        if (($u_address = $recruter->getAddress())) {
            if ($u_address->getCity()->getId() !== (int)$address[Address::KEY_CITY_ID]) {
                $u_address->setCity($new_city);
                $up++;
            }
            if ($u_address->getStreetName() !== $address[Address::KEY_STREET_NAME]) {
                $u_address->setStreetName($address[Address::KEY_STREET_NAME]);
                $up++;
            }
            if ($u_address->getNumber() !== (int)$address[Address::KEY_NUMBER]) {
                $u_address->setNumber((int)$address[Address::KEY_NUMBER]);
                $up++;
            }
        } else {
            $n_address = new Address((int)$address[Address::KEY_NUMBER], $address[Address::KEY_STREET_NAME], $new_city);
            $entityManager->persist($n_address);
            $entityManager->flush();
            $recruter->setAddress($n_address);
            $up++;
        }
        return $up;
    }

    /**
     * @throws Exception
     */
    private static function updateCandidate(Candidate $candidate, array $info, CheckerInterface $checker): int
    {

        //Check des valeurs reçues
        if (!$checker->checkData($info[Candidate::KEY_FIRST_NAME], 'string') ||
            !$checker->checkData($info[Candidate::KEY_LAST_NAME], 'string'))
            throw new Exception('Nom ou prénom incomplet ou erroné, données non mis a jours', 422);

        //Execution des comparaisons et des changements à faire
        $up = 0;
        if ($info[Candidate::KEY_FIRST_NAME] !== $candidate->getFirstName()) {
            $candidate->setFirstName($info[Candidate::KEY_FIRST_NAME]);
            $up++;
        }
        if ($info[Candidate::KEY_LAST_NAME] !== $candidate->getLastName()) {
            $candidate->setLastName($info[Candidate::KEY_LAST_NAME]);
            $up++;
        }
        return $up;
    }

    ////////////////////////////////////////////////////////////////////////
    /// ONGLET MES OFFRES

    #[Route('/profil_get_recruter_offers')]
    public function getRecruterOffers(EntityManagerInterface $entityManager, CheckerInterface $checker): Response
    {
        try {
            if (!$checker->checkData($_POST, 'array', ['id', 'password']))
                throw new Exception('Error Data', 422);
            $recruter = self::getRoledUserByPassWord(RolesInterface::ROLE_RECRUTER, $_POST['id'], $_POST['password'], $entityManager);
            $offers = $recruter->getOffers();
            $arr_offers = [];
            $except_list = [Offer::KEY_APPLIEDS_ID, Offer::KEY_LOCATION_ID, Offer::KEY_POSTER_ID, Offer::KEY_DESCRIPTION,
                            Offer::KEY_WEEK_HOURS, Offer::KEY_NET_SALARY];
            foreach ($offers as $offer)
                $arr_offers[] = $offer->getValueAsArray($except_list);
            return new JsonResponse(json_encode($arr_offers));
        } catch (Exception $exception) {
            return new Response($exception->getMessage(), $exception->getCode());
        }
    }

    #[Route('/profil_make_recruter_action_on_offers')]
    public function makeRecruterActionOnOffers(EntityManagerInterface $entityManager, CheckerInterface $checker)
    {
        try {
            if (!$checker->checkData($_POST, 'array', ['id', 'password', 'info']) ||
                !$checker->checkData(json_decode($_POST['info'], true), 'array', ['offers_ids', 'action_type']))
                throw new Exception('Error Data', 422);
            $info = json_decode($_POST['info'], true);
            $recruter = self::getRoledUserByPassWord(RolesInterface::ROLE_RECRUTER, $_POST['id'], $_POST['password'], $entityManager);
            if (self::makeActionOffers($recruter, $info['offers_ids'], $info['action_type'], $entityManager))
                return new Response('Actions bien réaliser', 200);
            else
                return new Response('Rien à changé', 200);
        } catch (Exception $exception) {
            return new Response($exception->getMessage(), $exception->getCode());
        }
    }
    private static function makeActionOffers(Recruter $recruter, array $offer_ids, int $opt,
                                             EntityManagerInterface $entityManager): bool
    {
        $finish = false;
        $action = match ($opt) {
            1 => 'make_archived',
            2 => 'make_unarchived',
            3 => 'make_delete',
            default => throw new Exception('Error data', 422),
        };
        foreach ($offer_ids as $id)
        {
            if (!($offer = $entityManager->getRepository(Offer::class)->findOneBy(['id' => $id])) ||
                $offer->getPoster()->getId() !== $recruter->getId())
                throw new Exception('Error data', 422);
            else
            {
                $offer->$action($entityManager);
                $finish = true;
            }
        }
        return $finish;
    }

    ////////////////////////////////////////////////
    /// ONGLET GESTIONS CANDIDATS

    #[Route('/profil_get_no_validated_candidates')]
    public function getNoValidatedCandidates(EntityManagerInterface $entityManager, CheckerInterface $checker): Response
    {
        try {
            self::getApproveInfoChecker($entityManager, $checker, $_POST);
            $candidates = $entityManager->getRepository(Candidate::class)->findBy(['activated' => false], ['id' => 'desc']);
            $arr_candidate_info = [];
            foreach ($candidates as $candidate)
                $arr_candidate_info[] = array_merge($candidate->getValueAsArray([Candidate::KEY_USER_ID]), ['email' => $candidate->getUser()->getEmail()]);
            return new JsonResponse(json_encode($arr_candidate_info));
        } catch (Exception $exception) {
            return new Response($exception->getMessage(), $exception->getCode());
        }
    }

    #[Route('profil_approves_candidates')]
    public function approveNoValidatedCandidates(EntityManagerInterface $entityManager, CheckerInterface $checker): Response
    {
        return self::approveNoValidated($entityManager, $checker, $_POST, Candidate::class, "Les candidats ont bien était approuvés");
    }

    ////////////////////////////////////////////////////////
    /// ONGLET GESTION RECRUTER

    #[Route('/profil_get_no_validated_recruters')]
    public function getNoValidatedRecruters(EntityManagerInterface $entityManager, CheckerInterface $checker): Response
    {
        try {
            self::getApproveInfoChecker($entityManager, $checker, $_POST);
            $recruters = $entityManager->getRepository(Recruter::class)->findBy(['activated' => false], ['id' => 'desc']);
            $arr_recruter_info = [];
            foreach ($recruters as $recruter)
                $arr_recruter_info[] = array_merge($recruter->getValueAsArray([Recruter::KEY_USER_ID, Recruter::KEY_ADDRESS]),
                    ['email' => $recruter->getUser()->getEmail(), 'address_name' => $recruter->getAddress()?->getFullName()]);
            return new JsonResponse(json_encode($arr_recruter_info));
        } catch (Exception $exception) {
            return new Response($exception->getMessage(), $exception->getCode());
        }
    }

    #[Route('profil_approves_recruters')]
    public function approveNoValidatedRecruters(EntityManagerInterface $entityManager, CheckerInterface $checker): Response
    {
        return self::approveNoValidated($entityManager, $checker, $_POST, Recruter::class, "Les recruteurs ont bien était approuvés");
    }


    ////////////////////////////////////////////////////////
    /// ONGLET APPROUVE ANNONCES

    #[Route('/profil_get_no_validated_offers')]
    public function getNoValidatedOffers(EntityManagerInterface $entityManager, CheckerInterface $checker): Response
    {
        try {
            self::getApproveInfoChecker($entityManager, $checker, $_POST);
            $offers = $entityManager->getRepository(Offer::class)->findBy(['validated' => false], ['id' => 'desc']);
            $arr_offer_info = [];
            foreach ($offers as $offer)
                $arr_offer_info[] = array_merge($offer->getValueAsArray([Offer::KEY_POSTER_ID,Offer::KEY_LOCATION_ID, Offer::KEY_APPLIEDS_ID]),
                    ['company_name' => $offer->getPoster()->getCompanyName()]);
            return new JsonResponse(json_encode($arr_offer_info));
        } catch (Exception $exception) {
            return new Response($exception->getMessage(), $exception->getCode());
        }
    }

    #[Route('profil_approves_offers')]
    public function approveNoValidatedOffers(EntityManagerInterface $entityManager, CheckerInterface $checker): Response
    {
        return self::approveNoValidated($entityManager, $checker, $_POST, Offer::class, "Les annonces ont bien était approuvées");
    }



    ////////////////////////////////////////////////////////
    /// ONGLET APPROUVE POSTULANCES

    #[Route('/profil_get_no_validated_applieds')]
    public function getNoValidatedApplieds(EntityManagerInterface $entityManager, CheckerInterface $checker): Response
    {
        try {
            self::getApproveInfoChecker($entityManager, $checker, $_POST);
            $applieds = $entityManager->getRepository(AppliedCandidate::class)->findBy(['validated' => false], ['id' => 'desc']);
            $arr_applied_info = [];
            foreach ($applieds as $applied)
            {
                $candidate = $applied->getCandidate();
                $candidate_arr = $candidate->getValueAsArray([Candidate::KEY_USER_ID, Candidate::KEY_ID]);
                $email_arr = ['email' => $candidate->getUser()->getEmail()];
                $arr_applied_info[] = array_merge($applied->getValueAsArray([AppliedCandidate::KEY_CANDIDATE_ID]),
                $candidate_arr, $email_arr);
            }
            return new JsonResponse(json_encode($arr_applied_info));
        } catch (Exception $exception) {
            return new Response($exception->getMessage(), $exception->getCode());
        }
    }

    #[Route('profil_approves_applieds')]
    public function approveNoValidatedApplieds(EntityManagerInterface $entityManager, CheckerInterface $checker,
                                               PathInterface $path, MailerInterface $mailer): Response
    {
        $response = self::approveNoValidated($entityManager, $checker, $_POST, AppliedCandidate::class,
            "Les candidatures ont bien était envoyé");
        try {
            $ids = json_decode($_POST['info']);
            foreach ($ids as $id)
            {
                $applied = $entityManager->getRepository(AppliedCandidate::class)->findOneBy(['id' => $id]);
                $offer = $applied->getOffer();
                $recruter_email = $offer->getPoster()->getUser()->getEmail();
                $candidate = $applied->getCandidate();
                if (!($cv_path = $candidate->getAbsoluteCvPath($path)))
                    throw new Exception("Email non envoyé au recruteur car le candidat n'a pas de cv en ligne");
                else
                    self::sendAppliedEmail($mailer, $cv_path, $candidate->getFullName(), $recruter_email, $offer->getTitle());
            }
            return $response;
        } catch (Exception $exception) {
            return new Response($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public static function sendAppliedEmail(MailerInterface $mailer, string $cv_path, string $name, string $email, string $offer_name)
    {
        if (file_exists($cv_path))
        {
            $email = (new Email())
                ->from('kafe.countac@gmx.fr')
                ->to($email)
                ->subject("Candidature à l'offre '$offer_name'")
                ->text("$name à postuler a votre offre '$offer_name'\n\n Son CV est envoyé join à se mail.")
                ->attach(fopen($cv_path, 'r'), 'cv.pdf')
            ;
            $mailer->send($email);
        }
    }


    ////////////////////////////////////////////////////////
    /// FONCTION COMMUNE POUR ACTION DU CONSULTANT


    private static function approveNoValidated(EntityManagerInterface $entityManager, CheckerInterface $checker, array $post,
                            string $class, string $message): Response
    {
        try {
            $ids = self::setApproveInfoChecker($entityManager, $checker, $post);
            foreach ($ids as $id)
            {
                if (!($entity = $entityManager->getRepository($class)->findOneBy(['id' => $id])))
                    throw new Exception('Error Data', 422);
                else
                    $entity->approve(true);
            }
            $entityManager->flush();
            return new Response($message, 200);
        } catch (Exception $exception) {
            return new Response($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @throws Exception
     */
    private static function getApproveInfoChecker(EntityManagerInterface $entityManager, CheckerInterface $checker, $post)
    {
            if (!$checker->checkData($post, 'array', ['id', 'password']))
                throw new Exception('Error Data', 422);
            self::getRoledUserByPassWord(RolesInterface::ROLE_CONSULTANT, $_POST['id'], $_POST['password'], $entityManager);
    }

    /**
     * @throws Exception
     */
    private static function setApproveInfoChecker(EntityManagerInterface $entityManager, CheckerInterface $checker, $post): array
    {
        if (!$checker->checkData($post, 'array', ['id', 'password', 'info']) ||
            !$checker->checkData(json_decode($post['info']), 'array'))
            throw new Exception('Error Data', 422);
        self::getRoledUserByPassWord(RolesInterface::ROLE_CONSULTANT, $_POST['id'], $_POST['password'], $entityManager);
        return json_decode($post['info']);
    }



    ////////////////////////////////////////////////////////
    /// ONGLET ADMIN PAGE

    #[Route('/profil_get_consultants')]
    public function getConsultants(EntityManagerInterface $entityManager, CheckerInterface $checker): Response
    {
        try {
            if (!$checker->checkData($_POST, 'array', ['id', 'password']))
                throw new Exception('Error Data', 422);
            self::getRoledUserByPassWord(RolesInterface::ROLE_ADMIN, $_POST['id'], $_POST['password'], $entityManager);
            $arr_consultants_info = $entityManager->createQuery(
                "SELECT u.email,u.id FROM App\Entity\User u WHERE JSON_CONTAINS(u.roles, '[\"ROLE_CONSULTANT\"]') = 1"
            )->execute();
            return new JsonResponse(json_encode($arr_consultants_info));
        } catch (Exception $exception) {
            return new Response($exception->getMessage(), $exception->getCode());
        }
    }

    #[Route('profil_remove_consultants')]
    public function removeConsultant(EntityManagerInterface $entityManager, CheckerInterface $checker): Response
    {

        try {
            if (!$checker->checkData($_POST, 'array', ['id', 'password', 'info']))
                throw new Exception('Error Data', 422);
            self::getRoledUserByPassWord(RolesInterface::ROLE_ADMIN, $_POST['id'], $_POST['password'], $entityManager);
            $ids = json_decode($_POST['info']);
            foreach ($ids as $id)
            {
                if (!($user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id])))
                    throw new Exception('Error Data 2', 422);
                $user->setRoles(array_diff($user->getRoles(), [RolesInterface::ROLE_CONSULTANT]));
            }
            $entityManager->flush();
            return new Response("Les consultants ont bien était supprimé", 200);
        } catch (Exception $exception) {
            return new Response($exception->getMessage(), $exception->getCode());
        }
    }



    //////////////////////////////////////////////////////////
    ///  FONCTION D'AUTHENTIFICATION

    /**
     * Authentification de l'utilisateur
     * @throws Exception
     */
    private static function getUserByIdPassword($post_id, $post_password, EntityManagerInterface $entityManager): User
    {
        $id = $post_id ?? '';
        $password = $post_password ?? '';
        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
        if ($user) {
            if ($user->getPassword() !== $password)
                throw new Exception('Bad password', 401);
            return $user;
        } else
            throw new Exception("User not found", 404);
    }

    /**
     * @throws Exception
     */
    private static function getRoledUserByPassWord(string $role, $post_user_id, $post_password,
                            EntityManagerInterface $entityManager): Candidate | Recruter | Consultant | Admin
    {
        $user = self::getUserByIdPassword($post_user_id, $post_password, $entityManager);
        $roled_user = match ($role)
        {
            RolesInterface::ROLE_CANDIDATE => $user->getCandidate($entityManager),
            RolesInterface::ROLE_RECRUTER => $user->getRecruter($entityManager),
            RolesInterface::ROLE_CONSULTANT => $user->getConsultant(),
            RolesInterface::ROLE_ADMIN => $user->getAdmin(),
            default => false,
        };
        if ($roled_user)
            return $roled_user;
        else
            throw new Exception('No right', 403);
    }

}
