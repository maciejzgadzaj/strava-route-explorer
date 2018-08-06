<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Route
 *
 * @ORM\Entity(repositoryClass="App\Repository\RouteRepository")
 * @ORM\Table(name="route")
 *
 * @package App\Entity
 */
class Route
{
    const TYPE_RUN = 1;
    const TYPE_RIDE = 2;

    const SUBTYPE_ROAD = 1;
    const SUBTYPE_MOUNTAIN_BIKE = 2;
    const SUBTYPE_CROSS = 3;
    const SUBTYPE_TRAIL = 4;
    const SUBTYPE_MIXED = 5;

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var \App\Entity\Athlete
     *
     * @ORM\ManyToOne(targetEntity="Athlete")
     * @ORM\JoinColumn(name="athlete_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $athlete;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint")
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", name="sub_type")
     */
    private $subType;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $description;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $distance;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=10, scale=2, name="elevation_gain")
     */
    private $elevationGain;

    /**
     * @var integer
     *
     * @ORM\OneToOne(targetEntity="Map", mappedBy="route")
     */
    private $map;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", name="climb_category")
     */
    private $climbCategory;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="created_at")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="updated_at")
     */
    private $updatedAt;

    /**
     * @var bool
     */
    private $isNew = false;

    /**
     * Route constructor.
     */
    public function __construct()
    {
        // This will not be saved in the database.
        $this->isNew = true;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return \App\Entity\Athlete
     */
    public function getAthlete(): \App\Entity\Athlete
    {
        return $this->athlete;
    }

    /**
     * @param \App\Entity\Athlete $athlete
     */
    public function setAthlete(\App\Entity\Athlete $athlete): void
    {
        $this->athlete = $athlete;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getSubType(): int
    {
        return $this->subType;
    }

    /**
     * @param int $subType
     */
    public function setSubType(int $subType): void
    {
        $this->subType = $subType;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return float
     */
    public function getDistance(): float
    {
        return $this->distance;
    }

    /**
     * @param float $distance
     */
    public function setDistance(float $distance): void
    {
        $this->distance = $distance;
    }

    /**
     * @return float
     */
    public function getElevationGain(): float
    {
        return $this->elevationGain;
    }

    /**
     * @param float $elevationGain
     */
    public function setElevationGain(float $elevationGain): void
    {
        $this->elevationGain = $elevationGain;
    }

    /**
     * @return int
     */
    public function getMap(): int
    {
        return $this->map;
    }

    /**
     * @param int $map
     */
    public function setMap(int $map): void
    {
        $this->map = $map;
    }

    /**
     * @return int
     */
    public function getClimbCategory(): int
    {
        return $this->climbCategory;
    }

    /**
     * @param int $climbCategory
     */
    public function setClimbCategory(int $climbCategory): void
    {
        $this->climbCategory = $climbCategory;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->isNew ?? false;
    }
}
