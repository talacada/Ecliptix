<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity\Character;

use App\Entity\Character\Character;
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
        // 1. Arrange: Create a character and set initial gold balance (e.g. 100) via setGold()

        // 2. Act: Add gold via addGold(50)

        // 3. Assert: Verify that getGold() returns 150
    }

    public function testSubtractGoldDecreasesAmount(): void
    {
        // 1. Arrange: Create a character and set initial gold balance to 100 via setGold()

        // 2. Act: Subtract 30 gold via subtractGold(30)

        // 3. Assert: Verify that getGold() returns 70
    }

    public function testSubtractGoldThrowsExceptionWhenInsufficientGold(): void
    {
        // 1. Arrange: Create a character with 50 gold

        // 2. Expect Exception:
        // $this->expectException(\InvalidArgumentException::class);
        // $this->expectExceptionMessage('Not enough gold');

        // 3. Act: Attempt to subtract 100 gold via subtractGold(100)
    }

    public function testSubtractDiamondsDecreasesAmount(): void
    {
        // 1. Arrange: Create a character and set initial diamonds balance to 50 via setDiamonds()

        // 2. Act: Subtract 20 diamonds via subtractDiamonds(20)

        // 3. Assert: Verify that getDiamonds() returns 30
    }

    public function testSubtractDiamondsThrowsExceptionWhenInsufficientDiamonds(): void
    {
        // 1. Arrange: Create a character with 10 diamonds

        // 2. Expect Exception:
        // $this->expectException(\InvalidArgumentException::class);
        // $this->expectExceptionMessage('Not enough diamonds');

        // 3. Act: Attempt to subtract 20 diamonds via subtractDiamonds(20)
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        // 1. Arrange: Create a character and set email via setEmail('hero@ecliptix.local')

        // 2. Act: Call getUserIdentifier()

        // 3. Assert: Verify that the returned value is 'hero@ecliptix.local'
    }
}
