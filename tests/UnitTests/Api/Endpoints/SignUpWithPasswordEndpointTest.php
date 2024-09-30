<?php

declare(strict_types=1);

namespace Olz\Tests\UnitTests\Api\Endpoints;

use Olz\Api\Endpoints\SignUpWithPasswordEndpoint;
use Olz\Entity\AuthRequest;
use Olz\Tests\Fake;
use Olz\Tests\Fake\Entity\FakeUser;
use Olz\Tests\UnitTests\Common\UnitTestCase;
use Olz\Utils\MemorySession;
use Olz\Utils\WithUtilsCache;
use PhpTypeScriptApi\HttpError;

/**
 * @internal
 *
 * @deprecated Use CreateUserEndpoint instead
 *
 * @covers \Olz\Api\Endpoints\SignUpWithPasswordEndpoint
 */
final class SignUpWithPasswordEndpointTest extends UnitTestCase {
    public const MINIMAL_INPUT = [
        'firstName' => 'fakeFirstName',
        'lastName' => 'fakeLastName',
        'username' => 'minimal-user',
        'password' => 'securePassword',
        'email' => 'minimal-user@staging.olzimmerberg.ch',
        'phone' => null,
        'gender' => null,
        'birthdate' => null,
        'street' => null,
        'postalCode' => null,
        'city' => null,
        'region' => null,
        'countryCode' => null,
        'siCardNumber' => null,
        'solvNumber' => null,
        'recaptchaToken' => 'fake-recaptcha-token',
    ];
    public const MAXIMAL_INPUT = [
        'firstName' => 'fakeFirstName',
        'lastName' => 'fakeLastName',
        'username' => 'maximal-user',
        'password' => 'securePassword',
        'email' => 'maximal-user@staging.olzimmerberg.ch',
        'phone' => '+41441234567',
        'gender' => 'M',
        'birthdate' => '2020-03-13',
        'street' => 'fakeStreet',
        'postalCode' => 'fakePostalCode',
        'city' => 'fakeCity',
        'region' => 'fakeRegion',
        'countryCode' => 'CC',
        'siCardNumber' => 1234567,
        'solvNumber' => 'JACK7NORRIS',
        'recaptchaToken' => 'fake-recaptcha-token',
    ];

    public function testSignUpWithPasswordEndpointIdent(): void {
        $endpoint = new SignUpWithPasswordEndpoint();
        $this->assertSame('SignUpWithPasswordEndpoint', $endpoint->getIdent());
    }

    public function testSignUpWithPasswordEndpointWithoutInput(): void {
        $endpoint = new SignUpWithPasswordEndpoint();
        $endpoint->runtimeSetup();
        try {
            $endpoint->call([
                'firstName' => null,
                'lastName' => null,
                'username' => null,
                'password' => null,
                'email' => null,
                'phone' => null,
                'gender' => null,
                'birthdate' => null,
                'street' => null,
                'postalCode' => null,
                'city' => null,
                'region' => null,
                'countryCode' => null,
                'siCardNumber' => null,
                'solvNumber' => null,
                'recaptchaToken' => null,
            ]);
            $this->fail('Exception expected.');
        } catch (HttpError $httperr) {
            $this->assertSame([
                "WARNING Bad user request",
            ], $this->getLogs());
            $this->assertSame('Fehlerhafte Eingabe', $httperr->getMessage());
            $this->assertSame([
                'firstName' => [['.' => ['Feld darf nicht leer sein.']]],
                'lastName' => [['.' => ['Feld darf nicht leer sein.']]],
                'username' => [['.' => ['Feld darf nicht leer sein.']]],
                'recaptchaToken' => [['.' => ['Feld darf nicht leer sein.']]],
                // @phpstan-ignore-next-line
            ], $httperr->getPrevious()->getValidationErrors());
        }
    }

    public function testSignUpWithPasswordEndpointWithInvalidRecaptchaToken(): void {
        $endpoint = new SignUpWithPasswordEndpoint();
        $endpoint->runtimeSetup();
        $session = new MemorySession();
        $endpoint->setSession($session);
        $endpoint->setServer(['REMOTE_ADDR' => '1.2.3.4']);

        $result = $endpoint->call([
            ...self::MINIMAL_INPUT,
            'recaptchaToken' => 'invalid-token',
        ]);

        $this->assertSame([
            'status' => 'DENIED',
        ], $result);
        $this->assertSame([], $session->session_storage);
    }

