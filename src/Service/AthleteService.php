<?php

namespace App\Service;

use App\Entity\Athlete;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
    public function __construct(EntityManagerInterface $entityManager, SessionInterface $session)
    {
        parent::__construct($entityManager, $session);

        $this->repository = $this->entityManager->getRepository(Athlete::class);
    }

    /**
     * Return total number of athletes.
     *
     * @return int
     */
    public function count()
    {
        return $this->repository->count([]);
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

    /**
     * Check if current site user is authorized with Strava.
     *
     * @return bool
     */
    public function isAuthorized()
    {
        return !empty($this->session->get('strava_athlete'));
    }

    /**
     * Return athlete currently authorized with Strava.
     *
     * @return \App\Entity\Athlete
     */
    public function getCurrentAthlete()
    {
        if ($athleteId = $this->session->get('strava_athlete')) {
            return $this->load($athleteId);
        }
    }

    /**
     * Return an array of athletes authorized with Strava.
     *
     * @return Athlete[]
     */
    public function getAuthorizedWithStrava()
    {
        $qb = $this->repository->createQueryBuilder('a');
        return $qb->where($qb->expr()->isNotNull('a.accessToken'))
            ->getQuery()
            ->getResult();
    }

    /**
     * Remove old Strava cookies and set one new one.
     */
    public function removeOldCookies()
    {
        // Set new cookie from old.
        if ($athleteCookie = $this->session->get('athlete')) {
            $athlete = $this->load($athleteCookie->id);
            $this->session->set('strava_athlete', $athlete->getId());

            // Remove old cookies.
            $this->session->remove('athlete');
            $this->session->remove('strava_access_token');
        }
    }
}
