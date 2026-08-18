<?php

declare(strict_types=1);

namespace App\State\Processor\Auth;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\EmailVerificationToken;
use App\Repository\EmailVerificationTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @implements ProcessorInterface<EmailVerificationToken, JsonResponse>
 */
class VerifyEmailProcessor implements ProcessorInterface
{
    public function __construct(
        private EmailVerificationTokenRepository $emailVerificationTokenRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        $dbToken = $this->emailVerificationTokenRepository->getToken($data);

        if (null === $dbToken) {
            throw new UnprocessableEntityHttpException('Invalid or expired token');
        }

        $dbToken->setUsedAt(new \DateTimeImmutable('now'));
        $dbToken->getCharacter()->setEmailVerified(true);

        $this->entityManager->flush();

        return new JsonResponse(
            ['message' => 'Email verified.'],
            Response::HTTP_OK,
        );
    }
}
