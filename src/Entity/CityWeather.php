<?php

namespace App\Entity;

use App\Repository\CityWeatherRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CityWeatherRepository::class)]
#[UniqueEntity('cityname','This city already exist')]
class CityWeather
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private ?string $cityname = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?float $alerttemperature = null;

    #[ORM\ManyToOne(inversedBy: 'cityWeather')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCityname(): ?string
    {
        return $this->cityname;
    }

    public function setCityname(?string $cityname): static
    {
        $this->cityname = $cityname;

        return $this;
    }

    public function getAlerttemperature(): ?float
    {
        return $this->alerttemperature;
    }

    public function setAlerttemperature(?float $alerttemperature): static
    {
        $this->alerttemperature = $alerttemperature;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
