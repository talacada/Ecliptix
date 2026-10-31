<?php

declare(strict_types=1);

namespace App\State\Processor\Auth;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Auth\PasswordResetInput;
use App\Exception\Auth\InvalidCredentialsException;
use App\Exception\Auth\InvalidTokenException;
use App\Repository\PasswordResetTokenRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @implements ProcessorInterface<PasswordResetInput, JsonResponse>
 */
class PasswordResetProcessor implements ProcessorInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private PasswordResetTokenRepository $passwordResetTokenRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        $token = $this->passwordResetTokenRepository->getByToken($data->getToken());

        if (null === $token) {
            throw new InvalidTokenException();
        }

        $character = $token->getCharacter();

        $character->setPasswordHash(
            $this->passwordHasher->hashPassword($character, $data->getPassword()),
        );

        $now = new DateTimeImmutable('now');
        $token->setUsedAt($now);

        $this->entityManager->flush();

        return new JsonResponse(
            ['message' => 'Password reset successfully.'],
            Response::HTTP_CREATED,
        );
    }
}
