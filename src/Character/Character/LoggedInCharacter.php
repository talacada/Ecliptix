<?php

declare(strict_types=1);

namespace App\Character\Character;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class LoggedInCharacter
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function getCharacter(): Character
    {
        $character = $this->security->getUser();

        if (!$character instanceof Character) {
            throw new UnauthorizedHttpException('Not authenticated');
        }

        return $character;
    }
}
