<?php

namespace App\State\Provider\Auth;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use App\Repository\EmailVerificationTokenRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Exception\DatabaseDoesNotExist;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class VerifyEmailProvider implements ProviderInterface
{
    public function __construct(
        private EmailVerificationTokenRepository $emailVerificationTokenRepository,
        private EntityManagerInterface $entityManager,
    ) { }
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $tokenFromUrl = $uriVariables['token'] ?? null;

        if ($tokenFromUrl === null) {
            throw new UnprocessableEntityHttpException('Token is required');
        }

        $dbToken = $this->emailVerificationTokenRepository->getToken($tokenFromUrl);

        if ($dbToken === null) {
            throw new UnprocessableEntityHttpException('Invalid or expired token');
        }

        if ($dbToken->getExpiresAt() < new DateTimeImmutable('now')) {
            throw new UnprocessableEntityHttpException('Invalid or expired token');
        }

        $dbToken->setUsedAt(new DateTimeImmutable('now'));
        $dbToken->getCharacter()->setEmailVerified(true);

        $this->entityManager->flush();

        return new Response(status: Response::HTTP_OK,);
    }
}
