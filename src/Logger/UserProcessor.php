<?php

declare(strict_types=1);

namespace App\Logger;

use App\Entity\Athlete;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;

// https://symfony.com/doc/current/logging/processors.html
final class UserProcessor implements ProcessorInterface
{
    public function __construct(private readonly Security $security)
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        /** @var ?Athlete $currentUser */
        $currentUser = $this->security->getUser();

        // Check if current user is impersonated.
        // https://symfony.com/doc/current/security/impersonating_user.html#finding-the-original-user
        $token = $this->security->getToken();

        if ($token instanceof SwitchUserToken) {
            /** @var ?Athlete $originalUser */
            $originalUser = $token->getOriginalToken()->getUser();

            $record->extra['original_user_id'] = $originalUser->getId();
            $record->extra['impersonated_user_id'] = $currentUser->getId();
        } else {
            $record->extra['user_id'] = $currentUser?->getId();
        }

        return $record;
    }
}
