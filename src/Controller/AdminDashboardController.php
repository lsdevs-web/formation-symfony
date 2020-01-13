<?php

namespace App\Controller;

use App\Service\StatService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminDashboardController extends AbstractController
{
    /**
     * @Route("/admin", name="admin_dashboard")
     * @param EntityManagerInterface $manager
     * @param StatService $service
     * @return Response
     */
    public function index(EntityManagerInterface $manager, StatService $service)
    {

        // VOIR STATSERVICE

        $stats = $service->getStats();


        $bestAds = $service->getAdsStats('DESC');

        $pireAds = $service->getAdsStats('ASC', 10);


        return $this->render('admin/dashboard/index.html.twig', [

            'stats' => $stats,
            'bestAds' => $bestAds,
            'pireAds' => $pireAds

        ]);
    }
}
