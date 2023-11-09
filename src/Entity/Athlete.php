<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AthleteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: AthleteRepository::class)]
#[ORM\Table(name: 'athletes')]
#[ORM\Index(columns: ['username'], name: 'idx_username')]
#[ORM\Index(columns: ['name'], name: 'idx_name')]
class Athlete implements UserInterface
{
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_USER = 'ROLE_USER';

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::BIGINT)]
    private int $id;

    #[ORM\Column(name: 'username', type: Types::STRING, nullable: true)]
    private ?string $username;

    #[ORM\Column(name: 'name', type: Types::STRING)]
    private string $name;

    #[ORM\Column(name: 'email', type: Types::STRING, nullable: true)]
    private ?string $email;

    #[ORM\Column(name: 'country', type: Types::STRING, nullable: true)]
    private ?string $country;

    #[ORM\Column(name: 'premium', type: Types::BOOLEAN)]
    private bool $premium;

    #[ORM\Column(name: 'profile', type: Types::STRING)]
    private string $profile;

    #[ORM\Column(name: 'last_sync', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $lastSync;

    #[ORM\Column(name: 'access_token', type: Types::STRING, length: 255, nullable: true)]
    private ?string $accessToken;

    #[ORM\Column(name: 'expires_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $expiresAt;

    #[ORM\Column(name: 'refresh_token', type: Types::STRING, nullable: true)]
    private ?string $refreshToken;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * @var Collection<int, Route>
     */
    #[ORM\OneToMany(mappedBy: 'athlete', targetEntity: Route::class)]
    private Collection $routes;

    /**
     * @var Collection<int, Route>
     */
    #[ORM\ManyToMany(targetEntity: Route::class, mappedBy: 'starredBy')]
    private Collection $starredRoutes;

    private bool $isNew = false;

    public function __construct()
    {
        $this->roles = [self::ROLE_USER];
        $this->routes = new ArrayCollection();
        $this->starredRoutes = new ArrayCollection();
        $this->isNew = true;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function isPremium(): bool
    {
        return $this->premium;
    }

    public function setPremium(bool $premium): self
    {
        $this->premium = $premium;

        return $this;
    }

    public function getProfile(): string
    {
        return $this->profile;
    }

    public function setProfile(string $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    public function getLastSync(): ?\DateTime
    {
        return $this->lastSync;
    }

    public function setLastSync(?\DateTime $lastSync): self
    {
        $this->lastSync = $lastSync;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getExpiresAt(): ?\DateTime
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTime $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // Guarantee every user at least has ROLE_USER.
        // https://symfony.com/doc/current/security.html#the-user
        $roles[] = self::ROLE_USER;

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return Collection<int, Route>
     */
    public function getRoutes(): Collection
    {
        return $this->routes;
    }

    /**
     * @var Collection<int, Route> $routes
     */
    public function setRoutes(Collection $routes): self
    {
        $this->routes = $routes;

        return $this;
    }

    public function hasRoute(Route $route): bool
    {
        return $this->routes->contains($route);
    }

    public function addRoute(Route $route): self
    {
        if ($this->hasRoute($route)) {
            return $this;
        }

        $this->routes->add($route);

        return $this;
    }

    public function removeRoute(Route $route): self
    {
        if (!$this->hasRoute($route)) {
            return $this;
        }

        $this->routes->removeElement($route);

        return $this;
    }

    /**
     * @return Collection<int, Route>
     */
    public function getStarredRoutes(): Collection
    {
        return $this->starredRoutes;
    }

    /**
     * @var Collection<int, Route> $starredRoutes
     */
    public function setStarredRoutes(Collection $starredRoutes): self
    {
        $this->starredRoutes = $starredRoutes;

        return $this;
    }

    public function isStarredRoute(Route $route): bool
    {
        return $this->starredRoutes->contains($route);
    }

    public function addStarredRoute(Route $route): self
    {
        if ($this->isStarredRoute($route)) {
            return $this;
        }

        $this->starredRoutes->add($route);

        return $this;
    }

    public function removeStarredRoute(Route $route): self
    {
        if (!$this->isStarredRoute($route)) {
            return $this;
        }

        $this->starredRoutes->removeElement($route);

        return $this;
    }

    public function isNew(): bool
    {
        return $this->isNew;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->name;
    }

    public function eraseCredentials(): void
    {
        $this->accessToken = null;
        $this->refreshToken = null;
    }
}
