<?php

declare(strict_types=1);

namespace Olz\Tests\UnitTests\Users\Endpoints;

use Olz\Entity\AccessToken;
use Olz\Entity\News\NewsEntry;
use Olz\Entity\NotificationSubscription;
use Olz\Entity\StravaLink;
use Olz\Entity\TelegramLink;
use Olz\Tests\Fake\Entity\Common\FakeOlzRepository;
use Olz\Tests\Fake\Entity\FakeUser;
use Olz\Tests\UnitTests\Common\UnitTestCase;
use Olz\Users\Endpoints\DeleteUserEndpoint;
use Olz\Utils\MemorySession;
use Olz\Utils\WithUtilsCache;
use PhpTypeScriptApi\HttpError;

/**
 * @extends FakeOlzRepository<NewsEntry>
 */
class FakeDeleteUserEndpointNewsEntryRepository extends FakeOlzRepository {
    public bool $has_news = true;

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array {
        return [new NewsEntry()];
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?object {
        return $this->has_news ? new NewsEntry() : null;
    }
}

/**
 * @extends FakeOlzRepository<NotificationSubscription>
 */
class FakeDeleteUserEndpointNotificationSubscriptionRepository extends FakeOlzRepository {
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array {
        return [new NotificationSubscription()];
    }
}

/**
 * @extends FakeOlzRepository<TelegramLink>
 */
class FakeDeleteUserEndpointTelegramLinkRepository extends FakeOlzRepository {
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array {
        return [new TelegramLink()];
    }
}

/**
 * @extends FakeOlzRepository<StravaLink>
 */
class FakeDeleteUserEndpointStravaLinkRepository extends FakeOlzRepository {
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array {
        return [new StravaLink()];
    }
}

/**
 * @extends FakeOlzRepository<AccessToken>
 */
class FakeDeleteUserEndpointAccessTokenRepository extends FakeOlzRepository {
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array {
        return [new AccessToken()];
    }
}

/**
 * @internal
 *
 * @coversNothing
 */
class DeleteUserEndpointForTest extends DeleteUserEndpoint {
    /** @var array<string> */
    public array $is_file_calls = [];
    /** @var array<string> */
    public array $unlink_calls = [];
    /** @var array<array{0: string, 1: string}> */
    public array $rename_calls = [];

    protected function isFile(string $path): bool {
        $this->is_file_calls[] = $path;
        return true;
    }

    protected function unlink(string $path): void {
        $this->unlink_calls[] = $path;
    }

    protected function rename(string $source_path, string $destination_path): void {
        $this->rename_calls[] = [$source_path, $destination_path];
    }
}

/**
 * @internal
 *
 * @covers \Olz\Users\Endpoints\DeleteUserEndpoint
 */
final class DeleteUserEndpointTest extends UnitTestCase {
    public function testDeleteUserEndpointIdent(): void {
        $endpoint = new DeleteUserEndpoint();
        $this->assertSame('DeleteUserEndpoint', $endpoint->getIdent());
    }

    public function testDeleteUserEndpointWrongUsername(): void {
        WithUtilsCache::get('authUtils')->current_user = FakeUser::adminUser();
        WithUtilsCache::get('entityUtils')->can_update_olz_entity = false;
        $endpoint = new DeleteUserEndpointForTest();
        $endpoint->runtimeSetup();
        $session = new MemorySession();
        $session->session_storage = [
            'auth' => 'ftp',
            'root' => 'karten',
            'user' => 'wrong_user',
        ];
        $endpoint->setSession($session);

        try {
            $endpoint->call(['id' => 1]);
            $this->fail('Exception expected.');
        } catch (HttpError $httperr) {
            $this->assertSame([
                "INFO Valid user request",
                "WARNING HTTP error 403",
            ], $this->getLogs());
            $this->assertSame('Kein Zugriff!', $httperr->getMessage());
            $this->assertSame([
                'auth' => 'ftp',
                'root' => 'karten',
                'user' => 'wrong_user',
            ], $session->session_storage);
            $this->assertSame([], $endpoint->unlink_calls);
            $this->assertSame([], $endpoint->rename_calls);
        }
    }

