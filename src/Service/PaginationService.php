<?php


namespace App\Service;


// On créé un service pour la pagination

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class PaginationService

{


    // On doit construire le manager pour l'avoir ici
    // On en aura besoin pour récupérer l'entity
    private $manager;

    // Environnement twig
    private $twig;
    private $route;
    private $entityClass;
    private $limit = 10;
    private $currentPage = 1;
    private $templatePath;

    public function __construct(EntityManagerInterface $manager, Environment $twig, RequestStack $requestStack, $templatePath)
    {
        $this->route = $requestStack->getCurrentRequest()->attributes->get('_route');

        $this->templatePath = $templatePath; // services.yaml

        $this->manager = $manager;
        $this->twig = $twig;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function setRoute($route)
    {
        $this->route = $route;

        return $this;

    }

    public function setTemplatePath($template)
    {
        $this->templatePath = $template;

        return $this;
    }

    public function getTemplatePath()
    {
        return $this->templatePath;
    }


    // On doit connaître quelle entity est utilisé (comment, annonce)

    public function display()
    {
        $this->twig->display($this->templatePath, [
            'page' => $this->currentPage,
            'pages' => $this->getPages(),
            'route' => $this->route
        ]);
    }

    public function getPages()
    {
        if (empty($this->entityClass)) {
            throw new \Exception("Vous n'avez pas spécifié l'entité sur laquelle paginée");
        }
        // Connaître le total des enregistrements
        $repo = $this->manager->getRepository($this->entityClass);
        $total = count($repo->findAll());

        // Faire la division et l'arrondit

        return ceil($total / $this->limit);
    }

    public function getData()
    {
        // 1 : Calculer l'offset

        if (empty($this->entityClass)) {
            throw new \Exception("Vous n'avez pas spécifié l'entité sur laquelle paginée");
        }

        $offset = $this->currentPage * $this->limit - $this->limit;

        // 2 : Demander au repos les élements

        $repo = $this->manager->getRepository($this->entityClass);

        // 3 : Return les élements

        return $repo->findBy([], [], $this->limit, $offset);
    }

    // On doit connaître la limite

    public function getEntityClass()
    {
        return $this->entityClass;
    }

    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    // On doit connaître la page courrante par defaut 1

    /**
     * @param int $limit
     * @return PaginationService
     */
    public function setLimit(int $limit): PaginationService
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @param int $currentPage
     * @return PaginationService
     */
    public function setCurrentPage(int $currentPage): PaginationService
    {
        $this->currentPage = $currentPage;
        return $this;
    }


}
