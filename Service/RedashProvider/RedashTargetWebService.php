<?php

declare(strict_types=1);

namespace App\Service\RedashProvider;

use App\DataSource\ConnectionParamsFactory;
use App\Entity\RedashUser;
use App\Exception\DataSourceCreationException;
use App\Exception\DashboardCreationException;
use App\Exception\QueryCreationException;
use App\Exception\UserCreationException;
use App\Exception\VisualizationCreationException;
use App\Exception\WidgetAddingException;
use App\Entity\Dashboard;
use App\Entity\DataSource;
use App\Entity\Query;
use App\Security\User;
use App\Entity\Visualization;
use App\Entity\Widget;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

/**
 * Class RedashTargetWebService
 * @package App\Service\RedashProvider
 */
final class RedashTargetWebService
{
    /**
     * @var TargetClient
     */
    protected TargetClient $client;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * RedashTargetWebService constructor.
     * @param TargetClient $client
     * @param LoggerInterface $appLogger
     */
    public function __construct(TargetClient $client, LoggerInterface $appLogger)
    {
        $this->client = $client;
        $this->logger = $appLogger;
    }

    /**
     * @param Dashboard $dashboard
     * @return array
     * @throws ExceptionInterface
     */
    public function createDashboard(Dashboard $dashboard): array
    {
        $this->logger->debug("Copy dashboard {$dashboard->getId()} to redash");
        try{
            $request = $this->client->createRequest('POST', 'dashboards');
            $request->setBody(['name' => $dashboard->getName()]);
            $response = $this->client->send($request);
        } catch (Exception $e) {
            throw new DashboardCreationException('Дашборд не может быть создан: ' . $e->getMessage());
        }

        return $response->asArray();
    }

    /**
     * @param Query $query
     * @return int
     * @throws ExceptionInterface
     */
    public function createQuery(Query $query): int
    {
        $this->logger->debug("Copy query to redash");
        try {
            $request = $this->client->createRequest('POST', 'queries');
            $request->setBody(
                [
                    'name' => $query->getName(),
                    'data_source_id' => $query->getDataSource()->getProdRedashId(),
                    'query' => $query->getQuery(),
                    'description' => $query->getDescription(),
                    'options' => $query->hasSelfParent() ? ['parameters' => []] : $query->getOptions(),
                ]
            );
            $response = $this->client->send($request);
        } catch (Exception $e) {
            throw new QueryCreationException('Запрос не может быть создан: ' . $e->getMessage());
        }

        $id = (int)$response->get('id');

        if ($query->hasSelfParent()) {
            $query->setProdRedashId($id);
            $query->setSelfParent();
            $this->updateQuery($query);
        }

        return $id;
    }

    /**
     * @param int $queryId
     * @return Response
     * @throws ExceptionInterface
     */
    public function refreshQuery(int $queryId): Response
    {
        $this->logger->debug("Refresh query {$queryId}");
        try{
            $request = $this->client->createRequest('POST', "queries/{$queryId}/refresh");
            $response = $this->client->send($request);
        } catch (Exception $e) {
            throw new QueryCreationException("Ошибка при запуске запроса {$queryId}: " . $e->getMessage());
        }

        return $response;
    }

    /**
     * @param int $queryId
     * @param array $queryParameters
     * @return array
     * @throws ExceptionInterface
     */
    public function runQuery(int $queryId, array $queryParameters = []): array
    {
        $this->logger->debug("Run query {$queryId}");
        try{
            $request = $this->client->createRequest('POST', "queries/{$queryId}/results");
            if (empty($queryParameters) === false) {
                $request->setBody(
                    [
                        'parameters' => $queryParameters,
                        "max_age"=> 0
                    ]
                );
            } else {
                $request->setBody(["max_age"=> 0]);
            }
            $response = $this->client->send($request);
        } catch (Exception $e) {
            throw new QueryCreationException("Ошибка при выполнении запроса {$queryId}: " . $e->getMessage());
        }

        return $response->asArray();
    }

    /**
     * @param int $queryId
     * @param array $queryParameters
     * @return array
     * @throws ExceptionInterface
     */
    public function runQueryAndWaitResult(int $queryId, array $queryParameters = []): array
    {
        $result = $this->runQuery($queryId, $queryParameters);
        if(isset($result['query_result']['data']['rows'])) {
            return $result['query_result']['data']['rows'];
        }
        $jobId = $result['job']['id'];
        $timeToStop = strtotime( '+30 second' );
        while (true) {
            $queryResult = $this->getQueryRunstatus($jobId);
            $queryStatus = (int)$queryResult['job']['status'];
            if ($queryStatus === 3) {
                $result = $this->getQueryResult($queryResult['job']['query_result_id']);
                $data = $result['query_result']['data']['rows'];
                break;
            }
            if (time() > $timeToStop || in_array($queryStatus, [4,5])) {
                $data = [];
                break;
            }
            sleep(1);
        }

        return $data;
    }

