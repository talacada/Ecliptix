<?php

declare(strict_types=1);

namespace App\Infrastructure\Api;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class CurrentUserScope
{
    public function __construct(
        public string $field,
    ) {
    }
}
