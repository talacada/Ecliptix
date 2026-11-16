<?php

declare(strict_types=1);

namespace App\Auth\Login;

use App\Character\Character\Character;
use Symfony\Component\Serializer\Attribute\Groups;

readonly class LoginOutput
{
    public const string READ_GROUP = 'login:read';

    public function __construct(
        #[Groups([self::READ_GROUP])]
        public string $token,

        #[Groups([self::READ_GROUP])]
        public Character $character,
    ) {
    }
}
