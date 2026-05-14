<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Auth\RegisterInput;
use App\Entity\Character;
use App\Repository\CharacterRepository;
use Doctrine\ORM\EntityManagerInterface;
use mysql_xdevapi\Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class RegisterProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface      $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private CharacterRepository $characterRepository,
    ) {
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): Character {

        assert($data, RegisterInput::class);

        if ($this->characterRepository->findOneBy(['email' => $data->getEmail()]) > 0) {
            throw new Exception("Email already registered");
        }
        if ($this->characterRepository->findOneBy(['username' => $data->getEmail()]) > 0) {
            throw new Exception("username already registered");
        }

        $character = new Character();

        $character->setEmail($data->getEmail());
        $character->setUsername($data->getUsername());
        $character->setPasswordHash(
            $this->passwordHasher->hashPassword($character, $data->getPassword())
        );

        $entityManager = $this->entityManager;
        $entityManager->persist($character);
        $entityManager->flush();
        return $character;
    }
}
