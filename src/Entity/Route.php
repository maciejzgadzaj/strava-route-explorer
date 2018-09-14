<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Route
 *
 * @ORM\Entity(repositoryClass="App\Repository\RouteRepository")
 * @ORM\Table(
 *     name="route",
 *     indexes={
 *         @ORM\Index(name="IDX_TYPE", columns={"type"}),
 *         @ORM\Index(name="IDX_SUB_TYPE", columns={"sub_type"}),
 *         @ORM\Index(name="IDX_NAME", columns={"name"}),
 *         @ORM\Index(name="IDX_DISTANCE", columns={"distance"}),
 *         @ORM\Index(name="IDX_ASCENT", columns={"ascent"}),
 *         @ORM\Index(name="IDX_PUBLIC", columns={"public"}),
 *         @ORM\Index(name="IDX_FULLTEXT_NAME", columns={"name"}, flags={"fulltext"}),
 *         @ORM\Index(name="IDX_FULLTEXT_DESC", columns={"description"}, flags={"fulltext"}),
 *         @ORM\Index(name="IDX_FULLTEXT_SEGMENTS", columns={"segments"}, flags={"fulltext"}),
 *         @ORM\Index(name="IDX_FULLTEXT_TAGS", columns={"tags"}, flags={"fulltext"}),
 *         @ORM\Index(name="IDX_FULLTEXT_NAME_DESC", columns={"name", "description"}, flags={"fulltext"})
 *     }
 * )
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
     * @ORM\Column(type="text")
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
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $ascent;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", name="climb_category")
     */
    private $climbCategory;

    /**
     * @var \CrEOF\Spatial\PHP\Types\Geometry\Point
     *
     * @ORM\Column(type="point")
     */
    private $start;

    /**
     * @var \CrEOF\Spatial\PHP\Types\Geometry\Point
     *
     * @ORM\Column(type="point")
     */
    private $end;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="polyline_summary")
     */
    private $polylineSummary;

    /**
     * @var array
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $segments;

    /**
     * @var array
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $tags;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $public;

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
     * @var \Doctrine\Common\Collections\Collection|\App\Entity\Athlete[]
     *
     * @ORM\ManyToMany(targetEntity="Athlete", inversedBy="starredRoutes")
     * @ORM\JoinTable(
     *     name="route_starred_by",
     *     joinColumns={@ORM\JoinColumn(name="route_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="athlete_id", referencedColumnName="id")}
     * )
     */
    private $starredBy;

    /**
     * @var bool
     */
    private $isNew = false;

    /**
     * Route constructor.
     */
    public function __construct()
    {
        $this->starredBy = new ArrayCollection();

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
    public function getAscent(): float
    {
        return $this->ascent;
    }

    /**
     * @param float $ascent
     */
    public function setAscent(float $ascent): void
    {
        $this->ascent = $ascent;
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
     * @return \CrEOF\Spatial\PHP\Types\Geometry\Point
     */
    public function getStart(): \CrEOF\Spatial\PHP\Types\Geometry\Point
    {
        return $this->start;
    }

    /**
     * @param \CrEOF\Spatial\PHP\Types\Geometry\Point $start
     */
    public function setStart(\CrEOF\Spatial\PHP\Types\Geometry\Point $start): void
    {
        $this->start = $start;
    }

    /**
     * @return \CrEOF\Spatial\PHP\Types\Geometry\Point
     */
    public function getEnd(): \CrEOF\Spatial\PHP\Types\Geometry\Point
    {
        return $this->end;
    }

    /**
     * @param \CrEOF\Spatial\PHP\Types\Geometry\Point $end
     */
    public function setEnd(\CrEOF\Spatial\PHP\Types\Geometry\Point $end): void
    {
        $this->end = $end;
    }

    /**
     * @return string
     */
    public function getPolylineSummary(): string
    {
        return $this->polylineSummary;
    }

    /**
     * @param string $polylineSummary
     */
    public function setPolylineSummary(string $polylineSummary): void
    {
        $this->polylineSummary = $polylineSummary;
    }

    /**
     * @return array
     */
    public function getSegments(): array
    {
        return $this->segments;
    }

    /**
     * @param array $segments
     */
    public function setSegments(array $segments): void
    {
        $this->segments = $segments;
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
     */
    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    /**
     * @return bool
     */
    public function isPublic(): bool
    {
        return $this->public;
    }

    /**
     * @param bool $public
     */
    public function setPublic(bool $public): void
    {
        $this->public = $public;
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
     * @return \App\Entity\Athlete[]|\Doctrine\Common\Collections\Collection
     */
    public function getStarredBy()
    {
        return $this->starredBy;
    }

    /**
     * @param \App\Entity\Athlete[]|\Doctrine\Common\Collections\Collection $starredBy
     */
    public function setStarredBy($starredBy): void
    {
        $this->starredBy = $starredBy;
    }

    /**
     * @param \App\Entity\Athlete $athlete
     */
    public function addStarredBy(Athlete $athlete): void
    {
        if ($this->starredBy->contains($athlete)) {
            return;
        }

        $this->starredBy->add($athlete);
    }

    /**
     * @param \App\Entity\Athlete $athlete
     */
    public function removeStarredBy(Athlete $athlete): void
    {
        if (!$this->starredBy->contains($athlete)) {
            return;
        }

        $this->starredBy->removeElement($athlete);
    }

    /**
     * @param \App\Entity\Athlete $athlete
     */
    public function isStarredBy(Athlete $athlete): bool
    {
        return $this->starredBy->contains($athlete);
    }

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->isNew ?? false;
    }
}