    public function testSignUpWithPasswordEndpointWithInvalidUsername(): void {
        $endpoint = new SignUpWithPasswordEndpoint();
        $endpoint->runtimeSetup();
        $session = new MemorySession();
        $endpoint->setSession($session);
        $endpoint->setServer(['REMOTE_ADDR' => '1.2.3.4']);

        try {
            $endpoint->call([
                ...self::MINIMAL_INPUT,
                'username' => 'inv@lid',
            ]);
            $this->fail('Exception expected.');
        } catch (HttpError $httperr) {
            $this->assertSame([
                "INFO Valid user request",
                "INFO New sign-up (using password): fakeFirstName fakeLastName (inv@lid@) <minimal-user@staging.olzimmerberg.ch>",
                "WARNING Bad user request",
            ], $this->getLogs());
            $this->assertSame('Fehlerhafte Eingabe', $httperr->getMessage());
            $this->assertSame([
                'username' => ['Der Benutzername darf nur Buchstaben, Zahlen, und die Zeichen -_. enthalten.'],
                // @phpstan-ignore-next-line
            ], $httperr->getPrevious()->getValidationErrors());
        }
    }

    public function testSignUpWithPasswordEndpointWithShortPassword(): void {
        $endpoint = new SignUpWithPasswordEndpoint();
        $endpoint->runtimeSetup();
        $session = new MemorySession();
        $endpoint->setSession($session);
        $endpoint->setServer(['REMOTE_ADDR' => '1.2.3.4']);

        try {
            $endpoint->call([
                ...self::MINIMAL_INPUT,
                'password' => 'short',
            ]);
            $this->fail('Exception expected.');
        } catch (HttpError $httperr) {
            $this->assertSame([
                "INFO Valid user request",
                "INFO New sign-up (using password): fakeFirstName fakeLastName (minimal-user@) <minimal-user@staging.olzimmerberg.ch>",
                "WARNING Bad user request",
            ], $this->getLogs());
            $this->assertSame('Fehlerhafte Eingabe', $httperr->getMessage());
            $this->assertSame([
                'password' => ['Das Passwort muss mindestens 8 Zeichen lang sein.'],
                // @phpstan-ignore-next-line
            ], $httperr->getPrevious()->getValidationErrors());
        }
    }

    public function testSignUpWithPasswordEndpointWithOlzimmerbergEmail(): void {
        $endpoint = new SignUpWithPasswordEndpoint();
        $endpoint->runtimeSetup();
        $session = new MemorySession();
        $endpoint->setSession($session);
        $endpoint->setServer(['REMOTE_ADDR' => '1.2.3.4']);

        try {
            $endpoint->call([
                ...self::MINIMAL_INPUT,
                'email' => 'bot@olzimmerberg.ch',
            ]);
            $this->fail('Exception expected.');
        } catch (HttpError $httperr) {
            $this->assertSame([
                "INFO Valid user request",
                "INFO New sign-up (using password): fakeFirstName fakeLastName (minimal-user@) <bot@olzimmerberg.ch>",
                "WARNING Bad user request",
            ], $this->getLogs());
            $this->assertSame('Fehlerhafte Eingabe', $httperr->getMessage());
            $this->assertSame([
                'email' => ['Bitte keine @olzimmerberg.ch E-Mail verwenden.'],
                // @phpstan-ignore-next-line
            ], $httperr->getPrevious()->getValidationErrors());
        }
    }

