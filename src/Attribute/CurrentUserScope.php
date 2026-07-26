<?php

namespace App\Attribute;


use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class CurrentUserScope
{
    public function __construct(
        public string $field,
    ) {}
}
