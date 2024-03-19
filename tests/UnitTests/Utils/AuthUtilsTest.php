<?php

declare(strict_types=1);

namespace Olz\Tests\UnitTests\Utils;

use Olz\Entity\AuthRequest;
use Olz\Entity\Roles\Role;
use Olz\Entity\User;
use Olz\Tests\Fake\Entity\FakeUser;
use Olz\Tests\UnitTests\Common\UnitTestCase;
use Olz\Utils\AuthUtils;
use Olz\Utils\MemorySession;
use Olz\Utils\WithUtilsCache;

/**
 * @internal
 *
 * @coversNothing
 */
class AuthUtilsForTest extends AuthUtils {
    protected function verifyPassword($password, $hash) {
        return md5($password) === $hash; // just for test
    }
}

/**
 * @internal
 *
 * @covers \Olz\Utils\AuthUtils
 */
final class AuthUtilsTest extends UnitTestCase {
    public function testAuthenticateWithCorrectCredentials(): void {
        $entity_manager = WithUtilsCache::get('entityManager');
        $session = new MemorySession();
        $session->session_storage = [
            'user' => 'inexistent',
        ];

        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setServer(['REMOTE_ADDR' => '1.2.3.4']);
        $auth_utils->setSession($session);

        // Also test that it's resolving admin-old to admin
        $result = $auth_utils->authenticate('admin-old', 'adm1n');

        $this->assertNotSame(null, FakeUser::adminUser());
        $this->assertSame(FakeUser::adminUser(), $result);
        $this->assertSame([
            'user' => 'inexistent', // for now, we don't modify the session
        ], $session->session_storage);
        $this->assertSame([
            [
                'ip_address' => '1.2.3.4',
                'action' => 'AUTHENTICATED',
                'timestamp' => null,
                'username' => 'admin-old',
            ],
        ], $entity_manager->getRepository(AuthRequest::class)->auth_requests);
        $this->assertSame([
            "INFO User login successful: admin-old",
            "INFO   Auth: all verified_email",
            "INFO   Root: karten",
        ], $this->getLogs());
    }

    public function testAuthenticateWithWrongUsername(): void {
        $session = new MemorySession();
        $session->session_storage = [
            'user' => 'inexistent',
        ];

        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setServer(['REMOTE_ADDR' => '1.2.3.4']);
        $auth_utils->setSession($session);

        try {
            $auth_utils->authenticate('wrooong', 'adm1n');
            $this->fail('Error expected');
        } catch (\Exception $exc) {
            $this->assertSame(
                'Login attempt with invalid credentials from IP: 1.2.3.4 (user: wrooong).',
                $exc->getMessage()
            );
        }

        $this->assertSame([
            'user' => 'inexistent',
        ], $session->session_storage);
        $entity_manager = WithUtilsCache::get('entityManager');
        $this->assertSame([
            [
                'ip_address' => '1.2.3.4',
                'action' => 'INVALID_CREDENTIALS',
                'timestamp' => null,
                'username' => 'wrooong',
            ],
        ], $entity_manager->getRepository(AuthRequest::class)->auth_requests);
        $this->assertSame([
            "NOTICE Login attempt with invalid credentials from IP: 1.2.3.4 (user: wrooong).",
        ], $this->getLogs());
    }

    public function testAuthenticateWithWrongPassword(): void {
        $session = new MemorySession();
        $session->session_storage = [
            'user' => 'inexistent',
        ];

        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setServer(['REMOTE_ADDR' => '1.2.3.4']);
        $auth_utils->setSession($session);

        try {
            $auth_utils->authenticate('admin', 'wrooong');
            $this->fail('Error expected');
        } catch (\Exception $exc) {
            $this->assertSame(
                'Login attempt with invalid credentials from IP: 1.2.3.4 (user: admin).',
                $exc->getMessage()
            );
        }

        $this->assertSame([
            'user' => 'inexistent',
        ], $session->session_storage);
        $entity_manager = WithUtilsCache::get('entityManager');
        $this->assertSame([
            [
                'ip_address' => '1.2.3.4',
                'action' => 'INVALID_CREDENTIALS',
                'timestamp' => null,
                'username' => 'admin',
            ],
        ], $entity_manager->getRepository(AuthRequest::class)->auth_requests);
        $this->assertSame([
            "NOTICE Login attempt with invalid credentials from IP: 1.2.3.4 (user: admin).",
        ], $this->getLogs());
    }