    public function testDeleteUserEndpointNoAccess(): void {
        WithUtilsCache::get('authUtils')->has_permission_by_query = ['users' => false];
        WithUtilsCache::get('entityUtils')->can_update_olz_entity = false;
        $endpoint = new DeleteUserEndpoint();
        $endpoint->runtimeSetup();

        try {
            $endpoint->call([
                'id' => FakeUser::vorstandUser()->getId(),
            ]);
            $this->fail('Error expected');
        } catch (HttpError $err) {
            $this->assertSame([
                "INFO Valid user request",
                "WARNING HTTP error 403",
            ], $this->getLogs());
            $this->assertSame([
                [FakeUser::vorstandUser(), 'vorstand', 'vorstand', null, null, 'users'],
            ], WithUtilsCache::get('entityUtils')->can_update_olz_entity_calls);
            $this->assertSame(403, $err->getCode());
        }
    }

    public function testDeleteUserEndpointInexistent(): void {
        WithUtilsCache::get('authUtils')->has_permission_by_query = ['users' => true];
        WithUtilsCache::get('entityUtils')->can_update_olz_entity = true;
        $endpoint = new DeleteUserEndpoint();
        $endpoint->runtimeSetup();

        try {
            $endpoint->call([
                'id' => FakeOlzRepository::NULL_ID,
            ]);
            $this->fail('Error expected');
        } catch (HttpError $err) {
            $this->assertSame([
                "INFO Valid user request",
                "WARNING HTTP error 404",
            ], $this->getLogs());
            $this->assertSame(
                [],
                WithUtilsCache::get('entityUtils')->can_update_olz_entity_calls,
            );
            $this->assertSame(404, $err->getCode());
            $entity_manager = WithUtilsCache::get('entityManager');
            $this->assertCount(0, $entity_manager->removed);
            $this->assertCount(0, $entity_manager->flushed_removed);
        }
    }

    public function testDeleteUserEndpointCannotDelete(): void {
        WithUtilsCache::get('authUtils')->current_user = FakeUser::adminUser();
        WithUtilsCache::get('entityUtils')->can_update_olz_entity = true;
        $entity_manager = WithUtilsCache::get('entityManager');
        $news_repo = new FakeDeleteUserEndpointNewsEntryRepository($entity_manager);
        $news_repo->has_news = true;
        $entity_manager->repositories[NewsEntry::class] = $news_repo;
        $entity_manager->repositories[NotificationSubscription::class] = new FakeDeleteUserEndpointNotificationSubscriptionRepository($entity_manager);
        $entity_manager->repositories[TelegramLink::class] = new FakeDeleteUserEndpointTelegramLinkRepository($entity_manager);
        $entity_manager->repositories[StravaLink::class] = new FakeDeleteUserEndpointStravaLinkRepository($entity_manager);
        $entity_manager->repositories[AccessToken::class] = new FakeDeleteUserEndpointAccessTokenRepository($entity_manager);
        WithUtilsCache::get('envUtils')->fake_data_path = 'fake-data-path/';
        $endpoint = new DeleteUserEndpointForTest();
        $endpoint->runtimeSetup();
        $session = new MemorySession();
        $session->session_storage = [
            'auth' => 'ftp',
            'root' => 'karten',
            'user' => 'admin',
        ];
        $endpoint->setSession($session);
        $default_user = FakeUser::defaultUser();

        $result = $endpoint->call(['id' => $default_user->getId()]); // another user

        $this->assertSame([
            'INFO Valid user request',
            'INFO Valid user response',
        ], $this->getLogs());
        $this->assertSame(['status' => 'OK'], $result);
        $this->assertSame(1, $default_user->getId());
        $this->assertSame('Default', $default_user->getFirstName());
        $this->assertSame('User', $default_user->getLastName());
        $this->assertSame('default', $default_user->getUsername());
        $this->assertSame('', $default_user->getEmail());
        $this->assertSame('', $default_user->getPasswordHash());
        $this->assertSame('', $default_user->getPhone());
        $this->assertNull($default_user->getGender());
        $this->assertNull($default_user->getBirthdate());
        $this->assertNull($default_user->getStreet());
        $this->assertNull($default_user->getPostalCode());
        $this->assertNull($default_user->getCity());
        $this->assertNull($default_user->getRegion());
        $this->assertNull($default_user->getCountryCode());
        $this->assertSame('', $default_user->getPermissions());
        $this->assertNull($default_user->getRoot());
        $this->assertSame(
            '2020-03-13 19:30:00',
            $default_user->getLastModifiedAt()->format('Y-m-d H:i:s')
        );
        $this->assertCount(5, $entity_manager->removed);
        $this->assertTrue($entity_manager->removed[0] instanceof NewsEntry);
        $this->assertTrue($entity_manager->removed[1] instanceof NotificationSubscription);
        $this->assertTrue($entity_manager->removed[2] instanceof TelegramLink);
        $this->assertTrue($entity_manager->removed[3] instanceof StravaLink);
        $this->assertTrue($entity_manager->removed[4] instanceof AccessToken);
        $this->assertSame($entity_manager->removed, $entity_manager->flushed_removed);
        // We deleted **another** user, so the session should still be intact!
        $this->assertSame([
            'auth' => 'ftp',
            'root' => 'karten',
            'user' => 'admin',
        ], $session->session_storage);
        $this->assertSame([
            'fake-data-path/img/users/1.jpg',
        ], $endpoint->is_file_calls);
        $this->assertSame([
            'fake-data-path/img/users/1.jpg',
        ], $endpoint->unlink_calls);
        $this->assertSame([], $endpoint->rename_calls);
    }

