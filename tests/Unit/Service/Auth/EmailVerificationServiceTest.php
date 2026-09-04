<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Auth;

use App\Entity\Character\Character;
use App\Service\Auth\EmailVerificationService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class EmailVerificationServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
    }
    public function testCreateToken(): void
    {
        $character =  new Character();

        $tokenService = new EmailVerificationService($this->entityManager);

        $this->entityManager
            ->expects($this->once())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $token = $tokenService->createToken($character);

        $this->assertTrue(Uuid::isValid((string) $token->getToken()));
        $this->assertSame($token->getCharacter(), $character);
        $this->assertEqualsWithDelta(new DateTimeImmutable('+24 hours'), $token->getExpiresAt(), 2);
        $this->assertNull($token->getUsedAt());
    }
}