    public function testAuthenticateCanNotAuthenticate(): void {
        $entity_manager = WithUtilsCache::get('entityManager');
        $entity_manager->repositories[AuthRequest::class]->num_remaining_attempts = 0;
        $session = new MemorySession();
        $session->session_storage = [
            'user' => 'inexistent',
        ];

        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setServer(['REMOTE_ADDR' => '1.2.3.4']);
        $auth_utils->setSession($session);

        try {
            $auth_utils->authenticate('admin', 'adm1n');
            $this->fail('Error expected');
        } catch (\Exception $exc) {
            $this->assertSame(
                'Login attempt from blocked IP: 1.2.3.4 (user: admin).',
                $exc->getMessage()
            );
        }

        $this->assertSame([
            'user' => 'inexistent',
        ], $session->session_storage);
        $this->assertSame([
            [
                'ip_address' => '1.2.3.4',
                'action' => 'BLOCKED',
                'timestamp' => null,
                'username' => 'admin',
            ],
        ], $entity_manager->getRepository(AuthRequest::class)->auth_requests);
        $this->assertSame([
            "NOTICE Login attempt from blocked IP: 1.2.3.4 (user: admin).",
        ], $this->getLogs());
    }

    public function testValidateValidAccessToken(): void {
        $session = new MemorySession();
        $session->session_storage = [
            'user' => 'inexistent',
        ];

        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setServer(['REMOTE_ADDR' => '1.2.3.4']);
        $auth_utils->setSession($session);

        $result = $auth_utils->validateAccessToken('valid-token');

        $this->assertSame(FakeUser::adminUser(), $result);
        $this->assertSame([
            'user' => 'inexistent', // for now, we don't modify the session
        ], $session->session_storage);
        $entity_manager = WithUtilsCache::get('entityManager');
        $this->assertSame([
            [
                'ip_address' => '1.2.3.4',
                'action' => 'TOKEN_VALIDATED',
                'timestamp' => null,
                'username' => 'admin',
            ],
        ], $entity_manager->getRepository(AuthRequest::class)->auth_requests);
        $this->assertSame([
            "INFO Token validation successful: 2",
        ], $this->getLogs());
    }

    public function testValidateInvalidAccessToken(): void {
        $session = new MemorySession();
        $session->session_storage = [
            'user' => 'inexistent',
        ];

        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setServer(['REMOTE_ADDR' => '1.2.3.4']);
        $auth_utils->setSession($session);

        try {
            $auth_utils->validateAccessToken('invalid-token');
            $this->fail('Error expected');
        } catch (\Exception $exc) {
            $this->assertSame(
                'Invalid access token validation from IP: 1.2.3.4.',
                $exc->getMessage()
            );
        }

        $this->assertSame([
            'user' => 'inexistent',
        ], $session->session_storage);
        $entity_manager = WithUtilsCache::get('entityManager');
        $this->assertSame([
            [
                'ip_address' => '1.2.3.4',
                'action' => 'INVALID_TOKEN',
                'timestamp' => null,
                'username' => '',
            ],
        ], $entity_manager->getRepository(AuthRequest::class)->auth_requests);
        $this->assertSame([
            "NOTICE Invalid access token validation from IP: 1.2.3.4.",
        ], $this->getLogs());
    }

