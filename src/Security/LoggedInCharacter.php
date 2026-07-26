<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Character\Character;
use App\Repository\Shop\ShopRotationRepository;
use App\Service\Elixir\ElixirCleanUp;
use App\Service\Shop\RotationGenerator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class LoggedInCharacter
{
    public function __construct(
        private readonly Security               $security,
        private readonly ElixirCleanUp          $elixirCleanUp,
        private readonly ShopRotationRepository $shopRotationRepository,
        private readonly RotationGenerator      $rotationGenerator
    ) {
    }

    public function getCharacter(): Character
    {
        $character = $this->security->getUser();

        if (!$character instanceof Character) {
            throw new UnauthorizedHttpException('Not authenticated');
        }

        $this->elixirCleanUp->removeExpired($character);

        if ($this->shopRotationRepository->hasActiveDailyRotation($character)) {
            $this->rotationGenerator->generate($character);
        }

        return $character;
    }

    public function getCharacterOrNull(): ?Character
    {
        $character = $this->security->getUser();

        return $character instanceof Character ? $character : null;
    }
}
