<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PublishedDashboardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PublishedDashboardRepository::class)
 */
class PublishedDashboard
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="json", options={"jsonb": true})
     */
    private array $assortment = [];

    /**
     * @ORM\Column(type="json", options={"jsonb": true})
     */
    private array $location = [];

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $url;

    /**
     * @ORM\Column(type="integer", unique=true)
     */
    private int $prodRedashId;

    /**
     * @ORM\ManyToOne(targetEntity=RedashUser::class, inversedBy="publishedDashboards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $redashUser;

    /**
     * @ORM\ManyToOne(targetEntity=Dashboard::class, inversedBy="publishedDashboards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $dashboard;

    /**
     * @ORM\OneToMany(targetEntity=Widget::class, mappedBy="publishedBoard")
     */
    private Collection $widgets;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $slug;

    /**
     * PublishedDashboard constructor.
     */
    public function __construct()
    {
        $this->widgets = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getAssortment(): array
    {
        return $this->assortment;
    }

    /**
     * @param array $assortment
     * @return $this
     */
    public function setAssortment(array $assortment): self
    {
        $this->assortment = $assortment;

        return $this;
    }

    /**
     * @return array
     */
    public function getLocation(): array
    {
        return $this->location;
    }

    /**
     * @param array $location
     * @return $this
     */
    public function setLocation(array $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @param array $restriction
     * @return bool
     */
    public function isRestrictionsEqual(array $restriction): bool
    {
        $assortment = isset($restriction['assortment']) ? explode(',', $restriction['assortment']) : [];
        $location = isset($restriction['location']) ? explode(',', $restriction['location']) : [];

        return (array_diff($this->getAssortment(), $assortment) === array_diff($assortment, $this->getAssortment()))
            && (array_diff($this->getLocation(), $location) === array_diff($location, $this->getLocation()));
    }

    /**
     * @return int
     */
    public function getProdRedashId(): int
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
     * @return RedashUser
     */
    public function getRedashUser(): RedashUser
    {
        return $this->redashUser;
    }

    /**
     * @param RedashUser $redashUser
     * @return $this
     */
    public function setRedashUser(RedashUser $redashUser): self
    {
        $this->redashUser = $redashUser;

        return $this;
    }

    /**
     * @return Dashboard
     */
    public function getDashboard(): Dashboard
    {
        return $this->dashboard;
    }

    /**
     * @param Dashboard $dashboard
     * @return $this
     */
    public function setDashboard(Dashboard $dashboard): self
    {
        $this->dashboard = $dashboard;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
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
            $widget->setPublishedBoard($this);
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
            if ($widget->getPublishedBoard() === $this) {
                $widget->setPublishedBoard(null);
            }
        }

        return $this;
    }
}