    public function testValidateExpiredAccessToken(): void {
        $session = new MemorySession();
        $session->session_storage = [
            'user' => 'inexistent',
        ];

        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setServer(['REMOTE_ADDR' => '1.2.3.4']);
        $auth_utils->setSession($session);

        try {
            $auth_utils->validateAccessToken('expired-token');
            $this->fail('Error expected');
        } catch (\Exception $exc) {
            $this->assertSame(
                'Expired access token validation from IP: 1.2.3.4.',
                $exc->getMessage()
            );
        }

        $this->assertSame([
            'user' => 'inexistent',
        ], $session->session_storage);
        $entity_manager = WithUtilsCache::get('entityManager');
        $this->assertSame([
            [
                'ip_address' => '1.2.3.4',
                'action' => 'EXPIRED_TOKEN',
                'timestamp' => null,
                'username' => '',
            ],
        ], $entity_manager->getRepository(AuthRequest::class)->auth_requests);
        $this->assertSame([
            "NOTICE Expired access token validation from IP: 1.2.3.4.",
        ], $this->getLogs());
    }

    public function testValidateAccessTokenCanNotValidate(): void {
        $entity_manager = WithUtilsCache::get('entityManager');
        $entity_manager->repositories[AuthRequest::class]->can_validate_access_token = false;
        $session = new MemorySession();
        $session->session_storage = [
            'user' => 'inexistent',
        ];

        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setServer(['REMOTE_ADDR' => '1.2.3.4']);
        $auth_utils->setSession($session);

        try {
            $auth_utils->validateAccessToken('valid-token');
            $this->fail('Error expected');
        } catch (\Exception $exc) {
            $this->assertSame(
                'Access token validation from blocked IP: 1.2.3.4.',
                $exc->getMessage()
            );
        }

        $this->assertSame([
            'user' => 'inexistent',
        ], $session->session_storage);
        $this->assertSame([
            [
                'ip_address' => '1.2.3.4',
                'action' => 'TOKEN_BLOCKED',
                'timestamp' => null,
                'username' => '',
            ],
        ], $entity_manager->getRepository(AuthRequest::class)->auth_requests);
        $this->assertSame([
            "NOTICE Access token validation from blocked IP: 1.2.3.4.",
        ], $this->getLogs());
    }

    public function testResolveUsername(): void {
        $auth_utils = new AuthUtilsForTest();

        $result = $auth_utils->resolveUsernameOrEmail('admin');
        $this->assertSame(FakeUser::adminUser(), $result);
    }

    public function testResolveOldUsername(): void {
        $auth_utils = new AuthUtilsForTest();

        $result = $auth_utils->resolveUsernameOrEmail('admin-old');
        $this->assertSame(FakeUser::adminUser(), $result);
    }

    public function testResolveEmail(): void {
        $auth_utils = new AuthUtilsForTest();

        $result = $auth_utils->resolveUsernameOrEmail('vorstand@olzimmerberg.ch');
        $this->assertSame(FakeUser::vorstandUser(), $result);
    }

    public function testResolveUsernameEmail(): void {
        $auth_utils = new AuthUtilsForTest();

        $result = $auth_utils->resolveUsernameOrEmail('admin@olzimmerberg.ch');
        $this->assertSame(FakeUser::adminUser(), $result);
    }

    public function testResolveOldUsernameEmail(): void {
        $auth_utils = new AuthUtilsForTest();

        $result = $auth_utils->resolveUsernameOrEmail('admin-old@olzimmerberg.ch');
        $this->assertSame(FakeUser::adminUser(), $result);
    }

    public function testHasPermissionNoUser(): void {
        $session = new MemorySession();
        $session->session_storage = [
            'user' => 'inexistent',
        ];
        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setSession($session);
        $this->assertSame(false, $auth_utils->hasPermission('test'));
        $this->assertSame(false, $auth_utils->hasPermission('other'));
        $this->assertSame(false, $auth_utils->hasPermission('all'));
        $this->assertSame(false, $auth_utils->hasPermission('any'));
    }

