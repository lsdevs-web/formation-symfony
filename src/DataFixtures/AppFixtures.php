<?php

namespace App\DataFixtures;

use App\Entity\Annonce;
use App\Entity\Booking;
use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{

    // On créé notre encodeur, dans les fixtures on ne peut pas utiliser l'injection de dépendance de l'encodeur comme dans le controller
    private $encoder;

    // Pour appeler l'encodeur il faut le construire
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
        // Les fixtures utilises les ObjectManager et pas les EntityManagerInterface des Controllers
    {
        // On importe et on créé faker
        $faker = Factory::create("FR-fr");


        // On créé un nouveau role
        $adminRole = new Role();
        $adminRole->setTitle('ROLE_ADMIN');
        $manager->persist($adminRole);

        // On créé un nouvel utilisateur
        $adminUser = new User();
        $adminUser->setFirstName('Admin')
            ->setLastName('Admin')
            ->setEmail('admin@symfony.fr')
            ->setHash($this->encoder->encodePassword($adminUser, 'pass'))
            ->setAvatar('https://avatars.io/twitter/LiiorC')
            ->setIntroduction($faker->sentence)
            ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(3)) . '</p>')
            // On oubli pas de lui rajouter son role !
            ->addUserRole($adminRole);
        $manager->persist($adminUser);


        // ICI NOUS GERONS LES UTILISATEURS
        $users = [];
        $genres = ['male', 'female'];


        for ($i = 1; $i <= 10; $i++) {
            $user = new User();

            $genre = $faker->randomElement($genres);

            $picture = "https://randomuser.me/api/portraits/";
            $pictureiD = $faker->numberBetween(1, 99) . '.jpg';

            // $picture = $picture . ($genre == 'male' ? 'men/' : 'women/') . $pictureiD;
            if ($genre == 'male') $picture = $picture . 'men/' . $pictureiD;
            else $picture = $picture . 'women/' . $pictureiD;

            // MOT DE PASSE HASHER
            $hash = $this->encoder->encodePassword($user, 'password'); // A besoin de UserInterface ! (User)


            $user->setFirstName($faker->firstName($genre))
                ->setLastName($faker->lastName)
                ->setEmail($faker->email)
                ->setIntroduction($faker->sentence)
                ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(3)) . '</p>')
                ->setHash($hash)
                ->setAvatar($picture);

            $manager->persist($user);
            $users[] = $user;
        }


        // ICI NOUS GERONS LES ANNONCES
        for ($i = 1; $i <= 72; $i++) {

            $annonce = new Annonce();

            $fTitle = $faker->sentence();
            $fImage = $faker->imageUrl(1000, 350);
            $fIntro = $faker->paragraph(1);
            $fContent = '<p>' . join('</p><p>', $faker->paragraphs(3)) . '</p>';

            $user = $users[mt_rand(0, count($users) - 1)];

            $annonce->setTitle($fTitle)
                ->setCoverImage($fImage)
                ->setIntro($fIntro)
                ->setContent($fContent)
                ->setPrice(mt_rand(12, 520))
                ->setRooms(mt_rand(1, 8))
                ->setAuthor($user);

            for ($j = 1; $j <= mt_rand(2, 5); $j++) {

                $image = new Image();

                $image->setUrl($faker->imageUrl())
                    ->setCaption($faker->sentence(1))
                    ->setAnnonce($annonce);


                $manager->persist($image);
            }

            // GESTION DES RESERVATIONS
            for ($j = 1; $j <= mt_rand(0, 10); $j++) {

                // On créé un nouveau Booking
                $booking = new Booking();

                // Date de création de l'annonce
                $createdAt = $faker->dateTimeBetween('-6 months');
                // Date de début de booking
                $startDate = $faker->dateTimeBetween('-3 months');


                // Durée de booking (en jours)
                $duration = mt_rand(3, 10);
                // Fin de booking
                $endDate = $faker->dateTimeBetween($startDate, "+$duration days");


                // Le prix du booking = Le prix de l'annonce * sa duréé
                $amount = $annonce->getPrice() * $duration;

                // On pioche un utilisateurs dans le tableau des users qui est plus haut
                $booker = $users[mt_rand(0, count($users) - 1)];

                // On génère un commentaire
                $comment = $faker->paragraph;

                // On set tous au booking !
                $booking->setBooker($booker)
                    ->setAnnonce($annonce)
                    ->setStartDate($startDate)
                    ->setEndDate($endDate)
                    ->setCreateAt($createdAt)
                    ->setAmount($amount)
                    ->setComment($comment);

                $manager->persist($booking);


                // Gestion des commentaires

                if (mt_rand(0, 1)) {
                    // On créé un nouveau commentaire
                    $comment = new Comment();

                    $comment->setContent($faker->paragraph)
                        ->setRating(mt_rand(1, 5))
                        ->setAuthor($booker)
                        ->setAnnonce($annonce);

                    $manager->persist($comment);
                }


            }

            $manager->persist($annonce);
        }

        $manager->flush();
    }
}
