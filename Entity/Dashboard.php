<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DashboardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DashboardRepository::class)
 */
class Dashboard implements EntityInterface
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @var int
     * @ORM\Column(name="test_redash_id", type="integer", unique=true)
     */
    private int $testRedashId;

    /**
     * @var int
     * @ORM\Column(name="prod_redash_id", type="integer", unique=true, nullable=true)
     */
    private int $prodRedashId;

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
     * @var bool
     * @ORM\Column(name="is_archived", type="boolean")
     */
    private bool $isArchived;

    /**
     * @var bool
     * @ORM\Column(name="is_favorite",type="boolean")
     */
    private bool $isFavorite;

    /**
     * @var bool
     * @ORM\Column(name="is_draft", type="boolean")
     */
    private bool $isDraft;

    /**
     * @var array
     * @ORM\Column(type="json", options={"jsonb": true})
     */
    private array $tags = [];

    /**
     * @var array
     * @ORM\Column(type="json", options={"jsonb": true})
     */
    private array $layout = [];

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $slug;

    /**
     * @ORM\Column(type="integer")
     */
    private int $version;

    /**
     * @ORM\Column(name="is_filters_enabled", type="boolean")
     */
    private bool $isFiltersEnabled;

    /**
     * @ORM\ManyToMany(targetEntity=Widget::class, inversedBy="dashboards")
     */
    private Collection $widget;

    /**
     * @var bool
     * @ORM\Column(name="is_active", type="boolean", options={"default" : false})
     */
    private bool $isActive = false;

    /**
     * @ORM\OneToMany(targetEntity=PublishedDashboard::class, mappedBy="dashboard", orphanRemoval=true)
     */
    private $publishedDashboards;

    /**
     * Dashboard constructor.
     */
    public function __construct()
    {
        $this->widget = new ArrayCollection();
        $this->publishedDashboards = new ArrayCollection();
    }

    /**
     * @param array $dashboardData
     * @return Dashboard
     */
    public function fromArray(array $dashboardData): self
    {
        $updated = isset($dashboardData['updated_at'])
            ? strtotime($dashboardData['updated_at'])
            : null;

        $this
            ->setSlug($dashboardData['slug'])
            ->setCreated(strtotime($dashboardData['created_at']))
            ->setIsArchived($dashboardData['is_archived'])
            ->setIsDraft($dashboardData['is_draft'])
            ->setIsFavorite($dashboardData['is_favorite'])
            ->setIsFiltersEnabled($dashboardData['dashboard_filters_enabled'])
            ->setLayout($dashboardData['layout'])
            ->setName($dashboardData['name'])
            ->setTags($dashboardData['tags'])
            ->setUpdated($updated)
            ->setVersion($dashboardData['version']);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
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
    public function getIsFavorite(): ?bool
    {
        return $this->isFavorite;
    }

    /**
     * @param bool $isFavorite
     * @return $this
     */
    public function setIsFavorite(bool $isFavorite): self
    {
        $this->isFavorite = $isFavorite;

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
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     * @return $this
     */
    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @return array
     */
    public function getLayout(): array
    {
        return $this->layout;
    }

    /**
     * @param array $layout
     * @return $this
     */
    public function setLayout(array $layout): self
    {
        $this->layout = $layout;

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
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return $this
     */
    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

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
    public function getIsFiltersEnabled(): ?bool
    {
        return $this->isFiltersEnabled;
    }

    /**
     * @param bool $isFiltersEnabled
     * @return $this
     */
    public function setIsFiltersEnabled(bool $isFiltersEnabled): self
    {
        $this->isFiltersEnabled = $isFiltersEnabled;

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
     */
    public function setProdRedashId(int $prodRedashId): void
    {
        $this->prodRedashId = $prodRedashId;
    }

    /**
     * @return int
     */
    public function getCreated(): int
    {
        return $this->created;
    }

    /**
     * @param int $created
     * @return Dashboard
     */
    public function setCreated(int $created): Dashboard
    {
        $this->created = $created;
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
     * @param int|null $updated
     * @return Dashboard
     */
    public function setUpdated(?int $updated): Dashboard
    {
        $this->updated = $updated;
        return $this;
    }

    /**
     * @return Collection|Widget[]
     */
    public function getWidget(): Collection
    {
        return $this->widget;
    }

    /**
     * @param Widget $widget
     * @return $this
     */
    public function addWidget(Widget $widget): self
    {
        if (!$this->widget->contains($widget)) {
            $this->widget[] = $widget;
        }

        return $this;
    }

    /**
     * @param Widget $widget
     * @return $this
     */
    public function removeWidget(Widget $widget): self
    {
        if ($this->widget->contains($widget)) {
            $this->widget->removeElement($widget);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection|PublishedDashboard[]
     */
    public function getPublishedDashboards(): Collection
    {
        return $this->publishedDashboards;
    }

    public function addPublishedDashboard(PublishedDashboard $publishedDashboard): self
    {
        if (!$this->publishedDashboards->contains($publishedDashboard)) {
            $this->publishedDashboards[] = $publishedDashboard;
            $publishedDashboard->setDashboard($this);
        }

        return $this;
    }

    public function removePublishedDashboard(PublishedDashboard $publishedDashboard): self
    {
        if ($this->publishedDashboards->contains($publishedDashboard)) {
            $this->publishedDashboards->removeElement($publishedDashboard);
            // set the owning side to null (unless already changed)
            if ($publishedDashboard->getDashboard() === $this) {
                $publishedDashboard->setDashboard(null);
            }
        }

        return $this;
    }
}
