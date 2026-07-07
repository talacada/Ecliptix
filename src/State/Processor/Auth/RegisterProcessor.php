<?php

declare(strict_types=1);

namespace App\State\Processor\Auth;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Auth\RegisterInput;
use App\Entity\Character\Character;
use App\Repository\Character\CharacterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class RegisterProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private CharacterRepository $characterRepository,
    ) {
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): Character {
        assert($data instanceof RegisterInput);

        if (null !== $this->characterRepository->findOneBy(['email' => $data->getEmail()])) {
            throw new UnprocessableEntityHttpException('Email already registered');
        }

        if (null !== $this->characterRepository->findOneBy(['username' => $data->getUsername()])) {
            throw new UnprocessableEntityHttpException('Username already registered');
        }

        $character = new Character();

        $character->setEmail($data->getEmail());
        $character->setUsername($data->getUsername());
        $character->setPasswordHash(
            $this->passwordHasher->hashPassword($character, $data->getPassword()),
        );

        $entityManager = $this->entityManager;
        $entityManager->persist($character);
        $entityManager->flush();

        return $character;
    }
}
