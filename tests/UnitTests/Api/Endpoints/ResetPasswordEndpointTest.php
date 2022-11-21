<?php

declare(strict_types=1);

namespace Olz\Tests\UnitTests\Api\Endpoints;

use Olz\Api\Endpoints\ResetPasswordEndpoint;
use Olz\Tests\Fake\FakeEmailUtils;
use Olz\Tests\Fake\FakeEntityManager;
use Olz\Tests\Fake\FakeEnvUtils;
use Olz\Tests\Fake\FakeLogger;
use Olz\Tests\Fake\FakeRecaptchaUtils;
use Olz\Tests\Fake\FakeUserRepository;
use Olz\Tests\Fake\FakeUsers;
use Olz\Tests\UnitTests\Common\UnitTestCase;
use Olz\Utils\GeneralUtils;
use PhpTypeScriptApi\HttpError;

class DeterministicResetPasswordEndpoint extends ResetPasswordEndpoint {
    public function __construct() {
        $this->setServer(['REMOTE_ADDR' => '1.2.3.4']);
    }

    protected function getRandomPassword() {
        return 'fake-new-password';
    }
}

/**
 * @internal
 *
 * @covers \Olz\Api\Endpoints\ResetPasswordEndpoint
 */
final class ResetPasswordEndpointTest extends UnitTestCase {
    public function testResetPasswordEndpointIdent(): void {
        $endpoint = new DeterministicResetPasswordEndpoint();
        $this->assertSame('ResetPasswordEndpoint', $endpoint->getIdent());
    }

    public function testResetPasswordEndpointWithoutInput(): void {
        $logger = FakeLogger::create();
        $endpoint = new DeterministicResetPasswordEndpoint();
        $endpoint->setLog($logger);
        try {
            $result = $endpoint->call([]);
            $this->fail('Exception expected.');
        } catch (HttpError $httperr) {
            $this->assertSame([
                'usernameOrEmail' => ["Fehlender Schlüssel: usernameOrEmail."],
                'recaptchaToken' => ["Fehlender Schlüssel: recaptchaToken."],
            ], $httperr->getPrevious()->getValidationErrors());
            $this->assertSame([
                "WARNING Bad user request",
            ], $logger->handler->getPrettyRecords());
        }
    }

    public function testResetPasswordEndpointWithNullInput(): void {
        $logger = FakeLogger::create();
        $endpoint = new DeterministicResetPasswordEndpoint();
        $endpoint->setLog($logger);
        try {
            $result = $endpoint->call([
                'usernameOrEmail' => null,
                'recaptchaToken' => null,
            ]);
            $this->fail('Exception expected.');
        } catch (HttpError $httperr) {
            $this->assertSame([
                'usernameOrEmail' => [['.' => ['Feld darf nicht leer sein.']]],
                'recaptchaToken' => [['.' => ['Feld darf nicht leer sein.']]],
            ], $httperr->getPrevious()->getValidationErrors());
            $this->assertSame([
                "WARNING Bad user request",
            ], $logger->handler->getPrettyRecords());
        }
    }