    public function testHasPermissionWithNoPermission(): void {
        $session = new MemorySession();
        $session->session_storage = [
            'user' => 'no',
        ];
        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setSession($session);
        $this->assertSame(false, $auth_utils->hasPermission('test'));
        $this->assertSame(false, $auth_utils->hasPermission('other'));
        $this->assertSame(false, $auth_utils->hasPermission('all'));
        $this->assertSame(true, $auth_utils->hasPermission('any'));
    }

    public function testHasPermissionWithSpecificPermission(): void {
        $session = new MemorySession();
        $session->session_storage = [
            'user' => 'specific',
        ];
        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setSession($session);
        $this->assertSame(true, $auth_utils->hasPermission('test'));
        $this->assertSame(false, $auth_utils->hasPermission('other'));
        $this->assertSame(false, $auth_utils->hasPermission('all'));
        $this->assertSame(true, $auth_utils->hasPermission('any'));
    }

    public function testHasPermissionWithAllPermissions(): void {
        $session = new MemorySession();
        $session->session_storage = [
            'user' => 'admin',
        ];
        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setSession($session);
        $this->assertSame(true, $auth_utils->hasPermission('test'));
        $this->assertSame(true, $auth_utils->hasPermission('other'));
        $this->assertSame(true, $auth_utils->hasPermission('all'));
        $this->assertSame(true, $auth_utils->hasPermission('any'));
    }

    public function testHasPermissionWithRolePermissions(): void {
        $session = new MemorySession();
        $session->session_storage = [
            'user' => 'vorstand',
        ];
        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setSession($session);
        $this->assertSame(true, $auth_utils->hasPermission('vorstand_user'));
        $this->assertSame(true, $auth_utils->hasPermission('vorstand_role'));
        $this->assertSame(false, $auth_utils->hasPermission('all'));
        $this->assertSame(true, $auth_utils->hasPermission('any'));
    }

    public function testHasUserPermissionNoUser(): void {
        $auth_utils = new AuthUtilsForTest();
        $this->assertSame(false, $auth_utils->hasUserPermission('test', null));
        $this->assertSame(false, $auth_utils->hasUserPermission('other', null));
        $this->assertSame(false, $auth_utils->hasUserPermission('all', null));
        $this->assertSame(false, $auth_utils->hasUserPermission('any', null));
    }

    public function testHasUserPermissionWithNoPermission(): void {
        $user = new User();
        $user->setPermissions('');
        $auth_utils = new AuthUtilsForTest();
        $this->assertSame(false, $auth_utils->hasUserPermission('test', $user));
        $this->assertSame(false, $auth_utils->hasUserPermission('other', $user));
        $this->assertSame(false, $auth_utils->hasUserPermission('all', $user));
        $this->assertSame(true, $auth_utils->hasUserPermission('any', $user));
    }

    public function testHasUserPermissionWithSpecificPermission(): void {
        $user = new User();
        $user->setPermissions(' test ');
        $auth_utils = new AuthUtilsForTest();
        $this->assertSame(true, $auth_utils->hasUserPermission('test', $user));
        $this->assertSame(false, $auth_utils->hasUserPermission('other', $user));
        $this->assertSame(false, $auth_utils->hasUserPermission('all', $user));
        $this->assertSame(true, $auth_utils->hasUserPermission('any', $user));
    }

    public function testHasUserPermissionWithAllPermissions(): void {
        $user = new User();
        $user->setPermissions(' all ');
        $auth_utils = new AuthUtilsForTest();
        $this->assertSame(true, $auth_utils->hasUserPermission('test', $user));
        $this->assertSame(true, $auth_utils->hasUserPermission('other', $user));
        $this->assertSame(true, $auth_utils->hasUserPermission('all', $user));
        $this->assertSame(true, $auth_utils->hasUserPermission('any', $user));
    }

