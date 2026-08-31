<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Auth;

use PHPUnit\Framework\TestCase;

class EmailVerificationServiceTest extends TestCase
{
    // TODO: testCreateTokenCreatesValidTokenWithCharacterAnd24HourExpiration() - assert returns EmailVerificationToken with correct Character, valid UUID, expiresAt in ~24h, and calls persist() + flush() on EntityManager
}
