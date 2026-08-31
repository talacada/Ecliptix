<?php

namespace App\Tests\Unit\Service\Elixir;


use PHPUnit\Framework\TestCase;

class ElixirCleanUpTest extends TestCase
{
    // TODO: testRemoveExpiredRemovesExpiredElixirsAndKeepsActiveOnes() - mock EntityManager, assert remove() is called only for expired elixirs (expiresAt < now)
    // TODO: testRemoveExpiredCallsFlushOnce() - assert flush() is always called on EntityManager
}