    public function testHasUserPermissionWithRolePermissions(): void {
        $user = new User();
        $user->setPermissions('aktuell ftp vorstand_user');
        $auth_utils = new AuthUtilsForTest();
        $this->assertSame(true, $auth_utils->hasUserPermission('vorstand_user', $user));
        $this->assertSame(false, $auth_utils->hasUserPermission('vorstand_role', $user));
        $this->assertSame(false, $auth_utils->hasUserPermission('all', $user));
        $this->assertSame(true, $auth_utils->hasUserPermission('any', $user));
    }

    public function testHasRolePermissionNoRole(): void {
        $auth_utils = new AuthUtilsForTest();
        $this->assertSame(false, $auth_utils->hasRolePermission('test', null));
        $this->assertSame(false, $auth_utils->hasRolePermission('other', null));
        $this->assertSame(false, $auth_utils->hasRolePermission('all', null));
        $this->assertSame(false, $auth_utils->hasRolePermission('any', null));
    }

    public function testHasRolePermissionWithNoPermission(): void {
        $role = new Role();
        $role->setPermissions('');
        $auth_utils = new AuthUtilsForTest();
        $this->assertSame(false, $auth_utils->hasRolePermission('test', $role));
        $this->assertSame(false, $auth_utils->hasRolePermission('other', $role));
        $this->assertSame(false, $auth_utils->hasRolePermission('all', $role));
        $this->assertSame(true, $auth_utils->hasRolePermission('any', $role));
    }

    public function testHasRolePermissionWithSpecificPermission(): void {
        $role = new Role();
        $role->setPermissions('test');
        $auth_utils = new AuthUtilsForTest();
        $this->assertSame(true, $auth_utils->hasRolePermission('test', $role));
        $this->assertSame(false, $auth_utils->hasRolePermission('other', $role));
        $this->assertSame(false, $auth_utils->hasRolePermission('all', $role));
        $this->assertSame(true, $auth_utils->hasRolePermission('any', $role));
    }

    public function testHasRolePermissionWithAllPermissions(): void {
        $role = new Role();
        $role->setPermissions('all');
        $auth_utils = new AuthUtilsForTest();
        $this->assertSame(true, $auth_utils->hasRolePermission('test', $role));
        $this->assertSame(true, $auth_utils->hasRolePermission('other', $role));
        $this->assertSame(true, $auth_utils->hasRolePermission('all', $role));
        $this->assertSame(true, $auth_utils->hasRolePermission('any', $role));
    }

    public function testGetCurrentUserFromToken(): void {
        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setGetParams(['access_token' => 'valid-token']);
        $auth_utils->setServer(['REMOTE_ADDR' => '1.2.3.4']);
        $this->assertSame(FakeUser::adminUser(), $auth_utils->getCurrentUser());
    }

    public function testGetCurrentUserFromSession(): void {
        $session = new MemorySession();
        $session->session_storage = [
            'user' => 'admin',
        ];
        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setGetParams([]);
        $auth_utils->setSession($session);
        $this->assertSame(FakeUser::adminUser(), $auth_utils->getCurrentUser());
    }

    public function testGetTokenUser(): void {
        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setGetParams(['access_token' => 'valid-token']);
        $auth_utils->setServer(['REMOTE_ADDR' => '1.2.3.4']);
        $this->assertSame(FakeUser::adminUser(), $auth_utils->getTokenUser());
    }

    public function testGetTokenUserForInvalidToken(): void {
        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setGetParams(['access_token' => 'invalid-token']);
        $auth_utils->setServer(['REMOTE_ADDR' => '1.2.3.4']);
        $this->assertSame(null, $auth_utils->getTokenUser());
    }

    public function testGetSessionUser(): void {
        $session = new MemorySession();
        $session->session_storage = [
            'user' => 'vorstand',
        ];
        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setSession($session);
        $this->assertSame(FakeUser::vorstandUser(), $auth_utils->getSessionUser());
    }

