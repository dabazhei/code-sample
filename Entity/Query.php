<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\QueryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=QueryRepository::class)
 */
class Query implements EntityInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @var int|null
     * @ORM\Column(name="test_redash_id", type="integer", nullable=true)
     */
    private ?int $testRedashId;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private int $created;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $updated = null;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $description = null;

    /**
     * @var array
     * @ORM\Column(type="json", options={"jsonb": true})
     */
    private array $options = [];

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private string $hash;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private string $query;

    /**
     * @ORM\Column(type="integer")
     */
    private int $version;

    /**
     * @ORM\Column(name="is_safe", type="boolean")
     */
    private bool $isSafe;

    /**
     * @ORM\Column(name="is_archived", type="boolean")
     */
    private bool $isArchived;

    /**
     * @ORM\Column(name="is_draft", type="boolean")
     */
    private bool $isDraft;

    /**
     * @ORM\OneToMany(targetEntity=Visualization::class, mappedBy="query")
     */
    private Collection $visualization;

    /**
     * @ORM\ManyToOne(targetEntity=DataSource::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private ?DataSource $dataSource;

    /**
     * @var int|null
     * @ORM\Column(name="prod_redash_id", type="integer", nullable=true)
     */
    private ?int $prodRedashId = null;

    /**
     * @var int
     */
    private int $resultCacheTTL = 0;

    /**
     * Query constructor.
     */
    public function __construct()
    {
        $this->visualization = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return $this
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getHash(): ?string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return $this
     */
    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getQuery(): ?string
    {
        return $this->query;
    }

    /**
     * @param string $query
     * @return $this
     */
    public function setQuery(string $query): self
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getVersion(): ?int
    {
        return $this->version;
    }

    /**
     * @param int $version
     * @return $this
     */
    public function setVersion(int $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsSafe(): ?bool
    {
        return $this->isSafe;
    }

    /**
     * @param bool $isSafe
     * @return $this
     */
    public function setIsSafe(bool $isSafe): self
    {
        $this->isSafe = $isSafe;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsArchived(): ?bool
    {
        return $this->isArchived;
    }

    /**
     * @param bool $isArchived
     * @return $this
     */
    public function setIsArchived(bool $isArchived): self
    {
        $this->isArchived = $isArchived;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsDraft(): ?bool
    {
        return $this->isDraft;
    }

    /**
     * @param bool $isDraft
     * @return $this
     */
    public function setIsDraft(bool $isDraft): self
    {
        $this->isDraft = $isDraft;

        return $this;
    }

    /**
     * @param int $created
     * @return Query
     */
    public function setCreated(int $created): Query
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return int
     */
    public function getCreated(): int
    {
        return $this->created;
    }

    /**
     * @param int|null $updated
     * @return Query
     */
    public function setUpdated(?int $updated): Query
    {
        $this->updated = $updated;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getUpdated(): ?int
    {
        return $this->updated;
    }

    /**
     * @param array $queryData
     * @return Query
     */
    public function fromArray(array $queryData): self
    {
        $queryData['options']['max_age'] = $this->getResultCacheTTL();
        $updated = isset($queryData['updated_at'])
            ? strtotime($queryData['updated_at'])
            : null;

        $this
            ->setCreated(strtotime($queryData['created_at']))
            ->setUpdated($updated)
            ->setOptions($queryData['options'])
            ->setDescription($queryData['description'])
            ->setHash($queryData['query_hash'])
            ->setName($queryData['name'])
            ->setQuery($queryData['query'])
            ->setIsDraft($queryData['is_draft'])
            ->setIsSafe($queryData['is_safe'])
            ->setIsArchived($queryData['is_archived'])
            ->setVersion($queryData['version']);
        return $this;
    }

    /**
     * @param int|null $testRedashId
     * @return Query
     */
    public function setTestRedashId(?int $testRedashId): Query
    {
        $this->testRedashId = $testRedashId;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getTestRedashId(): ?int
    {
        return $this->testRedashId;
    }

    /**
     * @return Collection|Visualization[]
     */
    public function getVisualization(): Collection
    {
        return $this->visualization;
    }

    /**
     * @param Visualization $visualization
     * @return $this
     */
    public function addVisualization(Visualization $visualization): self
    {
        if (!$this->visualization->contains($visualization)) {
            $this->visualization[] = $visualization;
            $visualization->setQuery($this);
        }

        return $this;
    }

    /**
     * @param Visualization $visualization
     * @return $this
     */
    public function removeVisualization(Visualization $visualization): self
    {
        if ($this->visualization->contains($visualization)) {
            $this->visualization->removeElement($visualization);
            // set the owning side to null (unless already changed)
            if ($visualization->getQuery() === $this) {
                $visualization->setQuery(null);
            }
        }

        return $this;
    }

    /**
     * @return DataSource|null
     */
    public function getDataSource(): ?DataSource
    {
        return $this->dataSource;
    }

    /**
     * @param DataSource|null $dataSource
     * @return $this
     */
    public function setDataSource(?DataSource $dataSource): self
    {
        $this->dataSource = $dataSource;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getProdRedashId(): ?int
    {
        return $this->prodRedashId;
    }

    /**
     * @param int|null $prodRedashId
     * @return $this
     */
    public function setProdRedashId(?int $prodRedashId): self
    {
        $this->prodRedashId = $prodRedashId;

        return $this;
    }

    /**
     * Return Query SQL parameters if exist. key - parameter name; value - parameter value
     *
     * @return array
     */
    public function getParameters(): array
    {
        $parameters = [];
        $options = $this->getOptions();
        if (empty($options['parameters']) === false) {
            foreach ($options['parameters'] as $parameter) {
                $parameters[$parameter['name']] = $parameter['value'];
            }
        }

        return $parameters;
    }

    /**
     * @return array
     */
    public function getDependencyQueries(): array
    {
        $options = $this->getOptions();
        $dependencyQueries = isset($options['parameters']) ? array_column($options['parameters'], 'queryId') : [];

        return array_unique($dependencyQueries);
    }

    /**
     *
     * @param array $rootTestToProdIds Key is a root query id in Redash Test, value is a root query id in Redash Prod
     * @return void
     */
    public function setDependencyQueries(array $rootTestToProdIds): void
    {
        $options = $this->getOptions();
        foreach ($options['parameters'] as $i => $iValue) {
            if (isset($options['parameters'][$i]['queryId'])) {
                $rootId = $options['parameters'][$i]['queryId'];
                $options['parameters'][$i]['queryId'] = $rootTestToProdIds[$rootId];
            }
        }
        $this->setOptions($options);
    }

    /**
     * @return bool
     */
    public function hasSelfParent(): bool
    {
        $options = $this->getOptions();
        $parentQueries = isset($options['parameters']) ? array_column($options['parameters'], 'parentQueryId') : [];

        return count($parentQueries) > 0;
    }

    /**
     * @return void
     */
    public function setSelfParent(): void
    {
        $options = $this->getOptions();
        foreach ($options['parameters'] as $i => $iValue) {
            if (isset($options['parameters'][$i]['parentQueryId'])) {
                $options['parameters'][$i]['parentQueryId'] = $this->getProdRedashId();
            }
        }
        $this->setOptions($options);
    }

    /**
     * @param int $resultCacheTTL
     * @return $this
     */
    public function setResultCacheTTL(int $resultCacheTTL): self
    {
        $this->resultCacheTTL = $resultCacheTTL;

        return $this;
    }

    /**
     * @return int
     */
    public function getResultCacheTTL(): int
    {
        return $this->resultCacheTTL;
    }
}
