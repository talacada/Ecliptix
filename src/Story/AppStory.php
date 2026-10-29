<?php

declare(strict_types=1);

namespace App\Story;

use DateMalformedStringException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Random\RandomException;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'main')]
final class AppStory extends Story
{
    public function __construct(
        private ItemDefinitionStory $itemDefinitionStory,
        private CharacterStory $characterStory,
    ) {
    }

    /**
     * @throws OptimisticLockException
     * @throws DateMalformedStringException
     * @throws RandomException
     * @throws ORMException
     */
    public function build(): void
    {
        $this->itemDefinitionStory->generate();
        $this->characterStory->generate();
    }
}
