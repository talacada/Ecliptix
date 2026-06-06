<?php

namespace App\Security;

use App\Entity\Character\Character;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class LoggedInCharacter
{
    public function __construct(
        private Security $security,
    ) {}

    public function getCharacter(): Character
    {
        $character = $this->security->getUser();

        if (!$character instanceof Character) {
            throw new UnauthorizedHttpException('Not authenticated');
        }

        return $character;
    }

    public function getCharacterOrNull(): ?Character
    {
        $character = $this->security->getUser();

        return $character instanceof Character ? $character : null;
    }
}
