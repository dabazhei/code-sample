<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Dashboard;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * Class TagsController
 * @package App\Controller
 */
final class TagsController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * TagsController constructor.
     * @param LoggerInterface $appLogger
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        LoggerInterface $appLogger,
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $appLogger;
    }

    /**
     * @Route("/v1/tags", methods={"GET"}, format="json")
     * @return Response
     */
    public function tags(): Response
    {
        try {
            $dashboardRepository = $this->entityManager->getRepository(Dashboard::class);
            return $this->json($dashboardRepository->getTags());
        } catch (Throwable $exception) {
            $this->logger->error($exception);
            return $this->json($exception->getMessage());
        }
    }
}
