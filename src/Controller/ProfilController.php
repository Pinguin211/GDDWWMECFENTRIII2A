<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Admin;
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
            self::KEY_CONSULTANT => $user->haveRole(RolesInterface::ROLE_CONSULTANT),
            self::KEY_ADMIN => $user->haveRole(RolesInterface::ROLE_ADMIN)
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
        switch ($role) {
            case RolesInterface::ROLE_CANDIDATE:
                $roled_user = $user->getCandidate($entityManager);
                break;
            case RolesInterface::ROLE_RECRUTER:
                $roled_user = $user->getRecruter($entityManager);
                break;
            case RolesInterface::ROLE_CONSULTANT:
                $roled_user = $user->getConsultant();
                break;
            case RolesInterface::ROLE_ADMIN:
                $roled_user = $user->getAdmin();
                break;
            default:
                $roled_user = false;
        }
        if ($roled_user)
            return $roled_user;
        else
            throw new Exception('No right', 403);
    }

}
