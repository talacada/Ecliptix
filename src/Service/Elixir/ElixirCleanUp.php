<?php

declare(strict_types=1);

namespace App\Service\Elixir;

use App\Entity\Character\Character;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class ElixirCleanUp
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function removeExpired(Character $character): void
    {
        $now = new DateTime();
        $hasRemovedExpired = false;

        foreach ($character->getActiveElixirs()->toArray() as $elixir) {
            if ($elixir->getExpiresAt() < $now) {
                $character->removeActiveElixir($elixir);
                $hasRemovedExpired = true;
            }
        }

        if ($hasRemovedExpired) {
            $this->entityManager->flush();
        }
    }
}
