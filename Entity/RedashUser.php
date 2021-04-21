<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RedashUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RedashUserRepository::class)
 */
class RedashUser implements EntityInterface
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @var int|null
     * @ORM\Column(name="redash_id", type="integer", nullable=true)
     */
    private ?int $redashId;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private string $email;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private string $password;

    /**
     * @var array
     * @ORM\Column(type="json", options={"jsonb": true})
     */
    private array $groups = [];

    /**
     * @var string
     * @ORM\Column(name="auth_type", type="string", length=255, nullable=true)
     */
    private string $authType;

    /**
     * @var bool
     * @ORM\Column(name="is_disabled", type="boolean")
     */
    private bool $isDisabled;

    /**
     * @var int|null
     * @ORM\Column(name="updated_at", type="integer", nullable=true)
     */
    private ?int $updatedAt;

    /**
     * @ORM\Column(name="profile_image_url", type="string", length=255 , nullable=true)
     */
    private string $profileImageUrl;

    /**
     * @var bool
     * @ORM\Column(name="is_invitation_pending", type="boolean")
     */
    private bool $isInvitationPending;

    /**
     * @var int
     * @ORM\Column(name="created_at", type="integer")
     */
    private int $createdAt;

    /**
     * @var int|null
     * @ORM\Column(name="disabled_at", type="integer", nullable=true)
     */
    private ?int $disabledAt;

    /**
     * @var bool
     * @ORM\Column(name="is_email_verified", type="boolean")
     */
    private bool $isEmailVerified;

    /**
     * @var int|null
     * @ORM\Column(name="active_at", type="integer", nullable=true)
     */
    private ?int $activeAt;

    /**
     * @ORM\OneToMany(targetEntity=DataSource::class, mappedBy="redashuser")
     */
    private Collection $dataSources;

    /**
     * @ORM\OneToMany(targetEntity=PublishedDashboard::class, mappedBy="redashUser", orphanRemoval=true)
     */
    private Collection $publishedDashboards;

    public function __construct()
    {
        $this->dataSources = new ArrayCollection();
        $this->publishedDashboards = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getRedashId(): ?int
    {
        return $this->redashId;
    }

    /**
     * @param int $redashId
     * @return $this
     */
    public function setRedashId(int $redashId): self
    {
        $this->redashId = $redashId;

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
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getGroups(): ?array
    {
        return $this->groups;
    }

    /**
     * @param array $groups
     * @return $this
     */
    public function setGroups(array $groups): self
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAuthType(): ?string
    {
        return $this->authType;
    }

    /**
     * @param string $authType
     * @return $this
     */
    public function setAuthType(string $authType): self
    {
        $this->authType = $authType;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsDisabled(): ?bool
    {
        return $this->isDisabled;
    }

    /**
     * @param bool $isDisabled
     * @return $this
     */
    public function setIsDisabled(bool $isDisabled): self
    {
        $this->isDisabled = $isDisabled;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getUpdatedAt(): ?int
    {
        return $this->updatedAt;
    }

    /**
     * @param int|null $updatedAt
     * @return $this
     */
    public function setUpdatedAt(?int $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getProfileImageUrl(): ?string
    {
        return $this->profileImageUrl;
    }

    /**
     * @param string $profileImageUrl
     * @return $this
     */
    public function setProfileImageUrl(string $profileImageUrl): self
    {
        $this->profileImageUrl = $profileImageUrl;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsInvitationPending(): ?bool
    {
        return $this->isInvitationPending;
    }

    /**
     * @param bool $isInvitationPending
     * @return $this
     */
    public function setIsInvitationPending(bool $isInvitationPending): self
    {
        $this->isInvitationPending = $isInvitationPending;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCreatedAt(): ?int
    {
        return $this->createdAt;
    }

    /**
     * @param int $createdAt
     * @return $this
     */
    public function setCreatedAt(int $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getDisabledAt(): ?int
    {
        return $this->disabledAt;
    }

    /**
     * @param int|null $disabledAt
     * @return $this
     */
    public function setDisabledAt(?int $disabledAt): self
    {
        $this->disabledAt = $disabledAt;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsEmailVerified(): ?bool
    {
        return $this->isEmailVerified;
    }

    /**
     * @param bool $isEmailVerified
     * @return $this
     */
    public function setIsEmailVerified(bool $isEmailVerified): self
    {
        $this->isEmailVerified = $isEmailVerified;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getActiveAt(): ?int
    {
        return $this->activeAt;
    }

    /**
     * @param int|null $activeAt
     * @return $this
     */
    public function setActiveAt(?int $activeAt): self
    {
        $this->activeAt = $activeAt;

        return $this;
    }

    /**
     * @param array $userData
     * @return $this
     */
    public function fromArray(array $userData): self
    {
        $updated = isset($userData['updated_at'])
            ? strtotime($userData['updated_at'])
            : null;

        $active = isset($userData['active_at'])
            ? strtotime($userData['active_at'])
            : null;

        $this
            ->setRedashId($userData['id'])
            ->setName($userData['name'])
            ->setAuthType($userData['auth_type'])
            ->setCreatedAt(strtotime($userData['created_at']))
            ->setEmail($userData['email'])
            ->setGroups($userData['groups'])
            ->setIsDisabled($userData['is_disabled'])
            ->setActiveAt($active)
            ->setUpdatedAt($updated)
            ->setProfileImageUrl($userData['profile_image_url'])
            ->setIsInvitationPending($userData['is_invitation_pending'])
            ->setIsEmailVerified($userData['is_email_verified'])
        ;

        return $this;
    }

    /**
     * @return Collection|DataSource[]
     */
    public function getDataSources(): Collection
    {
        return $this->dataSources;
    }

    /**
     * @param DataSource $dataSource
     * @return $this
     */
    public function addDataSource(DataSource $dataSource): self
    {
        if (!$this->dataSources->contains($dataSource)) {
            $this->dataSources[] = $dataSource;
            $dataSource->setRedashuser($this);
        }

        return $this;
    }

    /**
     * @param DataSource $dataSource
     * @return $this
     */
    public function removeDataSource(DataSource $dataSource): self
    {
        if ($this->dataSources->contains($dataSource)) {
            $this->dataSources->removeElement($dataSource);
            // set the owning side to null (unless already changed)
            if ($dataSource->getRedashuser() === $this) {
                $dataSource->setRedashuser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PublishedDashboard[]
     */
    public function getPublishedDashboards(): Collection
    {
        return $this->publishedDashboards;
    }

    /**
     * @param PublishedDashboard $publishedDashboard
     * @return $this
     */
    public function addPublishedDashboard(PublishedDashboard $publishedDashboard): self
    {
        if (!$this->publishedDashboards->contains($publishedDashboard)) {
            $this->publishedDashboards[] = $publishedDashboard;
            $publishedDashboard->setRedashUser($this);
        }

        return $this;
    }

    /**
     * @param PublishedDashboard $publishedDashboard
     * @return $this
     */
    public function removePublishedDashboard(PublishedDashboard $publishedDashboard): self
    {
        if ($this->publishedDashboards->contains($publishedDashboard)) {
            $this->publishedDashboards->removeElement($publishedDashboard);
            // set the owning side to null (unless already changed)
            if ($publishedDashboard->getRedashUser() === $this) {
                $publishedDashboard->setRedashUser(null);
            }
        }

        return $this;
    }
}
