<?php

declare(strict_types=1);

namespace App\Listener;

use App\Entity\Athlete;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;

#[AsEventListener(event: LoginSuccessEvent::class, method: 'onLoginSuccess')]
#[AsEventListener(event: LogoutEvent::class, method: 'onLogout')]
#[AsEventListener(event: SwitchUserEvent::class, method: 'onSwitchUser')]
final class UserListener
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        /** @var Athlete $user */
        $user = $event->getUser();

        $this->logger->info('User logged in', [
            'user_name' => $user->getUserIdentifier(),
        ]);
    }

    public function onLogout(LogoutEvent $event): void
    {
        $this->logger->info('User logged out');
    }

    public function onSwitchUser(SwitchUserEvent $event): void
    {
        /** @var Athlete $targetUser */
        $targetUser = $event->getTargetUser();

        $this->logger->info('Switched user', [
            'target_user_id' => $targetUser->getId(),
            'target_user_name' => $targetUser->getUserIdentifier(),
        ]);
    }
}