    public function testSignUpWithPasswordEndpointWithoutEmail(): void {
        $endpoint = new SignUpWithPasswordEndpoint();
        $endpoint->runtimeSetup();
        $session = new MemorySession();
        $endpoint->setSession($session);
        $endpoint->setServer(['REMOTE_ADDR' => '1.2.3.4']);

        try {
            $endpoint->call([
                ...self::MINIMAL_INPUT,
                'email' => null,
            ]);
            $this->fail('Exception expected.');
        } catch (HttpError $httperr) {
            $this->assertSame([
                "INFO Valid user request",
                "INFO New sign-up (using password): fakeFirstName fakeLastName (minimal-user@) <>",
                "WARNING Bad user request",
            ], $this->getLogs());
            $this->assertSame('Fehlerhafte Eingabe', $httperr->getMessage());
            $this->assertSame([
                'email' => ['Feld darf nicht leer sein.'],
                // @phpstan-ignore-next-line
            ], $httperr->getPrevious()->getValidationErrors());
        }
    }

    public function testSignUpWithPasswordEndpointWithoutPassword(): void {
        $endpoint = new SignUpWithPasswordEndpoint();
        $endpoint->runtimeSetup();
        $session = new MemorySession();
        $endpoint->setSession($session);
        $endpoint->setServer(['REMOTE_ADDR' => '1.2.3.4']);

        try {
            $endpoint->call([
                ...self::MINIMAL_INPUT,
                'password' => null,
            ]);
            $this->fail('Exception expected.');
        } catch (HttpError $httperr) {
            $this->assertSame([
                "INFO Valid user request",
                "INFO New sign-up (using password): fakeFirstName fakeLastName (minimal-user@) <minimal-user@staging.olzimmerberg.ch>",
                "WARNING Bad user request",
            ], $this->getLogs());
            $this->assertSame('Fehlerhafte Eingabe', $httperr->getMessage());
            $this->assertSame([
                'password' => ['Feld darf nicht leer sein.'],
                // @phpstan-ignore-next-line
            ], $httperr->getPrevious()->getValidationErrors());
        }
    }

    public function testSignUpWithPasswordEndpointWithMinimalDataForNewUser(): void {
        $endpoint = new SignUpWithPasswordEndpoint();
        $endpoint->runtimeSetup();
        $session = new MemorySession();
        $endpoint->setSession($session);
        $endpoint->setServer(['REMOTE_ADDR' => '1.2.3.4']);

        $result = $endpoint->call(self::MINIMAL_INPUT);

        $this->assertSame([
            "INFO Valid user request",
            "INFO New sign-up (using password): fakeFirstName fakeLastName (minimal-user@) <minimal-user@staging.olzimmerberg.ch>",
            "INFO Valid user response",
        ], $this->getLogs());
        $this->assertSame([
            'status' => 'OK',
        ], $result);
        $this->assertSame([
            'auth' => '',
            'root' => null,
            'user' => 'minimal-user',
            'user_id' => strval(Fake\FakeEntityManager::AUTO_INCREMENT_ID),
            'auth_user' => 'minimal-user',
            'auth_user_id' => strval(Fake\FakeEntityManager::AUTO_INCREMENT_ID),
        ], $session->session_storage);
        $entity_manager = WithUtilsCache::get('entityManager');
        $this->assertSame([
            [
                'ip_address' => '1.2.3.4',
                'action' => 'AUTHENTICATED_PASSWORD',
                'timestamp' => null,
                'username' => 'minimal-user',
            ],
        ], $entity_manager->getRepository(AuthRequest::class)->auth_requests);
        $this->assertCount(1, $entity_manager->persisted);
        $user = $entity_manager->persisted[0];
        $this->assertSame(Fake\FakeEntityManager::AUTO_INCREMENT_ID, $user->getId());
        $this->assertSame('minimal-user', $user->getUsername());
        $this->assertNull($user->getOldUsername());
        $this->assertSame('minimal-user@staging.olzimmerberg.ch', $user->getEmail());
        $this->assertFalse($user->isEmailVerified());
        $this->assertNull($user->getEmailVerificationToken());
        $this->assertFalse($user->hasPermission('verified_email'));
        $this->assertSame('fakeFirstName', $user->getFirstName());
        $this->assertSame('fakeLastName', $user->getLastName());
        $this->assertNull($user->getPhone());
        $this->assertNull($user->getGender());
        $this->assertNull($user->getBirthdate());
        $this->assertNull($user->getStreet());
        $this->assertNull($user->getPostalCode());
        $this->assertNull($user->getCity());
        $this->assertNull($user->getRegion());
        $this->assertNull($user->getCountryCode());
        $this->assertSame(
            '2020-03-13 19:30:00',
            $user->getLastModifiedAt()->format('Y-m-d H:i:s')
        );
        $this->assertSame($entity_manager->persisted, $entity_manager->flushed_persisted);
    }

