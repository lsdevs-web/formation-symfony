<?php

namespace App\Controller;

use App\Repository\AnnonceRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param AnnonceRepository $annonceRepository
     * @param UserRepository $userRepository
     * @return Response
     */
    public function index(AnnonceRepository $annonceRepository, UserRepository $userRepository)
    {
        return $this->render('home/index.html.twig', [
            // Voir AnnonceRepository
            'annonces'=> $annonceRepository->findBestAds(3),
            // Voir UserRepository
            'users'=> $userRepository->findBestUsers()
        ]);
    }
}