    public function testGetCurrentAuthUserFromSession(): void {
        $session = new MemorySession();
        $session->session_storage = [
            'auth_user' => 'admin',
        ];
        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setGetParams([]);
        $auth_utils->setSession($session);
        $this->assertSame(FakeUser::adminUser(), $auth_utils->getCurrentAuthUser());
    }

    public function testGetSessionAuthUser(): void {
        $session = new MemorySession();
        $session->session_storage = [
            'auth_user' => 'vorstand',
        ];
        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setSession($session);
        $this->assertSame(FakeUser::vorstandUser(), $auth_utils->getSessionAuthUser());
    }

    public function testGetAuthenticatedRoles(): void {
        $session = new MemorySession();
        $session->session_storage = [
            'user' => 'admin',
        ];
        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setGetParams([]);
        $auth_utils->setSession($session);
        $this->assertSame(['admin_role'], array_map(function ($role) {
            return $role->getUsername();
        }, $auth_utils->getAuthenticatedRoles()));
    }

    public function testGetAuthenticatedRolesUnauthenticated(): void {
        $session = new MemorySession();
        $session->session_storage = [];
        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setGetParams([]);
        $auth_utils->setSession($session);
        $this->assertSame(null, $auth_utils->getAuthenticatedRoles());
    }

    public function testGetAuthenticatedRolesAdmin(): void {
        $auth_utils = new AuthUtilsForTest();
        $this->assertSame(['admin_role'], array_map(function ($role) {
            return $role->getUsername();
        }, $auth_utils->getAuthenticatedRoles(FakeUser::adminUser())));
    }

    public function testGetAuthenticatedRolesVorstand(): void {
        $auth_utils = new AuthUtilsForTest();
        $this->assertSame(['vorstand_role'], array_map(function ($role) {
            return $role->getUsername();
        }, $auth_utils->getAuthenticatedRoles(FakeUser::vorstandUser())));
    }

    public function testIsRoleIdAuthenticated(): void {
        $session = new MemorySession();
        $session->session_storage = [
            'user' => 'admin',
        ];
        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setGetParams([]);
        $auth_utils->setSession($session);
        $this->assertSame(false, $auth_utils->isRoleIdAuthenticated(1));
        $this->assertSame(true, $auth_utils->isRoleIdAuthenticated(2));
        $this->assertSame(false, $auth_utils->isRoleIdAuthenticated(3));
    }

    public function testIsRoleIdAuthenticatedUnauthenticated(): void {
        $session = new MemorySession();
        $session->session_storage = [];
        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setGetParams([]);
        $auth_utils->setSession($session);
        $this->assertSame(false, $auth_utils->isRoleIdAuthenticated(1));
        $this->assertSame(false, $auth_utils->isRoleIdAuthenticated(2));
        $this->assertSame(false, $auth_utils->isRoleIdAuthenticated(3));
    }

    public function testHasRoleEditPermissionAdmin(): void {
        $session = new MemorySession();
        $session->session_storage = [
            'user' => 'admin',
        ];
        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setGetParams([]);
        $auth_utils->setSession($session);
        $this->assertSame(true, $auth_utils->hasRoleEditPermission(1));
        $this->assertSame(true, $auth_utils->hasRoleEditPermission(2));
        $this->assertSame(true, $auth_utils->hasRoleEditPermission(3));
    }

