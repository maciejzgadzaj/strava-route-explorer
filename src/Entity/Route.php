<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RouteRepository;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RouteRepository::class)]
#[ORM\Table(name: 'routes')]
#[ORM\Index(columns: ['name'], name: 'idx_name')]
#[ORM\Index(columns: ['name'], name: 'idx_fulltext_name', flags: ['fulltext'])]
#[ORM\Index(columns: ['description'], name: 'idx_fulltext_desc', flags: ['fulltext'])]
#[ORM\Index(columns: ['name', 'description'], name: 'idx_fulltext_name_desc', flags: ['fulltext'])]
#[ORM\Index(columns: ['type'], name: 'idx_type')]
#[ORM\Index(columns: ['sub_type'], name: 'idx_sub_type')]
#[ORM\Index(columns: ['public'], name: 'idx_public')]
#[ORM\Index(columns: ['distance'], name: 'idx_distance')]
#[ORM\Index(columns: ['elevation_gain'], name: 'idx_elevation_gain')]
#[ORM\Index(columns: ['segments'], name: 'idx_fulltext_segments', flags: ['fulltext'])]
#[ORM\Index(columns: ['tags'], name: 'idx_fulltext_tags', flags: ['fulltext'])]
class Route
{
    const TYPE_RUN = 1;
    const TYPE_RIDE = 2;

    const SUBTYPE_ROAD = 1;
    const SUBTYPE_MOUNTAIN_BIKE = 2;
    const SUBTYPE_CROSS = 3;
    const SUBTYPE_TRAIL = 4;
    const SUBTYPE_MIXED = 5;

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::BIGINT)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Athlete::class, inversedBy: 'routes')]
    #[ORM\JoinColumn(name: 'athlete_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Athlete $athlete;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 255)]
    private string $name;

    #[ORM\Column(name: 'type', type: Types::SMALLINT)]
    private int $type;

    #[ORM\Column(name: 'sub_type', type: Types::SMALLINT)]
    private int $subType;

    // Strava value.
    #[ORM\Column(name: 'private', type: Types::BOOLEAN)]
    private ?bool $private;

    // Local "published" value.
    #[ORM\Column(name: 'public', type: Types::BOOLEAN, options: ['default' => true])]
    private bool $public;

    #[ORM\Column(name: 'description', type: Types::TEXT)]
    private string $description;

    #[ORM\Column(name: 'distance', type: Types::DECIMAL, precision: 10, scale: 2)]
    private float $distance;

    #[ORM\Column(name: 'elevation_gain', type: Types::DECIMAL, precision: 10, scale: 2)]
    private float $elevationGain;

    #[ORM\Column(name: 'climb_category', type: Types::SMALLINT)]
    private int $climbCategory;

    #[ORM\Column(name: 'start', type: 'point')]
    private Point $start;

    #[ORM\Column(name: 'end', type: 'point')]
    private Point $end;

    #[ORM\Column(name: 'map_url', type: Types::TEXT, nullable: true)]
    private ?string $mapUrl;

    #[ORM\Column(name: 'polyline_summary', type: Types::TEXT)]
    private string $polylineSummary;

    #[ORM\Column(name: 'segments', type: Types::JSON, nullable: true)]
    private ?array $segments;

    #[ORM\Column(name: 'tags', type: Types::JSON, nullable: true)]
    private ?array $tags;

    /**
     * @var Collection<int, Athlete>
     */
    #[ORM\ManyToMany(targetEntity: Athlete::class, inversedBy: 'starredRoutes')]
    #[ORM\JoinTable(name: 'route_starred_by')]
    #[ORM\JoinColumn(name: 'route_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'athlete_id', referencedColumnName: 'id')]
    private Collection $starredBy;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private \DateTime $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    private \DateTime $updatedAt;

    private bool $isNew = false;

    public function __construct()
    {
        $this->starredBy = new ArrayCollection();
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

    public function getAthlete(): Athlete
    {
        return $this->athlete;
    }

    public function setAthlete(Athlete $athlete): self
    {
        $this->athlete = $athlete;

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

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSubType(): int
    {
        return $this->subType;
    }

    public function setSubType(int $subType): self
    {
        $this->subType = $subType;

        return $this;
    }

    public function isPrivate(): ?bool
    {
        return $this->private;
    }

    public function setPrivate(?bool $private): self
    {
        $this->private = $private;

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): self
    {
        $this->public = $public;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDistance(): float
    {
        return $this->distance;
    }

    public function setDistance(float $distance): self
    {
        $this->distance = $distance;

        return $this;
    }

    public function getElevationGain(): float
    {
        return $this->elevationGain;
    }

    public function setElevationGain(float $elevationGain): self
    {
        $this->elevationGain = $elevationGain;

        return $this;
    }

    public function getClimbCategory(): int
    {
        return $this->climbCategory;
    }

    public function setClimbCategory(int $climbCategory): self
    {
        $this->climbCategory = $climbCategory;

        return $this;
    }

    public function getStart(): Point
    {
        return $this->start;
    }

    public function setStart(Point $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): Point
    {
        return $this->end;
    }

    public function setEnd(Point $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getMapUrl(): ?string
    {
        return $this->mapUrl;
    }

    public function setMapUrl(?string $mapUrl): self
    {
        $this->mapUrl = $mapUrl;

        return $this;
    }

    public function getPolylineSummary(): string
    {
        return $this->polylineSummary;
    }

    public function setPolylineSummary(string $polylineSummary): self
    {
        $this->polylineSummary = $polylineSummary;

        return $this;
    }

    public function getSegments(): ?array
    {
        return $this->segments;
    }

    public function setSegments(?array $segments): self
    {
        $this->segments = $segments;

        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function hasTag(Tag $tag): bool
    {
        return $this->tags->contains($tag);
    }

    public function addTag(Tag $tag): self
    {
        if ($this->hasTag($tag)) {
            return $this;
        }

        $this->tags->add($tag);

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if (!$this->hasTag($tag)) {
            return $this;
        }

        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @return Collection<int, Athlete>
     */
    public function getStarredBy(): Collection
    {
        return $this->starredBy;
    }

    public function setStarredBy(Collection $starredBy): self
    {
        $this->starredBy = $starredBy;

        return $this;
    }

    public function isStarredBy(Athlete $athlete): bool
    {
        return $this->starredBy->contains($athlete);
    }

    public function addStarredBy(Athlete $athlete): self
    {
        if ($this->isStarredBy($athlete)) {
            return $this;
        }

        $this->starredBy->add($athlete);

        return $this;
    }

    public function removeStarredBy(Athlete $athlete): self
    {
        if (!$this->isStarredBy($athlete)) {
            return $this;
        }

        $this->starredBy->removeElement($athlete);

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isNew(): bool
    {
        return $this->isNew ?? false;
    }
}