    public function testResetPasswordEndpoint(): void {
        $logger = FakeLogger::create();
        $endpoint = new DeterministicResetPasswordEndpoint();
        $email_utils = new FakeEmailUtils();
        $endpoint->setEmailUtils($email_utils);
        $entity_manager = new FakeEntityManager();
        $user_repo = new FakeUserRepository();
        $entity_manager->repositories[User::class] = $user_repo;
        $endpoint->setEntityManager($entity_manager);
        $env_utils = new FakeEnvUtils();
        $endpoint->setEnvUtils($env_utils);
        $general_utils = new GeneralUtils();
        $endpoint->setGeneralUtils($general_utils);
        $endpoint->setLog($logger);
        $endpoint->setRecaptchaUtils(new FakeRecaptchaUtils());

        $result = $endpoint->call([
            'usernameOrEmail' => 'admin',
            'recaptchaToken' => 'fake-recaptcha-token',
        ]);

        $expected_text = <<<'ZZZZZZZZZZ'
        **!!! Falls du nicht soeben dein Passwort zurücksetzen wolltest, lösche diese E-Mail !!!**
        
        Hallo Admin,
        
        *Falls du dein Passwort zurückzusetzen möchtest*, klicke [hier](http://fake-base-url/_/email_reaktion.php?token=eyJhY3Rpb24iOiJyZXNldF9wYXNzd29yZCIsInVzZXIiOjIsIm5ld19wYXNzd29yZCI6ImZha2UtbmV3LXBhc3N3b3JkIn0}) oder auf folgenden Link:
        
        http://fake-base-url/_/email_reaktion.php?token=eyJhY3Rpb24iOiJyZXNldF9wYXNzd29yZCIsInVzZXIiOjIsIm5ld19wYXNzd29yZCI6ImZha2UtbmV3LXBhc3N3b3JkIn0
        
        Dein neues Passwort lautet dann nachher:
        `fake-new-password`

        ZZZZZZZZZZ;
        $this->assertSame(['status' => 'OK'], $result);
        $this->assertSame([
            [FakeUsers::adminUser(), '[OLZ] Passwort zurücksetzen', $expected_text, $expected_text],
        ], $email_utils->olzMailer->emails_sent);
        $this->assertSame([
            "INFO Valid user request",
            "INFO Password reset email sent to user (2).",
            "INFO Valid user response",
        ], $logger->handler->getPrettyRecords());
    }

    public function testResetPasswordEndpointUsingEmailErrorSending(): void {
        $logger = FakeLogger::create();
        $endpoint = new DeterministicResetPasswordEndpoint();
        $email_utils = new FakeEmailUtils();
        $endpoint->setEmailUtils($email_utils);
        $entity_manager = new FakeEntityManager();
        $user_repo = new FakeUserRepository();
        $entity_manager->repositories[User::class] = $user_repo;
        $endpoint->setEntityManager($entity_manager);
        $env_utils = new FakeEnvUtils();
        $endpoint->setEnvUtils($env_utils);
        $general_utils = new GeneralUtils();
        $endpoint->setGeneralUtils($general_utils);
        $endpoint->setLog($logger);
        $endpoint->setRecaptchaUtils(new FakeRecaptchaUtils());
        $vorstand_user = FakeUsers::vorstandUser();
        $vorstand_user->setFirstName('provoke_error');

        $result = $endpoint->call([
            'usernameOrEmail' => 'vorstand@test.olzimmerberg.ch',
            'recaptchaToken' => 'fake-recaptcha-token',
        ]);

        $this->assertSame(['status' => 'ERROR'], $result);
        $this->assertSame([], $email_utils->olzMailer->emails_sent);
        $this->assertSame([
            "INFO Valid user request",
            "CRITICAL Error sending password reset email to user (3): Provoked Mailer Error",
            "INFO Valid user response",
        ], $logger->handler->getPrettyRecords());
    }

    public function testResetPasswordEndpointInvalidUser(): void {
        $logger = FakeLogger::create();
        $endpoint = new DeterministicResetPasswordEndpoint();
        $entity_manager = new FakeEntityManager();
        $user_repo = new FakeUserRepository();
        $entity_manager->repositories[User::class] = $user_repo;
        $endpoint->setEntityManager($entity_manager);
        $env_utils = new FakeEnvUtils();
        $endpoint->setEnvUtils($env_utils);
        $endpoint->setLog($logger);
        $endpoint->setRecaptchaUtils(new FakeRecaptchaUtils());

        $result = $endpoint->call([
            'usernameOrEmail' => 'invalid',
            'recaptchaToken' => 'fake-recaptcha-token',
        ]);

        $this->assertSame(['status' => 'DENIED'], $result);
        $this->assertSame([
            "INFO Valid user request",
            "NOTICE Password reset for unknown user: invalid.",
            "INFO Valid user response",
        ], $logger->handler->getPrettyRecords());
    }

    public function testResetPasswordEndpointInvalidRecaptchaToken(): void {
        $logger = FakeLogger::create();
        $endpoint = new DeterministicResetPasswordEndpoint();
        $entity_manager = new FakeEntityManager();
        $user_repo = new FakeUserRepository();
        $entity_manager->repositories[User::class] = $user_repo;
        $endpoint->setEntityManager($entity_manager);
        $env_utils = new FakeEnvUtils();
        $endpoint->setEnvUtils($env_utils);
        $endpoint->setLog($logger);
        $endpoint->setRecaptchaUtils(new FakeRecaptchaUtils());

        $result = $endpoint->call([
            'usernameOrEmail' => 'admin',
            'recaptchaToken' => 'invalid-recaptcha-token',
        ]);

        $this->assertSame(['status' => 'DENIED'], $result);
        $this->assertSame([
            "INFO Valid user request",
            "INFO Valid user response",
        ], $logger->handler->getPrettyRecords());
    }
}
