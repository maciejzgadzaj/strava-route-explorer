<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Map
 *
 * @ORM\Entity(repositoryClass="App\Repository\MapRepository")
 * @ORM\Table(name="map")
 *
 * @package App\Entity
 */
class Map
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", length=20)
     */
    private $id;

    /**
     * @var \App\Entity\Route
     *
     * @ORM\OneToOne(targetEntity="Route")
     * @ORM\JoinColumn(name="route_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $route;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="polyline_summary")
     */
    private $polylineSummary;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=8, scale=5, name="start_latitude")
     */
    private $startLatitude;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=8, scale=5, name="start_longitude")
     */
    private $startLongitude;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=8, scale=5, name="end_latitude")
     */
    private $endLatitude;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=8, scale=5, name="end_longitude")
     */
    private $endLongitude;

    /**
     * @var bool
     */
    private $isNew = false;

    /**
     * Map constructor.
     */
    public function __construct()
    {
        // This will not be saved in the database.
        $this->isNew = true;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return \App\Entity\Route
     */
    public function getRoute(): \App\Entity\Route
    {
        return $this->route;
    }

    /**
     * @param \App\Entity\Route $route
     */
    public function setRoute(\App\Entity\Route $route): void
    {
        $this->route = $route;
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
     * @return float
     */
    public function getStartLatitude(): float
    {
        return $this->startLatitude;
    }

    /**
     * @param float $startLatitude
     */
    public function setStartLatitude(float $startLatitude): void
    {
        $this->startLatitude = $startLatitude;
    }

    /**
     * @return float
     */
    public function getStartLongitude(): float
    {
        return $this->startLongitude;
    }

    /**
     * @param float $startLongitude
     */
    public function setStartLongitude(float $startLongitude): void
    {
        $this->startLongitude = $startLongitude;
    }

    /**
     * @return float
     */
    public function getEndLatitude(): float
    {
        return $this->endLatitude;
    }

    /**
     * @param float $endLatitude
     */
    public function setEndLatitude(float $endLatitude): void
    {
        $this->endLatitude = $endLatitude;
    }

    /**
     * @return float
     */
    public function getEndLongitude(): float
    {
        return $this->endLongitude;
    }

    /**
     * @param float $endLongitude
     */
    public function setEndLongitude(float $endLongitude): void
    {
        $this->endLongitude = $endLongitude;
    }

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->isNew;
    }
}
