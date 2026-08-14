<?php

namespace App\State\Processor\Auth;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\PasswordResetTokenRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordResetProcessor implements ProcessorInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private PasswordResetTokenRepository $passwordResetTokenRepository,
        private EntityManagerInterface $entityManager,
    ) {}


    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        $token = $this->passwordResetTokenRepository->getByToken($data->getToken());

        if ($token === null) {
            throw new NotFoundHttpException('Token does not exist or is invalid.');
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
            Response::HTTP_CREATED
        );
    }
}
