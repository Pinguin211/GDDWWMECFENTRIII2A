<?php

namespace App\Controller;

use App\Entity\Location;
use App\Entity\Offer;
use App\Interface\LocationInterface;
use App\Service\CheckerInterface;
use App\Service\RolesInterface;
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
        $filters = self::validateFilters($_GET);
        $dql = self::getDqlFilters($filters, $entityManager);
        $ids_loc_ids = $entityManager->createQuery($dql)->getArrayResult();
        $ids = self::locationFilter($filters, $ids_loc_ids, $entityManager);

        $nb_ids = count($ids);
        $max_per_page = 20;
        $index = $this->getPageIndex($filters, $nb_ids, $max_per_page);
        $offers = [];
        while (isset($ids[$index['start']]) && $max_per_page > 0)
        {
            $offers[] = $entityManager->getRepository(Offer::class)->findOneBy(['id' => $ids[$index['start']]]);
            $index['start']++;
            $max_per_page--;
        }
        return $this->render('offer_list/index.html.twig', [
            'error' => $error,
            'last_email' => $lastEmail,
            'offers' => $offers,
            'entitymanager' => $entityManager,
            'filters' => $filters,
            'index' => $index,
            'nb_result' => $nb_ids
        ]);
    }
    /*
     * Filtres les resultats selon la location choisi si il y en a une
     */
    private static function locationFilter(array &$filters, array $ids_loc_ids, EntityManagerInterface $entityManager)
    {
        $ids_loc = [];
        foreach ($ids_loc_ids as $arr)
            $ids_loc[$arr['id']] = $arr[1];
        $filters['location_type_name'] = 'Ville, Région, Département';
        $filters['location_id_name'] = '----';
        if (!($class = Location::getClassByType($filters['location_type'])) ||
            !($loc = $entityManager->getRepository($class)->findOneBy(['id' => $filters['location_id']])))
            return array_keys($ids_loc);
        else
        {
            $filters['location_type_name'] = $loc->getTypeString();
            $filters['location_id_name'] = $loc->getName();
            $valid_loc_ids = self::getLocationIds($loc, $entityManager);
            return array_keys(array_intersect($ids_loc, $valid_loc_ids));
        }
    }
    /*
     * Renvoie le tableau des locations valide
     */
    private static function getLocationIds(LocationInterface $location, EntityManagerInterface $entityManager): array
    {
        $ids = [];
        $s_type = 1;
        while ($s_type <= $location->getType())
        {
            $dql = self::getLocationDqlIds($location, $s_type);
            $ids = array_merge($ids, $entityManager->createQuery($dql)->getSingleColumnResult());
            $s_type++;
        }
        return $ids;
    }
    /*
     * Creer les requetes dql pour recuperer les id des location valide
     */
    private static function getLocationDqlIds(LocationInterface $location, int $s_type): string
    {
        $class = Location::getClassByType($s_type);
        $arr = ['x','y','z','s'];
        $i = 0;
        $base_dql = "SELECT l.id FROM App\Entity\Location l JOIN $class x WITH l.type_id = x.id and l.type = $s_type";
        while ($s_type < $location->getType())
        {
            $nm1 = $arr[$i];
            $nm1_master_id = Location::getMasterColByType($s_type);
            $i++;
            $n = $arr[$i];
            $s_type++;
            $class = Location::getClassByType($s_type);
            $base_dql .= " JOIN $class $n WITH $n.id = $nm1.$nm1_master_id";
        }
        $n = $arr[$i];
        $base_dql .= " WHERE $n.id = " . $location->getId();
        return $base_dql;
    }
    /*
     * Array qui calcule le systeme de pages de la list
     */
    private function getPageIndex(array $filters, int $nb_ids, int $max_nb): array
    {
        $max_pages = intdiv($nb_ids, $max_nb);
        if ($nb_ids % $max_nb)
            $max_pages += 1;
        if ($max_pages === 0)
            $index['now'] = 1;
        else if ($max_pages <= (int)$filters['page'])
            $index['now'] = $max_pages;
        else
        {
            $index = ['now' => (int)$filters['page']];
            if ($max_pages > ($index['now'] + 1))
            {
                $index['max'] = $max_pages;
                $index['max_url'] = $this->constructUrlWithFilter($filters, $max_pages);
            }
        }
        if ($index['now'] > 1)
            $index['prev_url'] = $this->constructUrlWithFilter($filters, $index['now']-1);
        if (($index['now']) < $max_pages)
            $index['next_url'] = $this->constructUrlWithFilter($filters, $index['now'] + 1);
        $index['start'] = ($index['now'] - 1) * $max_nb;
        $index['now_url'] = $this->constructUrlWithFilter($filters, $index['now']);
        return $index;
    }
    /*
     * Permet de construire l'url de la page avec les filtres donné
     */
    private function constructUrlWithFilter(array $filters, int $page): string
    {
        return $this->generateUrl('app_annonces',
            array_merge(array_diff_key($filters, ['page', 'location_type_name', 'location_type_id']), ['page' => $page]));
    }
    /*
     * Verifie les filtre recus
     */
    private static function validateFilters($get): array
    {
        $filters = [];
        $names = [
            'search' => ['string', ''], 'page' => ['numeric', 1],
            'min_hours' => ['numeric', ''], 'max_hours' => ['numeric', ''],
            'min_salary' => ['numeric', ''], 'max_salary' => ['numeric', ''],
            'location_type' => ['numeric', 0], 'location_id' => ['numeric', 0]
        ];
        foreach ($names as $name => $info)
            $filters[$name] = CheckerInterface::checkArrayDataStatic($get, $name, $info[0]) ? $get[$name] : $info[1];
        return $filters;
    }
    /*
     * Execute la requete dql pour recevoir les id des annonces et les id de leur objet Location
     * selon les filtres donné (sauf la localisation)
     */
    private static function getDqlFilters(array $filters, EntityManagerInterface $entityManager)
    {
        $dql = 'SELECT o.id, IDENTITY(o.location) FROM App\Entity\Offer o WHERE ';
        $search = self::getSearchTitleFilters($filters['search']);
        $salary = self::getMinMaxFilters('o.net_salary', $filters['min_salary'], $filters['max_salary']);
        $hours = self::getMinMaxFilters('o.week_hours', $filters['min_hours'], $filters['max_hours']);
        $arr_filters = [$search, $salary, $hours];
        $pass = false;
        foreach ($arr_filters as $filter)
        {
            if (!empty($filter))
                $pass = true;
        }
        if ($pass)
        {
            foreach ($arr_filters as $filter)
            {
                if (!empty($filter))
                    $dql .= $filter . ' AND ';
            }
        }
        return $dql . ' o.validated = true and o.archived = false ORDER BY o.post_date desc';
    }
    /*
     * Ecris le morceau de requete dql pour le filtrage du titre de l'annonce
     */
    private static function getSearchTitleFilters(string $search): string
    {
        if (empty($search))
            return '';
        $to_search = explode(' ', $search);
        $filters = '';
        foreach ($to_search as $value)
            $filters .= "o.title like '%$value%' or ";
        return substr($filters, 0, -4);
    }
    /*
     * Ecris le morceau de requete dql pour le filtrage du salaire et des heures de l'annonce
     */
    private static function getMinMaxFilters(string $col_name, string $min, string $max): string
    {
        if (empty($min) && empty($max))
            return '';
        elseif (empty($min) && !empty($max))
            return "$col_name <= '$max'";
        elseif (!empty($min) && empty($max))
            return "$col_name >= $min";
        elseif (!empty($min) && !empty($max))
        {
            if ((int)$min >= (int)($max))
                return "$col_name <= '$min'";
            else
                return "$col_name BETWEEN '$min' AND '$max'";
        }
        return '';
    }


    ////////////////////////////////////////////////////////////////////////////////////////
    ///         PAGE DETAIL D'ANNONCE

    #[Route('/annonce_detail', name: 'app_offer_detail')]
    public function offer_detail(AuthenticationUtils $authenticationUtils, EntityManagerInterface $entityManager): Response
    {
        if (!isset($_GET['id']) || !($offer = $entityManager->getRepository(Offer::class)->findOneBy(['id'=>$_GET['id']])) ||
            (!$offer->isValidated() && !($this->isGranted(RolesInterface::ROLE_CONSULTANT) || $this->isGranted(RolesInterface::ROLE_ADMIN))))
            return $this->redirectToRoute('app_message', ['title' => 'Erreur 404', 'message' => "Cette page n'existe pas", 'redirect_app' => 'app_annonces']);


        //Login form
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastEmail = $authenticationUtils->getLastUsername();

        return $this->render('offer_list/offer_detail.html.twig', [
            'error' => $error,
            'last_email' => $lastEmail,
            'entitymanager' => $entityManager,
            'offer' => $offer,
        ]);
    }
}
