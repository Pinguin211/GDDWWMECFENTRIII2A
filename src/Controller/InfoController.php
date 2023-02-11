<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class InfoController extends AbstractController
{
    #[Route('/get_city_list')]
    public function getCityList(EntityManagerInterface $entityManager): JsonResponse
    {
        $arr = $entityManager->createQuery("SELECT c.id,c.name FROM App\Entity\City c")->execute();
        return new JsonResponse(json_encode($arr));
    }

    #[Route('/get_location_list')]
    public function getRegionList(EntityManagerInterface $entityManager): JsonResponse
    {
        $arr = [];
        $arr['city'] = $entityManager->createQuery("SELECT c.id,c.name FROM App\Entity\City c")->execute();
        $arr['region'] = $entityManager->createQuery("SELECT r.id,r.name FROM App\Entity\Region r")->execute();
        $arr['department'] = $entityManager->createQuery("SELECT d.id,d.name FROM App\Entity\Department d")->execute();
        return new JsonResponse(json_encode($arr));
    }
}
