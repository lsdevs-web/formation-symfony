<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Booking;
use App\Entity\Comment;
use App\Form\BookingType;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookingController extends AbstractController
{
    /**
     * Permet d'afficher le booking
     * Il aura besoin d'une annonce, d'un slug
     *
     * @IsGranted("ROLE_USER")
     *
     * @Route("/annonces/{slug}/book", name="booking_create")
     * @param Annonce $annonce
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function book(Annonce $annonce, Request $request, EntityManagerInterface $manager)
    {
        // On créé l'entité
        $booking = new Booking();

        // On créé le formulaire en lui passant son type et l'entité
        $form = $this->createForm(BookingType::class, $booking);
        // On récupère les infos de la requête
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // On chope l'user connecté
            $user = $this->getUser();

            // Le booker est l'user connecté
            $booking->setBooker($user)
                ->setAnnonce($annonce);

            // Si les dates ne sont pas disponibles, erreur
            if (!$booking->isBookableDate()) {
                $this->addFlash(
                    'warning',
                    "Les dates que vous avez saisies sont déjà prises"
                );
            } else {

                // Sinon enregistrement + redirection


                $manager->persist($booking);
                $manager->flush();

                // On redirige en insérant des paramètres, ici le id est nécéssaire à notre route /booking/{id}
                // withAlert sert à afficher un flash la première fois qu'on réserve l'annonce
                // Voir booking/show.html.twig
                return $this->redirectToRoute("booking_show", ['id' => $booking->getId(), 'withAlert' => true]);
            }


        }

        return $this->render('booking/book.html.twig', [
            'annonce' => $annonce,
            'form' => $form->createView()
        ]);
    }


    /**
     * Permet d'afficher la page d'une réservation
     *
     * @Route("/booking/{id}", name="booking_show")
     * @param Booking $booking
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function show(Booking $booking, Request $request, EntityManagerInterface $manager)
    {

        // On créé le commentaire
        $comment = new Comment();

        // On créé le formulaire avec son Type et son Entity
        $form = $this->createForm(CommentType::class, $comment);
        // On demande au formulaire de gérer la request
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On a besoin de dire à quelle annonce appartient le commentaire
            $comment->setAnnonce($booking->getAnnonce())
                // On a besoin de dire à quel auteur appartient le commentaire
                ->setAuthor($this->getUser());

            $manager->persist($comment);
            $manager->flush();

            $this->addFlash(
                "success",
                "Votre commentaire à bien été pris en compte !"
            );
        }

        return $this->render("booking/show.html.twig", [
            'booking' => $booking,
            'form' => $form->createView()
        ]);
    }
}
