<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\RetailerEnum;
use App\Repository\DataSourceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DataSourceRepository::class)
 * @ORM\Table(name="data_source")
 */
class DataSource implements EntityInterface
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @var string|null
     * @ORM\Column(name="pause_reason", type="string", length=255, nullable=true)
     */
    private ?string $pauseReason;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $syntax;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $paused;

    /**
     * @var bool|null
     * @ORM\Column(name="view_only", type="boolean", nullable=true)
     */
    private ?bool $viewOnly;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $type;

    /**
     * @var int
     * @ORM\Column(name="test_redash_id", type="integer")
     */
    private int $testRedashId;

    /**
     * @var int|null
     * @ORM\Column(name="prod_redash_id", type="integer", nullable=true)
     */
    private ?int $prodRedashId = null;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $retailer = null;

    /**
     * @ORM\ManyToOne(targetEntity=RedashUser::class, inversedBy="dataSources")
     */
    private $redashuser;

    /**
     * @return string
     */
    public function getRetailer(): ?string
    {
        return $this->retailer;
    }

    /**
     * @param string $retailer
     */
    public function setRetailer(string $retailer): void
    {
        $this->retailer = $retailer;
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
    public function getPauseReason(): ?string
    {
        return $this->pauseReason;
    }

    /**
     * @param string|null $pauseReason
     * @return $this
     */
    public function setPauseReason(?string $pauseReason): self
    {
        $this->pauseReason = $pauseReason;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSyntax(): ?string
    {
        return $this->syntax;
    }

    /**
     * @param string|null $syntax
     * @return $this
     */
    public function setSyntax(?string $syntax): self
    {
        $this->syntax = $syntax;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPaused(): ?int
    {
        return $this->paused;
    }

    /**
     * @param int|null $paused
     * @return $this
     */
    public function setPaused(?int $paused): self
    {
        $this->paused = $paused;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getViewOnly(): ?bool
    {
        return $this->viewOnly;
    }

    /**
     * @param bool|null $viewOnly
     * @return $this
     */
    public function setViewOnly(?bool $viewOnly): self
    {
        $this->viewOnly = $viewOnly;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     * @return $this
     */
    public function setType(?string $type): self
    {
        $this->type = $type;

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
     * @param int $testRedashId
     * @return $this
     */
    public function setTestRedashId(int $testRedashId): self
    {
        $this->testRedashId = $testRedashId;

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
     * @param int $prodRedashId
     * @return $this
     */
    public function setProdRedashId(int $prodRedashId): self
    {
        $this->prodRedashId = $prodRedashId;

        return $this;
    }

    /**
     * @param array $dataSource
     * @return DataSource
     */
    public function fromArray(array $dataSource): self
    {
        $this->setName($dataSource['name'])
            ->setPaused($dataSource['paused'])
            ->setPauseReason($dataSource['pause_reason'])
            ->setSyntax($dataSource['syntax'])
            ->setType($dataSource['type'])
            ->setViewOnly($dataSource['view_only'])
            ->setTestRedashId($dataSource['id'])
            ->setRetailer($dataSource['name']);

        return $this;
    }

    /**
     * @return RedashUser|null
     */
    public function getRedashuser(): ?RedashUser
    {
        return $this->redashuser;
    }

    /**
     * @param RedashUser|null $redashuser
     * @return $this
     */
    public function setRedashuser(?RedashUser $redashuser): self
    {
        $this->redashuser = $redashuser;

        return $this;
    }
}
