<?php

namespace Pinga\Pingsess\Test;

use Middlewares\Utils\Dispatcher;
use Pinga\Pingsess\Middleware\SessionStartMiddleware;
use Pinga\Pingsess\PhpSession;
use PHPUnit\Framework\TestCase;

/**
 * Test.
 *
 * @coversDefaultClass \Odan\Session\Middleware\SessionStartMiddleware
 */
class SessionStartMiddlewareTest extends TestCase
{
    private PhpSession $session;

    private SessionStartMiddleware $middleware;

    protected function setUp(): void
    {
        $_SESSION = [];

        $this->session = new PhpSession([
            'name' => 'app',
            // turn off automatic sending of cache headers entirely
            'cache_limiter' => '',
            // garbage collection
            'gc_probability' => 1,
            'gc_divisor' => 1,
            'gc_maxlifetime' => 30 * 24 * 60 * 60,
            'save_path' => getenv('GITHUB_ACTIONS') ? '/tmp' : '',
        ]);

        $this->middleware = new SessionStartMiddleware($this->session);
    }

    public function testInvoke(): void
    {
        // Session must not be started
        $this->assertFalse($this->session->isStarted());

        Dispatcher::run([
            $this->middleware,
        ]);

        // Session must be closed
        $this->assertFalse($this->session->isStarted());
    }
}