    /**
     * @param string $jobId
     * @return array
     * @throws ExceptionInterface
     */
    public function getQueryRunStatus(string $jobId): array
    {
        $this->logger->debug("Query running status {$jobId}");
        try{
            $request = $this->client->createRequest('GET', "jobs/{$jobId}");
            $response = $this->client->send($request);
        } catch (Exception $e) {
            throw new QueryCreationException("Ошибка при выполнении запроса jbos/{$jobId}: " . $e->getMessage());
        }

        return $response->asArray();
    }

    /**
     * @param int $queryResultId
     * @return array
     * @throws ExceptionInterface
     */
    public function getQueryResult(int $queryResultId): array
    {
        $this->logger->debug("Query result {$queryResultId}");
        try{
            $request = $this->client->createRequest('GET', "query_results/{$queryResultId}");
            $response = $this->client->send($request);
        } catch (Exception $e) {
            throw new QueryCreationException("Ошибка при выполнении запроса {$queryResultId}: " . $e->getMessage());
        }

        return $response->asArray();
    }

    /**
     * @param Query $query
     * @return int
     * @throws ExceptionInterface
     */
    public function updateQuery(Query $query): int
    {
        $this->logger->debug("Update query {$query->getId()} in redash");
        try {
            $request = $this->client->createRequest('POST', 'queries/' . $query->getProdRedashId());
            $request->setBody(
                [
                    'name' => $query->getName(),
                    'data_source_id' => $query->getDataSource()->getProdRedashId(),
                    'query' => $query->getQuery(),
                    'description' => $query->getDescription(),
                    'options' => $query->getOptions(),
                ]
            );
            $response = $this->client->send($request);
        } catch (Exception $e) {
            throw new QueryCreationException('Запрос не может быть обновлен: ' . $e->getMessage());
        }

        return (int)$response->get('id');
    }

    /**
     * @param Visualization $visualization
     * @return int
     * @throws VisualizationCreationException
     * @throws ExceptionInterface
     */
    public function createVisualization(Visualization $visualization): int
    {
        $this->logger->debug("Copy visualization {$visualization->getId()} to redash");
        try {
            $request = $this->client->createRequest('POST', 'visualizations');
            $request->setBody(
                [
                    'name' => $visualization->getName(),
                    'type' => $visualization->getType(),
                    'query_id' => $visualization->getQuery()->getProdRedashId(),
                    'description' => $visualization->getDescription(),
                    'options' => $visualization->getOptions(),
                ]
            );
            $response = $this->client->send($request);
        } catch (Exception $e) {
            throw new VisualizationCreationException('Визуализация не может быть создана: ' . $e->getMessage());
        }

        return (int)$response->get('id');
    }

    /**
     * @param Widget $widget
     * @param Dashboard $dashboard
     * @return int
     * @throws WidgetAddingException
     * @throws ExceptionInterface
     */
    public function addWidget(Widget $widget, Dashboard $dashboard): int
    {
        $this->logger->debug("Copy widget {$widget->getId()} to redash");
        try {
            $request = $this->client->createRequest('POST', 'widgets');
            $request->setBody(
                [
                    'dashboard_id' => $dashboard->getProdRedashId(),
                    'text' => $widget->getText(),
                    'visualization_id' => $widget->getVisualization()->getProdRedashId(),
                    'width' => 1,
                    'options' => $widget->getOptions()
                ]
            );
            $response = $this->client->send($request);
        } catch (Exception $e) {
            throw new WidgetAddingException('Виджет не может быть создан: ' . $e->getMessage());
        }

        return (int)$response->get('id');
    }

    /**
     * @param Dashboard $dashboard
     * @return string
     * @throws ExceptionInterface
     */
    public function publishDashboard(Dashboard $dashboard): string
    {
        $this->logger->debug("Publish dashboard {$dashboard->getId()}");
        try {
            $request = $this->client->createRequest('POST', 'dashboards/' . $dashboard->getProdRedashId());
            $request->setBody(['is_draft' => false]);
            $this->client->send($request);

            $request = $this->client->createRequest('POST', "dashboards/{$dashboard->getProdRedashId()}/share");
            $response = $this->client->send($request);
        } catch (Exception $e) {
            throw new DashboardCreationException('Дашборд не может быть опубликован: ' . $e->getMessage());
        }

        return (string)$response->get('public_url');
    }