    public function testSignUpWithPasswordEndpointWithMaximalDataForNewUser(): void {
        $endpoint = new SignUpWithPasswordEndpoint();
        $endpoint->runtimeSetup();
        $session = new MemorySession();
        $endpoint->setSession($session);
        $endpoint->setServer(['REMOTE_ADDR' => '1.2.3.4']);

        $result = $endpoint->call(self::MAXIMAL_INPUT);

        $this->assertSame([
            "INFO Valid user request",
            "INFO New sign-up (using password): fakeFirstName fakeLastName (minimal-user@) <minimal-user@staging.olzimmerberg.ch>",
            "INFO Valid user response",
        ], $this->getLogs());
        $this->assertSame([
            'status' => 'OK',
        ], $result);
        $this->assertSame([
            'auth' => '',
            'root' => null,
            'user' => 'minimal-user',
            'user_id' => strval(Fake\FakeEntityManager::AUTO_INCREMENT_ID),
            'auth_user' => 'minimal-user',
            'auth_user_id' => strval(Fake\FakeEntityManager::AUTO_INCREMENT_ID),
        ], $session->session_storage);
        $entity_manager = WithUtilsCache::get('entityManager');
        $this->assertSame([
            [
                'ip_address' => '1.2.3.4',
                'action' => 'AUTHENTICATED_PASSWORD',
                'timestamp' => null,
                'username' => 'minimal-user',
            ],
        ], $entity_manager->getRepository(AuthRequest::class)->auth_requests);
        $this->assertCount(1, $entity_manager->persisted);
        $user = $entity_manager->persisted[0];
        $this->assertSame(Fake\FakeEntityManager::AUTO_INCREMENT_ID, $user->getId());
        $this->assertSame('minimal-user', $user->getUsername());
        $this->assertNull($user->getOldUsername());
        $this->assertSame('minimal-user@staging.olzimmerberg.ch', $user->getEmail());
        $this->assertFalse($user->isEmailVerified());
        $this->assertNull($user->getEmailVerificationToken());
        $this->assertFalse($user->hasPermission('verified_email'));
        $this->assertSame('fakeFirstName', $user->getFirstName());
        $this->assertSame('fakeLastName', $user->getLastName());
        $this->assertSame('+41441234567', $user->getPhone());
        $this->assertSame('M', $user->getGender());
        $this->assertSame('2020-03-13 12:00:00', $user->getBirthdate()->format('Y-m-d H:i:s'));
        $this->assertSame('fakeStreet', $user->getStreet());
        $this->assertSame('fakePostalCode', $user->getPostalCode());
        $this->assertSame('fakeCity', $user->getCity());
        $this->assertSame('fakeRegion', $user->getRegion());
        $this->assertSame('CC', $user->getCountryCode());
        $this->assertSame(
            '2020-03-13 19:30:00',
            $user->getLastModifiedAt()->format('Y-m-d H:i:s')
        );
        $this->assertSame($entity_manager->persisted, $entity_manager->flushed_persisted);
    }

