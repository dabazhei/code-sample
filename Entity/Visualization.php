<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\VisualizationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VisualizationRepository::class)
 */
class Visualization implements EntityInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="integer")
     */
    private int $created;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $updated;

    /**
     * @var int|null
     * @ORM\Column(name="test_redash_id", type="integer", nullable=true)
     */
    private ?int $testRedashId;

    /**
     * @var int|null
     * @ORM\Column(name="prod_redash_id", type="integer", nullable=true)
     */
    private ?int $prodRedashId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $type;

    /**
     * @ORM\Column(type="json", options={"jsonb": true})
     */
    private array $options = [];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\OneToMany(targetEntity=Widget::class, mappedBy="visualization")
     */
    private Collection $widgets;

    /**
     * @var Query|null
     * @ORM\ManyToOne(targetEntity=Query::class, inversedBy="visualization")
     */
    private ?Query $query = null;

    /**
     * Visualization constructor.
     */
    public function __construct()
    {
        $this->widgets = new ArrayCollection();
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
     * @return int|null
     */
    public function getCreated(): ?int
    {
        return $this->created;
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
     * @param int|null $prodRedashId
     * @return $this
     */
    public function setProdRedashId(?int $prodRedashId): self
    {
        $this->prodRedashId = $prodRedashId;

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
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;

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
     * @return Collection|Widget[]
     */
    public function getWidgets(): Collection
    {
        return $this->widgets;
    }

    /**
     * @param Widget $widget
     * @return $this
     */
    public function addWidget(Widget $widget): self
    {
        if (!$this->widgets->contains($widget)) {
            $this->widgets[] = $widget;
            $widget->setVisualization($this);
        }

        return $this;
    }

    /**
     * @param Widget $widget
     * @return $this
     */
    public function removeWidget(Widget $widget): self
    {
        if ($this->widgets->contains($widget)) {
            $this->widgets->removeElement($widget);
            // set the owning side to null (unless already changed)
            if ($widget->getVisualization() === $this) {
                $widget->setVisualization(null);
            }
        }

        return $this;
    }

    /**
     * @return Query|null
     */
    public function getQuery(): ?Query
    {
        return $this->query;
    }

    /**
     * @param Query|null $query
     * @return $this
     */
    public function setQuery(?Query $query): self
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @param array $visualization
     * @return Visualization
     */
    public function fromArray(array $visualization): self
    {
        $updated = isset($visualization['updated_at'])
            ? strtotime($visualization['updated_at'])
            : null;

        $this->setDescription($visualization['description'])
            ->setOptions($visualization['options'])
            ->setCreated(strtotime($visualization['created_at']))
            ->setUpdated($updated)
            ->setType($visualization['type'])
            ->setName($visualization['name']);
        return $this;
    }
}
