<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Athlete;
use App\Repository\AthleteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class AthleteService
{
    private AthleteRepository|EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->repository = $this->entityManager->getRepository(Athlete::class);
    }

    public function count(): int
    {
        return $this->repository->count([]);
    }

    public function exists(int $athleteId): bool
    {
        return !empty($this->repository->findOneBy(['id' => $athleteId]));
    }

    public function load(int $athleteId): Athlete
    {
        return $this->repository->findOneBy(['id' => $athleteId]);
    }

    public function save(array $athleteData, array $tokenData = null): Athlete
    {
        if (!$athlete = $this->repository->find($athleteData['id'])) {
            $athlete = new Athlete();
            $athlete->setId($athleteData['id']);
        }

        $athlete
            ->setUsername($athleteData['username'])
            ->setName(trim($athleteData['firstname']).' '.trim($athleteData['lastname']))
            ->setEmail($athleteData['email'] ?? null)
            ->setCountry($athleteData['country'])
            ->setPremium($athleteData['premium'])
            ->setProfile($athleteData['profile'])
        ;

        if (!empty($tokenData)) {
            $athlete = $this->saveTokenData($athlete, $tokenData, true);
        }

        $this->entityManager->persist($athlete);
        $this->entityManager->flush();

        return $athlete;
    }

    public function saveTokenData(Athlete $athlete, array $tokenData, bool $skipSave = false): Athlete
    {
        $athlete->setAccessToken($tokenData['access_token']);
        $athlete->setRefreshToken($tokenData['refresh_token']);

        $expiresAt = new \DateTime();
        $expiresAt->setTimestamp($tokenData['expires_at']);
        $athlete->setExpiresAt($expiresAt);

        if (empty($skipSave)) {
            $this->entityManager->persist($athlete);
            $this->entityManager->flush();
        }

        return $athlete;
    }

    public function delete(int $atleteId): void
    {
        $athlete = $this->repository->findOneBy(['id' => $atleteId]);

        $this->entityManager->remove($athlete);
        $this->entityManager->flush();
    }
}