    public function testHasRoleEditPermissionVorstand(): void {
        $session = new MemorySession();
        $session->session_storage = [
            'user' => 'vorstand',
        ];
        $auth_utils = new AuthUtilsForTest();
        $auth_utils->setGetParams([]);
        $auth_utils->setSession($session);
        $this->assertSame(false, $auth_utils->hasRoleEditPermission(1));
        $this->assertSame(false, $auth_utils->hasRoleEditPermission(2));
        $this->assertSame(true, $auth_utils->hasRoleEditPermission(3));
        $this->assertSame(true, $auth_utils->hasRoleEditPermission(33));
        $this->assertSame(true, $auth_utils->hasRoleEditPermission(333));
        $this->assertSame(true, $auth_utils->hasRoleEditPermission(3333));
        $this->assertSame(true, $auth_utils->hasRoleEditPermission(33333));
        $this->assertSame(false, $auth_utils->hasRoleEditPermission(333333));
    }

    public function testIsUsernameAllowed(): void {
        $auth_utils = new AuthUtilsForTest();
        $this->assertSame(true, $auth_utils->isUsernameAllowed('testTEST1234.-_'));
        $this->assertSame(false, $auth_utils->isUsernameAllowed('test@wtf'));
        $this->assertSame(false, $auth_utils->isUsernameAllowed('ötzi'));
        $this->assertSame(false, $auth_utils->isUsernameAllowed('\';DROP TABLE users;'));
    }

    public function testIsPasswordAllowed(): void {
        $auth_utils = new AuthUtilsForTest();
        $this->assertSame(false, $auth_utils->isPasswordAllowed('test'));
        $this->assertSame(true, $auth_utils->isPasswordAllowed('longpassword'));
        $this->assertSame(false, $auth_utils->isPasswordAllowed('1234567'));
        $this->assertSame(true, $auth_utils->isPasswordAllowed('12345678'));
    }

    public function testGetUserAvatarNoUser(): void {
        $auth_utils = new AuthUtilsForTest();
        $this->assertSame(
            ['1x' => '/_/assets/user_initials_%3F.svg'],
            $auth_utils->getUserAvatar(null)
        );
    }

    public function testGetUserAvatarHasAvatar(): void {
        $auth_utils = new AuthUtilsForTest();
        $user = FakeUser::adminUser();

        $data_path = WithUtilsCache::get('envUtils')->getDataPath();
        $user_image_path = "{$data_path}img/users/{$user->getId()}.jpg";
        mkdir(dirname($user_image_path), 0777, true);
        file_put_contents($user_image_path, '');

        $this->assertSame(
            ['1x' => "/data-href/img/users/{$user->getId()}.jpg"],
            $auth_utils->getUserAvatar($user)
        );
    }

    public function testGetUserAvatarHasHighResolutionAvatar(): void {
        $auth_utils = new AuthUtilsForTest();
        $user = FakeUser::adminUser();

        $data_path = WithUtilsCache::get('envUtils')->getDataPath();
        $user_image_path = "{$data_path}img/users/{$user->getId()}.jpg";
        $user_image_2x_path = "{$data_path}img/users/{$user->getId()}@2x.jpg";
        mkdir(dirname($user_image_path), 0777, true);
        file_put_contents($user_image_path, '');
        file_put_contents($user_image_2x_path, '');

        $this->assertSame(
            [
                '2x' => "/data-href/img/users/{$user->getId()}@2x.jpg",
                '1x' => "/data-href/img/users/{$user->getId()}.jpg",
            ],
            $auth_utils->getUserAvatar($user)
        );
    }

    public function testGetUserAvatarNoAvatar(): void {
        $auth_utils = new AuthUtilsForTest();
        $user = FakeUser::adminUser();
        $this->assertSame(
            ['1x' => '/_/assets/user_initials_AI.svg'],
            $auth_utils->getUserAvatar($user)
        );
    }

    public function testGetUserAvatarNoAvatarSpecialChars(): void {
        $auth_utils = new AuthUtilsForTest();
        $user = FakeUser::adminUser();
        $user->setFirstName("Özdemir");
        $user->setLastName(null);
        $this->assertSame(
            ['1x' => '/_/assets/user_initials_%C3%96%3F.svg'],
            $auth_utils->getUserAvatar($user)
        );
    }
}
