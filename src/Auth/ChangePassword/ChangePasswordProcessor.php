<?php

declare(strict_types=1);

namespace App\Auth\ChangePassword;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Character\Character\Character;
use App\Character\Character\LoggedInCharacter;
use Doctrine\ORM\EntityManagerInterface;
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
            throw new InvalidPasswordException();
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
