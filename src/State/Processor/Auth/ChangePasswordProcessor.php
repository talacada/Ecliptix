<?php

declare(strict_types=1);

namespace App\State\Processor\Auth;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Auth\ChangePasswordInput;
use App\Entity\Character\Character;
use App\Security\LoggedInCharacter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @implements ProcessorInterface<ChangePasswordInput, Character>
 */
class ChangePasswordProcessor implements ProcessorInterface
{
    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(mixed $data, Operation $operation, $uriVariables = [], $context = []): Character
    {
        $character = $this->loggedInCharacter->getCharacter();

        if (!$this->passwordHasher->isPasswordValid($character, $data->getOldPassword())) {
            throw new BadRequestHttpException('Old password is incorrect');
        }

        $character->setPasswordHash(
            $this->passwordHasher->hashPassword($character, $data->getNewPassword()),
        );

        $entityManager = $this->entityManager;
        $entityManager->persist($character);
        $entityManager->flush();

        return $character;
    }
}