    /**
     * @param string $slug
     * @return array
     * @throws ExceptionInterface
     */
    public function getDashboard(string $slug): array
    {
        $this->logger->debug("Get dashboard data {$slug}");
        try {
            $request = $this->client->createRequest('GET', 'dashboards/' . $slug);
            $response = $this->client->send($request);
        } catch (Exception $e) {
            throw new DashboardCreationException('Нет данных для дашборда  : ' . $e->getMessage());
        }

        return $response->asArray();
    }


    /**
     * @param DataSource $dataSource
     * @param ParameterBagInterface $container
     * @return int
     * @throws ExceptionInterface
     */
    public function createDataSource(DataSource $dataSource, ParameterBagInterface $container): int
    {
        $this->logger->debug("Copy dataSource ");
        $connectionParams = (new ConnectionParamsFactory($dataSource, $container))->create();

        try {
            $request = $this->client->createRequest('POST', 'data_sources');
            $request->setBody(
                [
                    "type" => $dataSource->getType(),
                    "name" => $dataSource->getName(),
                    "view_only" => $dataSource->getViewOnly(),
                    "options" => $connectionParams->toArray(),
                ]
            );
            $response = $this->client->send($request);
        } catch (Exception $e) {
            $message = "DataSource cant be created " . $e->getMessage();
            $this->logger->debug($message);
            throw new DataSourceCreationException($message);
        }

        return (int)$response->get('id');
    }

    /**
     * @param int $userId
     * @return array
     * @throws ExceptionInterface
     */
    public function checkIfUSerExist(int $userId): array
    {
        $this->logger->debug("Check if user with id {$userId} exist on prod");
        try {
            $request = $this->client->createRequest('GET', "users/$userId");
            $response = $this->client->send($request);
            return $response->asArray();
        } catch (Exception $e) {
            throw new UserCreationException('Пользоветль не может быть создан: ' . $e->getMessage());
        }
    }

    /**
     * @param User $user
     * @param int $groupId
     * @return array
     * @throws ExceptionInterface
     */
    public function createUser(User $user, int $groupId): array
    {
        $this->logger->debug("Copy user {$user->getEmail()}");
        try {
            $request = $this->client->createRequest('POST', 'users?is_import_from_backend=true&no_invite=true');
            $request->setBody(
                [
                    "name" => $user->getLogin(),
                    "email" => $user->getEmail(),
                    "password" => $user->getPassword(),
                    "group_ids" => [$groupId],
                    "is_invitation_pending" => false,
                ]
            );

            $response = $this->client->send($request);
            return $response->asArray();
        } catch (Exception $e) {
            throw new UserCreationException('Пользоветль не может быть создан: ' . $e->getMessage());
        }
    }

    /**
     * @param User $user
     * @return int
     * @throws ExceptionInterface
     */
    public function createGroup(User $user): int
    {
        $this->logger->debug("Copy group {$user->getLogin()}");
        try {
            $request = $this->client->createRequest('POST', 'groups');
            $request->setBody(
                [
                    "name" => $user->getLogin(),
                ]
            );

            $response = $this->client->send($request);
            return (int)$response->get('id');
        } catch (Exception $e) {
            throw new UserCreationException('Группа не может быть создана: ' . $e->getMessage());
        }
    }

    /**
     * @param RedashUser $redashUser
     * @param int $groupId
     * @return array
     * @throws ExceptionInterface
     */
    public function addDataSourceToGroup(RedashUser $redashUser, int $groupId): array
    {
        $path = "groups/$groupId/data_sources?is_import_from_backend=true";
        $this->logger->debug("Starting add data sources to group with id $groupId");
        $result = [];

        foreach ($redashUser->getDataSources() as $dataSource) {
            $this->logger->debug("Add data source with id {$dataSource->getId()} to group");
            $request = $this->client->createRequest('POST', $path);
            $request->setBody(
                [
                    "id" => $groupId,
                    "data_source_id" => $dataSource->getProdRedashId(),
                    "view_only" => true
                ]
            );

            try{
                $response = $this->client->send($request);
                $result[] = $response->asArray();
            } catch (Exception $e) {
                $this->logger->debug("Cant add DataSource to Group with id $groupId" . $e->getMessage());
            }
        }

        return $result;
    }
}