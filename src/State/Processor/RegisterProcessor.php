<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Auth\RegisterInput;
use App\Entity\Character;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class RegisterProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface      $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): Character {

        assert($data, RegisterInput::class);

        //TODO zkontrolovat jestli username a email uz v db nejsou pomoci CharacterRepository::findByOne

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
