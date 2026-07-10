<?php

declare(strict_types=1);

namespace App\ApiResource\Auth;

use App\Entity\Character\Character;
use Symfony\Component\Serializer\Attribute\Groups;

readonly class LoginOutput
{
    public function __construct(
        #[Groups(['login:read'])]
        public string $token,

        #[Groups(['login:read'])]
        public Character $character,
    ) {
    }
}
