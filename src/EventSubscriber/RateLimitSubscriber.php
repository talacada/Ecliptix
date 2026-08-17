<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class RateLimitSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire(service: 'limiter.login_limiter')]
        private RateLimiterFactory $loginLimiter,

        #[Autowire(service: 'limiter.register_limiter')]
        private RateLimiterFactory $registerLimiter,

        #[Autowire(service: 'limiter.password_reset_limiter')]
        private RateLimiterFactory $passwordResetLimiter,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        $limiter = match ($route) {
            '_api_/auth/login_post' => $this->loginLimiter,
            '_api_/auth/register_post' => $this->registerLimiter,
            '_api_/auth/request-password-reset_post', '_api_/auth/password-reset_post' => $this->passwordResetLimiter,
            default => null,
        };

        if (null === $limiter) {
            return;
        }

        $ip = $request->getClientIp() ?? 'unknown';

        $limit = $limiter->create($ip)->consume(1);

        if (!$limit->isAccepted()) {
            throw new TooManyRequestsHttpException(retryAfter: $limit->getRetryAfter()->getTimestamp() - time(), message: 'Too many attempts. Try again later.');
        }
    }
}