    public function testDeleteUserEndpointCanDelete(): void {
        $default_user = FakeUser::defaultUser();
        WithUtilsCache::get('authUtils')->current_user = $default_user;
        WithUtilsCache::get('entityUtils')->can_update_olz_entity = true;
        $entity_manager = WithUtilsCache::get('entityManager');
        $news_repo = new FakeDeleteUserEndpointNewsEntryRepository($entity_manager);
        $news_repo->has_news = false;
        $entity_manager->repositories[NewsEntry::class] = $news_repo;
        $entity_manager->repositories[NotificationSubscription::class] = new FakeDeleteUserEndpointNotificationSubscriptionRepository($entity_manager);
        $entity_manager->repositories[TelegramLink::class] = new FakeDeleteUserEndpointTelegramLinkRepository($entity_manager);
        $entity_manager->repositories[StravaLink::class] = new FakeDeleteUserEndpointStravaLinkRepository($entity_manager);
        $entity_manager->repositories[AccessToken::class] = new FakeDeleteUserEndpointAccessTokenRepository($entity_manager);
        WithUtilsCache::get('envUtils')->fake_data_path = 'fake-data-path/';
        $endpoint = new DeleteUserEndpointForTest();
        $endpoint->runtimeSetup();
        $session = new MemorySession();
        $session->session_storage = [
            'auth' => 'ftp',
            'root' => 'karten',
            'user' => 'default',
        ];
        $endpoint->setSession($session);

        $result = $endpoint->call(['id' => $default_user->getId()]);

        $this->assertSame([
            'INFO Valid user request',
            // 'WARNING Removing user default (User ID: 1).',
            'INFO Valid user response',
        ], $this->getLogs());
        $this->assertSame(['status' => 'OK'], $result);
        $this->assertSame($entity_manager->removed, $entity_manager->flushed_removed);
        $this->assertCount(5, $entity_manager->removed);
        $this->assertTrue($entity_manager->removed[0] instanceof NewsEntry);
        $this->assertTrue($entity_manager->removed[1] instanceof NotificationSubscription);
        $this->assertTrue($entity_manager->removed[2] instanceof TelegramLink);
        $this->assertTrue($entity_manager->removed[3] instanceof StravaLink);
        $this->assertTrue($entity_manager->removed[4] instanceof AccessToken);
        $this->assertSame(0, $default_user->getOnOff());
        $this->assertSame([], $session->session_storage);
        $this->assertSame([
            'fake-data-path/img/users/1.jpg',
        ], $endpoint->is_file_calls);
        $this->assertSame([
            'fake-data-path/img/users/1.jpg',
        ], $endpoint->unlink_calls);
        $this->assertSame([], $endpoint->rename_calls);
    }
}
