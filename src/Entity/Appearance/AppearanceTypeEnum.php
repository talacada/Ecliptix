<?php

declare(strict_types=1);

namespace App\Entity\Appearance;

enum AppearanceTypeEnum: string
{
    case hair = 'hair';
    case eyes = 'eyes';
    case mouth = 'mouth';
    case nose = 'nose';
    case ears = 'ears';
}
