<?php

declare(strict_types=1);

namespace App\State\Processor\Auth;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Auth\LoginInput;
use App\ApiResource\Auth\LoginOutput;
use App\Repository\Character\CharacterRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @implements ProcessorInterface<LoginInput, LoginOutput>
 */
readonly class LoginProcessor implements ProcessorInterface
{
    public function __construct(
        private CharacterRepository $characterRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager,
    ) {
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): LoginOutput {
        $character = $this->characterRepository->findOneBy(['email' => $data->getEmail()]);

        if (!$character || !$this->passwordHasher->isPasswordValid($character, $data->getPassword())) {
            throw new UnauthorizedHttpException('', 'Invalid credentials');
        }

        $token = $this->jwtManager->create($character);

        return new LoginOutput(
            token: $token,
            character: $character,
        );
    }
}
