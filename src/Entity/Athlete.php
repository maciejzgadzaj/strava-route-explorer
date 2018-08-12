<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Athlete
 *
 * @ORM\Entity(repositoryClass="App\Repository\AthleteRepository")
 * @ORM\Table(name="athlete")
 *
 * @package App\Entity
 */
class Athlete
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, name="access_token")
     */
    private $accessToken;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $email;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $premium;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $profile;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", name="last_sync", nullable=true)
     */
    private $lastSync;

    /**
     * @var bool
     */
    private $isNew = false;

    /**
     * Athlete constructor.
     */
    public function __construct()
    {
        // This will not be saved in the database.
        $this->isNew = true;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param mixed $accessToken
     */
    public function setAccessToken($accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return bool
     */
    public function isPremium(): bool
    {
        return $this->premium;
    }

    /**
     * @param bool $premium
     */
    public function setPremium(bool $premium): void
    {
        $this->premium = $premium;
    }

    /**
     * @return string
     */
    public function getProfile(): string
    {
        return $this->profile;
    }

    /**
     * @param string $profile
     */
    public function setProfile(string $profile): void
    {
        $this->profile = $profile;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastSync(): ?\DateTime
    {
        return $this->lastSync;
    }

    /**
     * @param \DateTime|null $lastSync
     */
    public function setLastSync(?\DateTime $lastSync): void
    {
        $this->lastSync = $lastSync;
    }

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->isNew;
    }
}
