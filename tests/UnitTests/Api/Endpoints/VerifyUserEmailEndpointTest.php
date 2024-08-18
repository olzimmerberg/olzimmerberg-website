<?php

declare(strict_types=1);

namespace Olz\Tests\UnitTests\Api\Endpoints;

use Olz\Api\Endpoints\VerifyUserEmailEndpoint;
use Olz\Exceptions\RecaptchaDeniedException;
use Olz\Tests\Fake\Entity\FakeUser;
use Olz\Tests\UnitTests\Common\UnitTestCase;
use Olz\Utils\WithUtilsCache;
use PhpTypeScriptApi\HttpError;

/**
 * @internal
 *
 * @covers \Olz\Api\Endpoints\VerifyUserEmailEndpoint
 */
final class VerifyUserEmailEndpointTest extends UnitTestCase {
    public function testVerifyUserEmailEndpointIdent(): void {
        $endpoint = new VerifyUserEmailEndpoint();
        $this->assertSame('VerifyUserEmailEndpoint', $endpoint->getIdent());
    }

    public function testVerifyUserEmailEndpoint(): void {
        $user = FakeUser::defaultUser();
        WithUtilsCache::get('authUtils')->current_user = $user;
        $endpoint = new VerifyUserEmailEndpoint();
        $endpoint->runtimeSetup();

        $result = $endpoint->call([
            'recaptchaToken' => 'fake-recaptcha-token',
        ]);

        $this->assertSame([
            "INFO Valid user request",
            "INFO Valid user response",
        ], $this->getLogs());
        $this->assertSame(['status' => 'OK'], $result);
        $this->assertSame([
            ['user' => $user],
        ], WithUtilsCache::get('emailUtils')->email_verification_emails_sent);
    }

    public function testVerifyUserEmailEndpointWithoutInput(): void {
        $endpoint = new VerifyUserEmailEndpoint();
        $endpoint->runtimeSetup();

        try {
            $endpoint->call([]);
            $this->fail('Exception expected.');
        } catch (HttpError $httperr) {
            $this->assertSame('Fehlerhafte Eingabe', $httperr->getMessage());
            $this->assertSame([
                "WARNING Bad user request",
            ], $this->getLogs());
        }
    }

    public function testVerifyUserEmailEndpointWithNullInput(): void {
        $endpoint = new VerifyUserEmailEndpoint();
        $endpoint->runtimeSetup();

        try {
            $endpoint->call([
                'recaptchaToken' => null,
            ]);
            $this->fail('Exception expected.');
        } catch (HttpError $httperr) {
            $this->assertSame('Fehlerhafte Eingabe', $httperr->getMessage());
            $this->assertSame([
                "WARNING Bad user request",
            ], $this->getLogs());
        }
    }

    public function testVerifyUserEmailEndpointUnauthenticated(): void {
        $endpoint = new VerifyUserEmailEndpoint();
        $endpoint->runtimeSetup();

        try {
            $endpoint->call([
                'recaptchaToken' => 'fake-recaptcha-token',
            ]);
            $this->fail('Exception expected.');
        } catch (HttpError $httperr) {
            $this->assertSame('Nicht eingeloggt!', $httperr->getMessage());
            $this->assertSame([
                "INFO Valid user request",
                "WARNING HTTP error 401",
            ], $this->getLogs());
        }
    }

    public function testVerifyUserEmailEndpointInvalidRecaptchaToken(): void {
        WithUtilsCache::get('authUtils')->current_user = FakeUser::defaultUser();
        WithUtilsCache::get('emailUtils')->send_email_verification_email_error = new RecaptchaDeniedException('test');
        $endpoint = new VerifyUserEmailEndpoint();
        $endpoint->runtimeSetup();

        $result = $endpoint->call([
            'recaptchaToken' => 'invalid-recaptcha-token',
        ]);

        $this->assertSame([
            "INFO Valid user request",
            "WARNING reCaptcha token was invalid",
            "NOTICE Recaptcha denied for user (ID:1)",
            "INFO Valid user response",
        ], $this->getLogs());
        $this->assertSame(['status' => 'DENIED'], $result);
        $this->assertSame([], WithUtilsCache::get('emailUtils')->email_verification_emails_sent);
    }

    public function testVerifyUserEmailEndpointErrorSending(): void {
        WithUtilsCache::get('authUtils')->current_user = FakeUser::defaultUser();
        WithUtilsCache::get('emailUtils')->send_email_verification_email_error = new \Exception('test');
        $endpoint = new VerifyUserEmailEndpoint();
        $endpoint->runtimeSetup();

        $result = $endpoint->call([
            'recaptchaToken' => 'fake-recaptcha-token',
        ]);

        $this->assertSame([
            "INFO Valid user request",
            "ERROR Error sending fake verification email",
            "ERROR Error verifying email for user (ID:1)",
            "INFO Valid user response",
        ], $this->getLogs());
        $this->assertSame(['status' => 'ERROR'], $result);
        $this->assertSame([], WithUtilsCache::get('emailUtils')->email_verification_emails_sent);
    }
}
