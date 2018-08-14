<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class ControllerBase
 *
 * @package App\Controller
 */
class ControllerBase extends Controller
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * RoutesController constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
