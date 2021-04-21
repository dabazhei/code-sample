<?php

declare(strict_types=1);

namespace App\Controller;

use App\DashboardCreationSteps\RefreshPublishedBoard;
use App\DashboardCreationSteps\CreateDashboard;
use App\DashboardCreationSteps\CreateDataSource;
use App\DashboardCreationSteps\CreateUser;
use App\DashboardCreationSteps\CreateUserGroup;
use App\DashboardCreationSteps\FillDashboard;
use App\DashboardCreationSteps\InterruptedProcessChecker;
use App\DashboardCreationSteps\Payload;
use App\DashboardCreationSteps\PublishDashboard;
use App\DashboardCreationSteps\UpdateUserGroup;
use App\DashboardLink\DashboardRestrictionsGroups;
use App\Entity\RedashUser;
use App\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Pipeline\InterruptibleProcessor;
use League\Pipeline\PipelineBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DashboardController
 * @package App\Controller
 */
final class DashboardController extends AbstractController
{
    /**
     * @Route("/v1/dashboard/{id<\d+>}", methods={"GET"}, format="json")
     * @param Request $request
     * @param $id
     * @param LoggerInterface $appLogger
     * @param EntityManagerInterface $entityManager
     * @param Payload $payload
     * @return JsonResponse
     */
    public function dashboard(
        Request $request,
        $id,
        LoggerInterface $appLogger,
        EntityManagerInterface $entityManager,
        Payload $payload
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        $redashUserRepository = $entityManager->getRepository(RedashUser::class);
        $redashUser = $redashUserRepository->findOneBy(['email' => $user->getEmail()]);
        $payload->setDashboardId((int)$id)
            ->setUser($user)
            ->setRestrictions(new DashboardRestrictionsGroups($request, $appLogger))
        ;
        $pipelineBuilder = new PipelineBuilder();

        if ($redashUser === null) {
            $pipelineBuilder->add(new CreateUserGroup())
                ->add(new CreateUser())
                ->add(new CreateDataSource())
                ->add(new UpdateUserGroup())
            ;
        } else {
            $payload->setRedashUser($redashUser);
        }

        $pipelineBuilder->add(new RefreshPublishedBoard())
            ->add(new CreateDashboard())
            ->add(new FillDashboard())
            ->add(new PublishDashboard())
        ;

        $pipeline = $pipelineBuilder->build(new InterruptibleProcessor(new InterruptedProcessChecker()));

        try {
            $result = $pipeline->process($payload);
            return $this->json($result->getPublishedBoard()->getUrl());
        } catch (Exception $e) {
            $errorMessage = 'Dashboard not created: ' . $e->getMessage();
            $appLogger->error($errorMessage, ['dashboardID' => $id]);
            return $this->json($errorMessage, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
