<?php

namespace App\Controller\Test;

use PDO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class testController extends AbstractController
{
    #[Route('/test', name: 'app_test_test')]
    public function index(): Response
    {
        $pdo = new PDO($_ENV['DATABASE_PDO_URL'], $_ENV['DATABASE_USER'], $_ENV['DATABASE_PASSWORD']);

        $stat = $pdo->prepare('select email from user where email = ?');
        $stat->bindValue(1, 'a@kdkdkddjizdizj');
        $ex = $stat->execute();
        $res = $stat->fetchAll();
        dd($res, $ex);



        return new Response("");
    }
}
