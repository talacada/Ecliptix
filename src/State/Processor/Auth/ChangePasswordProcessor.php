<?php

namespace App\State\Processor\Auth;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Auth\ChangePasswordInput;
use App\Entity\Character\Character;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class ChangePasswordProcessor implements ProcessorInterface
{
    public function __construct(
        private Security $security,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
    ) {}

    public function process(mixed $data, $operation, $uriVariables = [], $context = []): Character
        {
            assert($data instanceof ChangePasswordInput);

            $character = $this->security->getUser();

            if ($character instanceof Character) {
                throw new UnauthorizedHttpException('Not authenticated');
            }

            $hashedOld = $this->passwordHasher->hashPassword($character, $data->getOldPassword());

            if ($hashedOld != $character->getPassword()) {
                //TODO ERROR HERE RETURN EXCEPTION
            }

            $character->setPasswordHash(
                $this->passwordHasher->hashPassword($character, $data->getNewPassword())
            );

            $entityManager = $this->entityManager;
            $entityManager->persist($character);
            $entityManager->flush();

            return $character;
        }
}
