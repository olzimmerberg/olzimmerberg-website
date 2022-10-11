<?php

declare(strict_types=1);

namespace Olz\Tests\IntegrationTests\Utils;

use Olz\Tests\IntegrationTests\Common\IntegrationTestCase;
use Olz\Utils\EnvUtils;

class FakeIntegrationTestEnvUtils extends EnvUtils {
    public static function fromEnv() {
        // For this test, clear the "cache" always
        parent::$from_env_instance = null;
        return parent::fromEnv();
    }
}

/**
 * @internal
 *
 * @covers \Olz\Utils\EnvUtils
 */
final class EnvUtilsIntegrationTest extends IntegrationTestCase {
    public function testEnvUtilsFromEnv(): void {
        $env_utils = FakeIntegrationTestEnvUtils::fromEnv();
        $this->assertMatchesRegularExpression(
            '/\/tests\/IntegrationTests\/document\-root\/$/',
            $env_utils->getDataPath()
        );
        $this->assertSame('/', $env_utils->getDataHref());
        $this->assertSame(
            realpath(__DIR__.'/../../..').'/',
            $env_utils->getCodePath()
        );
        $this->assertSame('/', $env_utils->getCodeHref());
        $this->assertSame('http://integration-test.host', $env_utils->getBaseHref());
    }

    public function testEnvUtilsFromEnvWithMissingConfigFile(): void {
        global $_SERVER;
        $previous_server = $_SERVER;
        $_SERVER = [
            'DOCUMENT_ROOT' => __DIR__, // no config file in here.
        ];

        try {
            $env_utils = FakeIntegrationTestEnvUtils::fromEnv();
            $this->fail('Error expected');
        } catch (\Exception $exc) {
            $this->assertSame('Konfigurationsdatei nicht gefunden!', $exc->getMessage());
        }

        $_SERVER = $previous_server;
    }

    public function testEnvUtilsGetConfigPathWithNoDocumentRoot(): void {
        global $_SERVER;
        $previous_server = $_SERVER;
        $_SERVER = []; // e.g. for doctrine cli-config.php

        $config_path = FakeIntegrationTestEnvUtils::getConfigPath();

        $this->assertMatchesRegularExpression(
            '/\/\.\.\/\.\.\/public\/config.php$/',
            $config_path
        );

        $_SERVER = $previous_server;
    }

    public function testEnvUtilsFromEnvWithinUnitTest(): void {
        global $_SERVER;
        $previous_server = $_SERVER;
        $_SERVER = [
            'DOCUMENT_ROOT' => $previous_server['DOCUMENT_ROOT'] ?? 'test-no-root',
            'argv' => ['phpunit', 'tests/UnitTests'],
        ];

        try {
            $env_utils = FakeIntegrationTestEnvUtils::fromEnv();
            $this->fail('Error expected');
        } catch (\Exception $exc) {
            $this->assertMatchesRegularExpression(
                '/^Unit tests should never use EnvUtils::fromEnv!/',
                $exc->getMessage()
            );
        }

        $_SERVER = $previous_server;
    }
}