    public function testSignUpWithPasswordEndpointWithMinimalDataForNewFamilyUser(): void {
        WithUtilsCache::get('authUtils')->current_user = FakeUser::adminUser();
        $endpoint = new SignUpWithPasswordEndpoint();
        $endpoint->runtimeSetup();
        $session = new MemorySession();
        $endpoint->setSession($session);
        $endpoint->setServer(['REMOTE_ADDR' => '1.2.3.4']);

        $result = $endpoint->call([
            ...self::MINIMAL_INPUT,
            'email' => null,
            'password' => null,
        ]);

        $this->assertSame([
            "INFO Valid user request",
            "INFO New sign-up (using password): fakeFirstName fakeLastName (minimal-user@) <>",
            "INFO Valid user response",
        ], $this->getLogs());
        $this->assertSame([
            'status' => 'OK',
        ], $result);
        $this->assertSame([], $session->session_storage);
        $entity_manager = WithUtilsCache::get('entityManager');
        $this->assertSame([], $entity_manager->getRepository(AuthRequest::class)->auth_requests);
        $this->assertCount(1, $entity_manager->persisted);
        $user = $entity_manager->persisted[0];
        $this->assertSame(Fake\FakeEntityManager::AUTO_INCREMENT_ID, $user->getId());
        $this->assertSame('minimal-user', $user->getUsername());
        $this->assertNull($user->getOldUsername());
        $this->assertNull($user->getEmail());
        $this->assertFalse($user->isEmailVerified());
        $this->assertNull($user->getEmailVerificationToken());
        $this->assertFalse($user->hasPermission('verified_email'));
        $this->assertSame('fakeFirstName', $user->getFirstName());
        $this->assertSame('fakeLastName', $user->getLastName());
        $this->assertNull($user->getPhone());
        $this->assertNull($user->getGender());
        $this->assertNull($user->getBirthdate());
        $this->assertNull($user->getStreet());
        $this->assertNull($user->getPostalCode());
        $this->assertNull($user->getCity());
        $this->assertNull($user->getRegion());
        $this->assertNull($user->getCountryCode());
        $this->assertSame(
            '2020-03-13 19:30:00',
            $user->getLastModifiedAt()->format('Y-m-d H:i:s')
        );
        $this->assertSame($entity_manager->persisted, $entity_manager->flushed_persisted);
    }

    public function testSignUpWithPasswordEndpointWithMaximalDataForNewFamilyUser(): void {
        WithUtilsCache::get('authUtils')->current_user = FakeUser::adminUser();
        $endpoint = new SignUpWithPasswordEndpoint();
        $endpoint->runtimeSetup();
        $session = new MemorySession();
        $endpoint->setSession($session);
        $endpoint->setServer(['REMOTE_ADDR' => '1.2.3.4']);

        $result = $endpoint->call(self::MAXIMAL_INPUT);

        $this->assertSame([
            "INFO Valid user request",
            "INFO New sign-up (using password): fakeFirstName fakeLastName (minimal-user@) <minimal-user@staging.olzimmerberg.ch>",
            "INFO Valid user response",
        ], $this->getLogs());
        $this->assertSame([
            'status' => 'OK',
        ], $result);
        $this->assertSame([], $session->session_storage);
        $entity_manager = WithUtilsCache::get('entityManager');
        $this->assertSame([], $entity_manager->getRepository(AuthRequest::class)->auth_requests);
        $this->assertCount(1, $entity_manager->persisted);
        $user = $entity_manager->persisted[0];
        $this->assertSame(Fake\FakeEntityManager::AUTO_INCREMENT_ID, $user->getId());
        $this->assertSame('minimal-user', $user->getUsername());
        $this->assertNull($user->getOldUsername());
        $this->assertSame('minimal-user@staging.olzimmerberg.ch', $user->getEmail());
        $this->assertFalse($user->isEmailVerified());
        $this->assertNull($user->getEmailVerificationToken());
        $this->assertFalse($user->hasPermission('verified_email'));
        $this->assertSame('fakeFirstName', $user->getFirstName());
        $this->assertSame('fakeLastName', $user->getLastName());
        $this->assertSame('+41441234567', $user->getPhone());
        $this->assertSame('M', $user->getGender());
        $this->assertSame('2020-03-13 12:00:00', $user->getBirthdate()->format('Y-m-d H:i:s'));
        $this->assertSame('fakeStreet', $user->getStreet());
        $this->assertSame('fakePostalCode', $user->getPostalCode());
        $this->assertSame('fakeCity', $user->getCity());
        $this->assertSame('fakeRegion', $user->getRegion());
        $this->assertSame('CC', $user->getCountryCode());
        $this->assertSame(
            '2020-03-13 19:30:00',
            $user->getLastModifiedAt()->format('Y-m-d H:i:s')
        );
        $this->assertSame($entity_manager->persisted, $entity_manager->flushed_persisted);
    }

