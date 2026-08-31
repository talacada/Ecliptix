<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity\Character;

use App\Config\CharacterConfig;
use App\Entity\Character\Character;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;

class CharacterTest extends TestCase
{
    public function testInitialCharacterHasDefaultValues(): void
    {
        $character = new Character();

        $this->assertSame(1, $character->getGold());
        $this->assertSame(1, $character->getDiamonds());
        $this->assertSame(1, $character->getLevel());
        $this->assertSame(0, $character->getExperience());
        $this->assertSame(1, $character->getDamage());
        $this->assertSame(100, $character->getHealth());
        $this->assertSame(4, $character->getBackpackCapacity());
        $this->assertSame(1, $character->getPrestigePoints());
        $this->assertFalse($character->isEmailVerified());
    }

    public function testAddGoldIncreasesAmount(): void
    {
        $character = new Character();
        $character->addGold(50);
        $this->assertSame( CharacterConfig::INITIAL_GOLD + 50, $character->getGold());
    }

    public function testSetGold(): void
    {
        $character = new Character();
        $character->setGold(777);
        $this->assertSame( 777, $character->getGold());
    }

    public function testSubtractGoldValidAmount(): void
    {
        $character = new Character();
        $character->setGold(111);
        $character->subtractGold(12);
        $this->assertSame( 99, $character->getGold());
    }

    public function testSubtractGoldThrowsExceptionWhenInsufficientGold(): void
    {
        $character = new Character();
        $character->setGold(111);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Not enough gold');

        $character->subtractGold(112);
    }

    public function testSetDiamonds(): void
    {
        $character = new Character();
        $character->setDiamonds(222);

        $this->assertSame( 222, $character->getDiamonds());
    }

    public function testAddDiamonds(): void
    {
        $character = new Character();
        $character->setDiamonds(10);
        $character->addDiamonds(222);

        $this->assertSame( 232, $character->getDiamonds());
    }

    public function testSubtractDiamondsThrowsExceptionWhenInsufficientDiamonds(): void
    {
        $character = new Character();
        $character->setDiamonds(10);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Not enough diamonds');

        $character->subtractDiamonds(11);
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $character = new Character();

        $character->setEmail('test.test@test.com');

        $this->assertSame('test.test@test.com', $character->getUserIdentifier());
        $this->assertSame('test.test@test.com', $character->getEmail());
    }

    public function testSubtractDiamondsValidAmount(): void
    {
        $character = new Character();

        $character->setDiamonds(222);
        $character->subtractDiamonds(10);

        $this->assertSame( 212, $character->getDiamonds());
    }

    public function testGetUserIdentifierThrowsExceptionWhenEmailIsEmpty(): void
    {
        $character = new Character();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('User email cant be empty.');

        $character->getUserIdentifier();
    }

    // TODO: testGetShopRotationsFiltersExpiredAndFutureRotations() - assert only rotations where validFrom < now < validUntil are returned
    public function testGetShopRotationsFiltersExpiredAndFutureRotations(): void
    {

    }

    // TODO: testRemoveActiveElixirUnsetsCharacterReference() - assert character is set to null on the ActiveElixir instance when removed
    public function testRemoveActiveElixirUnsetsCharacterReference(): void
    {

    }
}
