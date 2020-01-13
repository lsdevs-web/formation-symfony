<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\AdminBookingType;
use App\Repository\BookingRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminBookingController extends AbstractController
{
    /**
     * @Route("/admin/bookings/{page<\d+>?1}", name="admin_booking_index")
     * @param BookingRepository $repository
     * @param $page
     * @param PaginationService $pagination
     * @return Response
     */
    public function index(BookingRepository $repository, $page, PaginationService $pagination)
    {

        // Service de pagination PaginationService

        $pagination->setEntityClass(Booking::class)
            ->setCurrentPage($page)
            ->setLimit(5);


        return $this->render('admin/booking/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Permet d'éditer une réservation
     *
     * @Route("/admin/bookings/{id}/edit", name="admin_booking_edit")
     * @param Booking $booking
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function edit(Booking $booking, Request $request, EntityManagerInterface $manager)
    {
        $form = $this->createForm(AdminBookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // On calcule automatiquement l'amount en le mettant à 0 car
            // dans l'entity Booking nous avons fais une fonction if empty amount

            $booking->setAmount($booking->getAnnonce()->getPrice() * $booking->getDuration());

            $manager->persist($booking);
            $manager->flush();

            $this->addFlash(
                "success",
                "La réservation à été modifiée"
            );
        }


        return $this->render("admin/booking/edit.html.twig", [
            'form' => $form->createView(),
            'booking' => $booking
        ]);

    }


    /**
     * Permet de supprimer une réservation
     *
     * @Route("/admin/bookings/{id}/delete", name="admin_booking_delete")
     * @param Booking $booking
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */
    public function delete(Booking $booking, EntityManagerInterface $manager)
    {

        $manager->remove($booking);
        $manager->flush();

        $this->addFlash(
            "success",
            "Réservation supprimée"
        );

        return $this->redirectToRoute("admin_booking_index");

    }
}