    public function testSignUpWithPasswordEndpointWithValidDataForExistingUsernameWithoutPassword(): void {
        $endpoint = new SignUpWithPasswordEndpoint();
        $endpoint->runtimeSetup();
        $session = new MemorySession();
        $endpoint->setSession($session);
        $endpoint->setServer(['REMOTE_ADDR' => '1.2.3.4']);

        $result = $endpoint->call([
            ...self::MINIMAL_INPUT,
            'username' => 'child1', // has no password yet
        ]);

        $this->assertSame([
            "INFO Valid user request",
            "INFO New sign-up (using password): fakeFirstName fakeLastName (child1@) <minimal-user@staging.olzimmerberg.ch>",
            "INFO Valid user response",
        ], $this->getLogs());
        $this->assertSame([
            'status' => 'OK',
        ], $result);
        $this->assertSame([
            'auth' => '',
            'root' => null,
            'user' => 'child1',
            'user_id' => '5',
            'auth_user' => 'child1',
            'auth_user_id' => '5',
        ], $session->session_storage);
        $entity_manager = WithUtilsCache::get('entityManager');
        $this->assertSame([
            [
                'ip_address' => '1.2.3.4',
                'action' => 'AUTHENTICATED_PASSWORD',
                'timestamp' => null,
                'username' => 'child1',
            ],
        ], $entity_manager->getRepository(AuthRequest::class)->auth_requests);
    }

    public function testSignUpWithPasswordEndpointWithValidDataForExistingUsernameWithPassword(): void {
        $endpoint = new SignUpWithPasswordEndpoint();
        $endpoint->runtimeSetup();
        $session = new MemorySession();
        $endpoint->setSession($session);
        $endpoint->setServer(['REMOTE_ADDR' => '1.2.3.4']);

        try {
            $endpoint->call([
                ...self::MINIMAL_INPUT,
                'username' => 'admin', // has a password
            ]);
            $this->fail('Exception expected.');
        } catch (HttpError $httperr) {
            $this->assertSame([
                "INFO Valid user request",
                "INFO New sign-up (using password): fakeFirstName fakeLastName (admin@) <minimal-user@staging.olzimmerberg.ch>",
                "WARNING Bad user request",
            ], $this->getLogs());
            $this->assertSame('Fehlerhafte Eingabe', $httperr->getMessage());
            $this->assertSame(
                [
                    'message' => 'Fehlerhafte Eingabe',
                    'error' => [
                        'type' => 'ValidationError',
                        'validationErrors' => [
                            'username' => ['Es existiert bereits eine Person mit diesem Benutzernamen. Wolltest du gar kein Konto erstellen, sondern dich nur einloggen?'],
                        ],
                    ],
                ],
                $httperr->getStructuredAnswer(),
            );
        }
    }

    public function testSignUpWithPasswordEndpointWithValidDataForExistingEmailWithoutPassword(): void {
        $endpoint = new SignUpWithPasswordEndpoint();
        $endpoint->runtimeSetup();
        $session = new MemorySession();
        $endpoint->setSession($session);
        $endpoint->setServer(['REMOTE_ADDR' => '1.2.3.4']);

        $result = $endpoint->call([
            ...self::MINIMAL_INPUT,
            'username' => 'inexistent',
            'email' => 'child1@gmail.com',
        ]);

        $this->assertSame([
            "INFO Valid user request",
            "INFO New sign-up (using password): fakeFirstName fakeLastName (inexistent@) <child1@gmail.com>",
            "INFO Valid user response",
        ], $this->getLogs());
        $this->assertSame([
            'status' => 'OK',
        ], $result);
        $this->assertSame([
            'auth' => '',
            'root' => null,
            'user' => 'inexistent',
            'user_id' => '5',
            'auth_user' => 'inexistent',
            'auth_user_id' => '5',
        ], $session->session_storage);
        $entity_manager = WithUtilsCache::get('entityManager');
        $this->assertSame([
            [
                'ip_address' => '1.2.3.4',
                'action' => 'AUTHENTICATED_PASSWORD',
                'timestamp' => null,
                'username' => 'inexistent',
            ],
        ], $entity_manager->getRepository(AuthRequest::class)->auth_requests);
    }

