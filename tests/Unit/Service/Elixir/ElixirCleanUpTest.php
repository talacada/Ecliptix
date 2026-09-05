<?php

namespace App\Tests\Unit\Service\Elixir;


use App\Entity\Character\ActiveElixir;
use App\Entity\Character\Character;
use App\Service\Elixir\ElixirCleanUp;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ElixirCleanUpTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
    }
    // TODO: testRemoveExpiredRemovesExpiredElixirsAndKeepsActiveOnes() - mock EntityManager, assert remove() is called only for expired elixirs (expiresAt < now)
    public function testRemoveExpiredRemovesExpiredElixirsAndKeepsActiveOnes(): void
    {
        $character = new Character();

        $oldElixir = new ActiveElixir();
        $oldElixir->setExpiresAt(new DateTimeImmutable("yesterday"));

        $activeElixirInMinutes = new ActiveElixir();
        $activeElixirInMinutes->setExpiresAt(new DateTimeImmutable("now + 20 minutes"));

        $activeElixirExpiresTomorrow = new ActiveElixir();
        $activeElixirExpiresTomorrow->setExpiresAt(new DateTimeImmutable("tomorrow"));

        $character->addActiveElixir($oldElixir);
        $character->addActiveElixir($activeElixirInMinutes);
        $character->addActiveElixir($activeElixirExpiresTomorrow);

        $this->assertCount(3, $character->getActiveElixirs());

        $this->entityManager
            ->expects($this->exactly(1))
            ->method('flush');


        $cleanUp = new ElixirCleanUp($this->entityManager);
        $cleanUp->removeExpired($character);

        $this->assertCount(2, $character->getActiveElixirs());
    }
}
