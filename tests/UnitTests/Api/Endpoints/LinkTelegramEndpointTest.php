<?php

declare(strict_types=1);

namespace Olz\Tests\UnitTests\Api\Endpoints;

use Olz\Api\Endpoints\LinkTelegramEndpoint;
use Olz\Tests\UnitTests\Common\UnitTestCase;
use Olz\Utils\MemorySession;

/**
 * @internal
 *
 * @covers \Olz\Api\Endpoints\LinkTelegramEndpoint
 */
final class LinkTelegramEndpointTest extends UnitTestCase {
    public function testLinkTelegramEndpoint(): void {
        $endpoint = new LinkTelegramEndpoint();
        $endpoint->runtimeSetup();
        $session = new MemorySession();
        $session->session_storage = [
            'auth' => 'ftp',
            'root' => 'karten',
            'user' => 'admin',
        ];
        $endpoint->setSession($session);

        $result = $endpoint->call([]);

        $this->assertSame([
            'INFO Valid user request',
            'INFO Valid user response',
        ], $this->getLogs());
        $this->assertSame([
            'botName' => 'bot-name',
            'pin' => 'correct-pin',
        ], $result);
    }
}