    public function testSignUpWithPasswordEndpointWithValidDataForExistingEmailWithPassword(): void {
        $endpoint = new SignUpWithPasswordEndpoint();
        $endpoint->runtimeSetup();
        $session = new MemorySession();
        $endpoint->setSession($session);
        $endpoint->setServer(['REMOTE_ADDR' => '1.2.3.4']);

        try {
            $endpoint->call([
                ...self::MINIMAL_INPUT,
                'username' => 'inexistent',
                'email' => 'admin@gmail.com',
            ]);
            $this->fail('Exception expected.');
        } catch (HttpError $httperr) {
            $this->assertSame([
                "INFO Valid user request",
                "INFO New sign-up (using password): fakeFirstName fakeLastName (inexistent@) <admin@gmail.com>",
                "WARNING Bad user request",
            ], $this->getLogs());
            $this->assertSame('Fehlerhafte Eingabe', $httperr->getMessage());
            $this->assertSame(
                [
                    'message' => 'Fehlerhafte Eingabe',
                    'error' => [
                        'type' => 'ValidationError',
                        'validationErrors' => [
                            'email' => ['Es existiert bereits eine Person mit dieser E-Mail Adresse. Wolltest du gar kein Konto erstellen, sondern dich nur einloggen?'],
                        ],
                    ],
                ],
                $httperr->getStructuredAnswer(),
            );
        }
    }

    public function testSignUpWithPasswordEndpointErrorSending(): void {
        WithUtilsCache::get('emailUtils')->send_email_verification_email_error = new \Exception('test');
        $endpoint = new SignUpWithPasswordEndpoint();
        $endpoint->runtimeSetup();
        $session = new MemorySession();
        $endpoint->setSession($session);
        $endpoint->setServer(['REMOTE_ADDR' => '1.2.3.4']);

        $result = $endpoint->call(self::MINIMAL_INPUT);

        $this->assertSame([
            "INFO Valid user request",
            "INFO New sign-up (using password): fakeFirstName fakeLastName (minimal-user@) <minimal-user@staging.olzimmerberg.ch>",
            "ERROR Error sending fake verification email",
            "INFO Valid user response",
        ], $this->getLogs());
        $this->assertSame([
            'status' => 'OK_NO_EMAIL_VERIFICATION',
        ], $result);
        $this->assertSame([
            'auth' => '',
            'root' => null,
            'user' => 'minimal-user',
            'user_id' => strval(Fake\FakeEntityManager::AUTO_INCREMENT_ID),
            'auth_user' => 'minimal-user',
            'auth_user_id' => strval(Fake\FakeEntityManager::AUTO_INCREMENT_ID),
        ], $session->session_storage);
        $entity_manager = WithUtilsCache::get('entityManager');
        $this->assertSame([
            [
                'ip_address' => '1.2.3.4',
                'action' => 'AUTHENTICATED_PASSWORD',
                'timestamp' => null,
                'username' => 'minimal-user',
            ],
        ], $entity_manager->getRepository(AuthRequest::class)->auth_requests);
        $this->assertCount(1, $entity_manager->persisted);
        $user = $entity_manager->persisted[0];
        $this->assertSame(Fake\FakeEntityManager::AUTO_INCREMENT_ID, $user->getId());
        $this->assertSame('minimal-user', $user->getUsername());
        $this->assertNull($user->getOldUsername());
        $this->assertSame('minimal-user@staging.olzimmerberg.ch', $user->getEmail());
        $this->assertFalse($user->isEmailVerified());
        $this->assertNull($user->getEmailVerificationToken());
        $this->assertFalse($user->hasPermission('verified_email'));
        $this->assertSame('fakeFirstName', $user->getFirstName());
        $this->assertSame('fakeLastName', $user->getLastName());
        $this->assertNull($user->getPhone());
        $this->assertNull($user->getGender());
        $this->assertNull($user->getBirthdate());
        $this->assertNull($user->getStreet());
        $this->assertNull($user->getPostalCode());
        $this->assertNull($user->getCity());
        $this->assertNull($user->getRegion());
        $this->assertNull($user->getCountryCode());
        $this->assertSame(
            '2020-03-13 19:30:00',
            $user->getLastModifiedAt()->format('Y-m-d H:i:s')
        );
        $this->assertSame($entity_manager->persisted, $entity_manager->flushed_persisted);
    }
}
