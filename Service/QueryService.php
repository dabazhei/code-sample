<?php

declare(strict_types=1);

namespace App\Service;

use App\DashboardLink\DashboardRestrictionsGroups;
use App\Entity\DataSource;
use App\Entity\Query;
use App\Entity\RedashUser;
use App\Service\RedashProvider\RedashSourceWebService;
use App\Service\RedashProvider\RedashTargetWebService;
use App\Utility\DateRanges;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

/**
 * Class QueryService
 * @package App\Service
 */
final class QueryService
{

    public const QUERY_PLACEHOLDER_TPL = '--%s_restrictions_query';
    public const ID_PLACEHOLDER_TPL = '--%s_restrictions_id';

    /**
     * @var array
     */
    private array $placeholderToReplacementMap = [
        '--assortment_restrictions_id' => 'and assortment_group_id in(%s)',
        '--assortment_restrictions_query' => 'and plu in (select distinct toUInt64(plu) from internal_data.assortment_groups_products where assortment_group_id in(%s))',
        '--location_restrictions_id' => 'and location_group_id in(%s)',
        '--location_restrictions_query' => 'and toUInt64(shop_id) in (select distinct toUInt64(shop_id) from internal_data.location_groups_shops where location_group_id in(%s))'
    ];
    /**
     * @var EntityManager
     */
    private EntityManager $entityManager;
    /**
     * @var RedashSourceWebService
     */
    private RedashSourceWebService $redashSourceWS;
    /**
     * @var RedashTargetWebService
     */
    private RedashTargetWebService $redashTargetWS;
    /**
     * @var int
     */
    private int $queryResultCacheTTL;

    /**
     * QueryService constructor.
     * @param int $queryResultCacheTTL
     * @param EntityManager $entityManager
     * @param RedashSourceWebService $redashSourceWS
     * @param RedashTargetWebService $redashTargetWS
     */
    public function __construct(
        int $queryResultCacheTTL,
        EntityManager $entityManager,
        RedashSourceWebService $redashSourceWS,
        RedashTargetWebService $redashTargetWS
    )
    {
        $this->queryResultCacheTTL = $queryResultCacheTTL;
        $this->entityManager = $entityManager;
        $this->redashSourceWS = $redashSourceWS;
        $this->redashTargetWS = $redashTargetWS;
    }

    /**
     * @param Query $query
     * @param DashboardRestrictionsGroups $restrictions
     */
    public function enrichQuery(Query $query, DashboardRestrictionsGroups $restrictions): void
    {
        $replacements = $this->prepareReplacementPairs($restrictions->toArray());

        $query->setQuery(strtr($query->getQuery(), $replacements));
    }

    /**
     * @param array $restrictions
     * @return array
     */
    private function prepareReplacementPairs(array $restrictions): array
    {
        $pairs = [];
        foreach ($restrictions as $restrictionName => $restrictionValue)
        {
            $pairs += $this->getIdReplacementPair($restrictionName, $restrictionValue);
            $pairs += $this->getQueryReplacementPair($restrictionName, $restrictionValue);
        }

        return $pairs;
    }

    /**
     * @param string $restrictionName
     * @param $restrictionValue
     * @return array
     */
    private function getIdReplacementPair(string $restrictionName, $restrictionValue): array
    {
        $placeholder = sprintf(self::ID_PLACEHOLDER_TPL, $restrictionName);
        $replacement = sprintf($this->placeholderToReplacementMap[$placeholder], $restrictionValue);

        return [$placeholder => $replacement];
    }

    /**
     * @param string $restrictionName
     * @param $restrictionValue
     * @return array
     */
    private function getQueryReplacementPair(string $restrictionName, $restrictionValue): array
    {
        $placeholder = sprintf(self::QUERY_PLACEHOLDER_TPL, $restrictionName);
        $replacement = sprintf($this->placeholderToReplacementMap[$placeholder], $restrictionValue);

        return [$placeholder => $replacement];
    }

    /**
     * @param Query $query
     * @param RedashUser $redashUser
     * @param DashboardRestrictionsGroups $restrictions
     * @return void
     * @throws ExceptionInterface
     * @throws ORMException
     */
    public function setQueryParamValues(Query $query, RedashUser $redashUser, DashboardRestrictionsGroups $restrictions): void
    {
        $options = $query->getOptions();

        foreach ($options['parameters'] as $key => $parameter) {
            if (isset($parameter['queryId']) === true) {
                $parameter = $this->setValueFromDependencyQuery($parameter, $redashUser, $restrictions);
            }
            if ($this->isValueDynamic($parameter['value'])){
                $parameter = $this->setDynamicValue($parameter);
            }
            $options['parameters'][$key] = $parameter;
        }

        $query->setOptions($options);
        $this->entityManager->persist($query);
    }

    /**
     * @param $value
     * @param array $dependencyQueryResult
     * @return array|string|int
     */
    public function updateParamFromQueryResult($value, array $dependencyQueryResult)
    {
        $parameterValues = $dependencyQueryResult[0] ?? [];
        $newValue = $parameterValues['value'] ?? array_shift($parameterValues) ?? $parameterValues;
        return (is_array($value) && !is_array($newValue)) ? [$newValue] : $newValue;
    }

    /**
     * @param array $parameter
     * @param RedashUser $redashUser
     * @param DashboardRestrictionsGroups $restrictions
     * @throws ExceptionInterface
     * @throws ORMException
     */
    private function setValueFromDependencyQuery(array $parameter, RedashUser $redashUser, DashboardRestrictionsGroups $restrictions): array
    {
        $dataSourceRepo = $this->entityManager->getRepository(DataSource::class);
        $queryData = $this->redashSourceWS->getQuery($parameter['queryId']);
        $dataSource = $dataSourceRepo->findOneBy(
            [
                'testRedashId' => $queryData['data_source_id'],
                'redashuser' => $redashUser->getId()
            ]
        );
        $dependencyQuery = (new Query())
            ->setDataSource($dataSource)
            ->setTestRedashId($parameter['queryId'])
            ->fromArray($queryData)
        ;
        $this->enrichQuery($dependencyQuery, $restrictions);
        $prodId = $this->redashTargetWS->createQuery($dependencyQuery);
        $dependencyQuery->setProdRedashId($prodId);

        $this->redashTargetWS->refreshQuery($dependencyQuery->getProdRedashId());
        $this->entityManager->persist($dependencyQuery);

        $dependencyQueryResult = $this->redashTargetWS->runQueryAndWaitResult($dependencyQuery->getProdRedashId());
        $parameter['value'] = $this->updateParamFromQueryResult($parameter['value'], $dependencyQueryResult);
        $parameter['queryId'] = $dependencyQuery->getProdRedashId();

        return $parameter;
    }

    /**
     * @param array $parameter
     * @return array
     */
    private function setDynamicValue(array $parameter): array
    {
        $methodName = $this->queryParameterToMethodName($parameter['value']);
        if (method_exists( DateRanges::class, $methodName )) {
            $parameter['value'] = DateRanges::$methodName();
        }

        return $parameter;
    }

    /**
     * @param string $paramName
     * @return string
     */
    private function queryParameterToMethodName(string $paramName): string
    {
        $paramName = str_replace('d_', '', $paramName);
        return str_replace('_', '', lcfirst(ucwords($paramName, '_')));
    }

    /**
     * @param $parameter
     * @return bool
     */
    private function isValueDynamic($parameter): bool
    {
        return is_string($parameter) && strpos($parameter, 'd_') === 0;
    }
}