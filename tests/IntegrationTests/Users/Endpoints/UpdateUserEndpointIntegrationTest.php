<?php

declare(strict_types=1);

namespace Olz\Tests\IntegrationTests\Api\Endpoints;

use Olz\Tests\IntegrationTests\Common\IntegrationTestCase;
use Olz\Users\Endpoints\UpdateUserEndpoint;

/**
 * @internal
 *
 * @coversNothing
 */
class UpdateUserEndpointForIntegrationTest extends UpdateUserEndpoint {
    public function testOnlyUnlink(string $path): void {
        $this->unlink($path);
    }

    public function testOnlyRename(string $source_path, string $destination_path): void {
        $this->rename($source_path, $destination_path);
    }
}

/**
 * @internal
 *
 * @covers \Olz\Users\Endpoints\UpdateUserEndpoint
 */
final class UpdateUserEndpointIntegrationTest extends IntegrationTestCase {
    public function testUnlink(): void {
        $endpoint = new UpdateUserEndpointForIntegrationTest();
        $path = __DIR__.'/../../document-root/temp/update_user_endpoint.txt';
        file_put_contents($path, 'some content');
        $this->assertTrue(is_file($path));
        $endpoint->testOnlyUnlink($path);
        $this->assertFalse(is_file($path));
    }

    public function testRename(): void {
        $endpoint = new UpdateUserEndpointForIntegrationTest();
        $from_path = __DIR__.'/../../document-root/temp/update_user_endpoint_from.txt';
        $to_path = __DIR__.'/../../document-root/temp/update_user_endpoint_to.txt';
        file_put_contents($from_path, 'some content');
        $this->assertTrue(is_file($from_path));
        $this->assertFalse(is_file($to_path));
        $endpoint->testOnlyRename($from_path, $to_path);
        $this->assertFalse(is_file($from_path));
        $this->assertTrue(is_file($to_path));
        unlink($to_path);
    }
}
