<?php

namespace Pinga\Pingsess\Test;

use Pinga\Pingsess\MemorySession;

/**
 * Memory Session Test.
 *
 * @coversDefaultClass \Odan\Session\MemorySession
 */
class MemorySessionTest extends PhpSessionTest
{
    protected function setUp(): void
    {
        $this->session = new MemorySession();
        $this->manager = $this->session;
    }
}
