<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

class FlashService
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function add(string $type, mixed $message): void
    {
        try {
            $session = $this->requestStack->getSession();
            $session->getFlashBag()->add('notice', $message);
        } catch (SessionNotFoundException) {
            // Do nothing.
        }
    }
}
