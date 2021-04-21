<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\WidgetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=WidgetRepository::class)
 */
class Widget implements EntityInterface
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
     * @var PublishedDashboard|null
     * @ORM\ManyToOne(targetEntity=PublishedDashboard::class, inversedBy="widgets")
     */
    private ?PublishedDashboard $publishedBoard;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private int $created;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $updated;

    /**
     * @var array
     * @ORM\Column(type="json", options={"jsonb": true})
     */
    private array $options = [];

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private string $text = '';

    /**
     * @ORM\ManyToMany(targetEntity=Dashboard::class, mappedBy="widget")
     */
    private $dashboards;

    /**
     * @var Visualization|null
     * @ORM\ManyToOne(targetEntity=Visualization::class, inversedBy="widgets")
     */
    private ?Visualization $visualization = null;

    /**
     * Widget constructor.
     */
    public function __construct()
    {
        $this->dashboards = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getOptions(): array
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
     * @param int $created
     * @return $this
     */
    public function setCreated(int $created): self
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCreated(): ?int
    {
        return $this->created;
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
     * @return $this
     */
    public function setUpdated(?int $updated): self
    {
        $this->updated = $updated;
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
     * @param int|null $testRedashId
     * @return $this
     */
    public function setTestRedashId(?int $testRedashId): self
    {
        $this->testRedashId = $testRedashId;
        return $this;
    }

    /**
     * @return PublishedDashboard|null
     */
    public function getPublishedBoard(): ?int
    {
        return $this->publishedBoard;
    }

    /**
     * @param PublishedDashboard|null $publishedBoard
     * @return $this
     */
    public function setPublishedBoard(?PublishedDashboard $publishedBoard): self
    {
        $this->publishedBoard = $publishedBoard;
        return $this;
    }

    /**
     * @param array $widgetData
     * @return Widget
     */
    public function fromArray(array $widgetData): self
    {
        $updated = isset($widgetData['updated_at'])
            ? strtotime($widgetData['updated_at'])
            : null;

        $this
            ->setCreated(strtotime($widgetData['created_at']))
            ->setUpdated($updated)
            ->addDashboard($widgetData['dashboard'])
            ->setOptions($widgetData['options'])
            ->setText($widgetData['text']);

        return $this;
    }

    /**
     * @param string $text
     * @return Widget
     */
    public function setText(string $text): Widget
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return Collection|Dashboard[]
     */
    public function getDashboards(): Collection
    {
        return $this->dashboards;
    }

    /**
     * @param Dashboard $dashboard
     * @return $this
     */
    public function addDashboard(Dashboard $dashboard): self
    {
        if (!$this->dashboards->contains($dashboard)) {
            $this->dashboards[] = $dashboard;
            $dashboard->addWidget($this);
        }

        return $this;
    }

    /**
     * @param Dashboard $dashboard
     * @return $this
     */
    public function removeDashboard(Dashboard $dashboard): self
    {
        if ($this->dashboards->contains($dashboard)) {
            $this->dashboards->removeElement($dashboard);
            $dashboard->removeWidget($this);
        }

        return $this;
    }

    /**
     * @return Visualization|null
     */
    public function getVisualization(): ?Visualization
    {
        return $this->visualization;
    }

    /**
     * @param Visualization|null $visualization
     * @return $this
     */
    public function setVisualization(?Visualization $visualization): self
    {
        $this->visualization = $visualization;

        return $this;
    }
}
