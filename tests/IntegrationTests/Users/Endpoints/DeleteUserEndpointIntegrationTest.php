<?php

declare(strict_types=1);

namespace Olz\Tests\IntegrationTests\Api\Endpoints;

use Olz\Tests\IntegrationTests\Common\IntegrationTestCase;
use Olz\Users\Endpoints\DeleteUserEndpoint;

/**
 * @internal
 *
 * @coversNothing
 */
class DeleteUserEndpointForIntegrationTest extends DeleteUserEndpoint {
    public function testOnlyIsFile(string $path): bool {
        return $this->isFile($path);
    }

    public function testOnlyUnlink(string $path): void {
        $this->unlink($path);
    }
}

/**
 * @internal
 *
 * @covers \Olz\Api\Endpoints\DeleteUserEndpoint
 */
final class DeleteUserEndpointIntegrationTest extends IntegrationTestCase {
    public function testIsFile(): void {
        $endpoint = new DeleteUserEndpointForIntegrationTest();
        $this->assertTrue($endpoint->testOnlyIsFile(__FILE__));
        $this->assertFalse($endpoint->testOnlyIsFile(__DIR__.'/does_not_exist.txt'));
    }

    public function testUnlink(): void {
        $endpoint = new DeleteUserEndpointForIntegrationTest();
        $path = __DIR__.'/../../document-root/temp/delete_user_endpoint.txt';
        file_put_contents($path, 'some content');
        $this->assertTrue(is_file($path));
        $endpoint->testOnlyUnlink($path);
        $this->assertFalse(is_file($path));
    }
}
