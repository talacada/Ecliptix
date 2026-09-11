<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Character\Character;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class AbstractApiTestCase extends ApiTestCase
{
    use ResetDatabase;

    protected static ?bool $alwaysBootKernel = false;

    protected function createAuthenticatedClient(Character $character): Client
    {
        /** @var JWTTokenManagerInterface $jwtManager */
        $jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($character);

        return static::createClient([], ['auth_bearer' => $token]);
    }
}
