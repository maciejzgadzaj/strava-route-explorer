<?php

namespace App\Service;

use App\Entity\Athlete;

/**
 * Class AthleteService
 *
 * @package App\Service
 */
class AthleteService extends EntityService
{
    /**
     * @var \App\Repository\AthleteRepository
     */
    private $repository;

    /**
     * AthleteService constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    public function __construct(
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        \Symfony\Component\HttpFoundation\Session\SessionInterface $session
    ) {
        parent::__construct($entityManager, $session);

        $this->repository = $this->entityManager->getRepository(Athlete::class);
    }

    /**
     * Check if athlete exists.
     *
     * @param int $athleteId
     *
     * @return bool
     */
    public function exists($athleteId)
    {
        return !empty($this->repository->findOneBy(['id' => $athleteId]));
    }

    /**
     * Load an athlete.
     *
     * @param int $athleteId
     *
     * @return \App\Entity\Athlete
     */
    public function load($athleteId)
    {
        return $this->repository->findOneBy(['id' => $athleteId]);
    }

    /**
     * Save new or update existing athlete.
     *
     * @param object $athleteData
     *
     * @return \App\Entity\Athlete
     */
    public function save($athleteData, $accessToken = null)
    {
        if (!$athlete = $this->repository->find($athleteData->id)) {
            $athlete = new Athlete();
            $athlete->setId($athleteData->id);
        }

        $athlete->setUsername($athleteData->username);
        $athlete->setEmail($athleteData->email ?? null);
        $athlete->setName(trim($athleteData->firstname).' '.trim($athleteData->lastname));
        $athlete->setPremium($athleteData->premium);
        $athlete->setProfile($athleteData->profile);

        if (!empty($accessToken)) {
            $athlete->setAccessToken($accessToken);
        }

        $this->entityManager->persist($athlete);
        $this->entityManager->flush();

        return $athlete;
    }

    /**
     * Delete an athlete.
     *
     * @param int $atleteId
     */
    public function delete($atleteId)
    {
        $athlete = $this->repository->findOneBy(['id' => $atleteId]);

        $this->entityManager->remove($athlete);
        $this->entityManager->flush();
    }
}
