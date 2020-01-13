<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Form\AnnonceType;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminAnnonceController extends AbstractController
{
    /**
     * A besoin du repository des annonces
     *
     * Ne pas oublier le requirements pour la pagination
     * On peut placer le requirements en inline {page<\d+>} on met un ? pour le rendre optionnel {page<\d+>}?
     * On donne une valeur par defaut en le mettant après le ? {page<\d+>?1}
     *
     * @Route("/admin/annonces/{page}", name="admin_annonces_index", requirements={"page": "\d+"})
     * @param AnnonceRepository $repository
     * @param int $page
     * @return Response
     */
    public function index(AnnonceRepository $repository, $page = 1)

    {

        // ON A CREER UN SERVICE SYMFONY POUR LA PAGINATION ( VOIR PAGINATION SERVICE => UTILISATION DANS AdminBookingController )

        // On reçoit en param la page qui par defaut est 1
        // On sécurise la page avec requirements

        // Le nombre d'annonces par pages
        $limit = 10;

        // A partir de quelle annonce on doit chercher le nombre d'annonces à afficher
        $start = $page * $limit - $limit;
        // 1 * 10 = 10 -10 = 0 // On commence à l'annonce 0
        // 2 * 10 = 20 - 10 = 10 // On commence à l'annonce 10

        // On choppe le total de toutes les annonces pour rendre dynamique la pagination HTML
        $total = count($repository->findAll());
        // Donc le nombre de page =
        $pages = ceil($total / $limit);
        // On arrondit à la page suppérieur car on ne peut pas avoir 3,5 pages
        // Si 100 annonces et limite 10 => 10 pages
        // Si 35 annonces et limite 10 =>  3,5 pages => ceil = 4 pages

        return $this->render('admin/annonce/index.html.twig', [
            // Ici on cherche les annonces
            // 1er : ['id' =>  '123']
            // 2eme : [] (OrderBy) c'est un filtre pour savoir comment trier les annonces
            'annonces' => $repository->findBy([], [], $limit, $start),
            // On passe le nombre de pages à twig
            'pages' => $pages,
            // On passe la page atuelle
            'page' => $page
        ]);
    }

    /**
     * Permet d'éditer l'annonce
     *
     * @Route("/admin/annonces/{id}/edit", name="admin_annonce_edit")
     *
     * @param Annonce $annonce
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function edit(Annonce $annonce, Request $request, EntityManagerInterface $manager)
    {
        $form = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $manager->persist($annonce);
            $manager->flush();

            $this->addFlash(
                "success",
                "L'annonce à été modifiée"
            );

        }

        return $this->render("admin/annonce/edit.html.twig", [
            'annonce' => $annonce,
            'form' => $form->createView()
        ]);
    }


    /**
     * Permet de supprimer une annpnce
     *
     * @Route("/admin/annonces/{id}/delete", name="admin_annonce_delete")
     * @param Annonce $annonce
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */
    public function delete(Annonce $annonce, EntityManagerInterface $manager)
    {

        if (count($annonce->getBookings()) > 0) {
            $this->addFlash(
                "warning",
                "Cette annonce à des réservations"
            );
        } else {
            $manager->remove($annonce);
            $manager->flush();

            $this->addFlash(
                "success",
                "L'annonce à été supprimée"
            );
        }


        return $this->redirectToRoute('admin_annonces_index');

    }
}
