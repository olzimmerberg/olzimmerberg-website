<?php

declare(strict_types=1);

namespace Olz\Tests\UnitTests\Utils;

use Olz\Entity\OlzEntity;
use Olz\Entity\Role;
use Olz\Entity\User;
use Olz\Tests\Fake\FakeAuthUtils;
use Olz\Tests\Fake\FakeEntityManager;
use Olz\Tests\Fake\FakeUsers;
use Olz\Tests\UnitTests\Common\UnitTestCase;
use Olz\Utils\EntityUtils;
use Olz\Utils\FixedDateUtils;

/**
 * @internal
 *
 * @covers \Olz\Utils\EntityUtils
 */
final class EntityUtilsTest extends UnitTestCase {
    public function testCreateOlzEntity(): void {
        $auth_utils = new FakeAuthUtils();
        $date_utils = new FixedDateUtils('2020-03-13 19:30:00');
        $entity_manager = new FakeEntityManager();
        $entity_utils = new EntityUtils();
        $entity_utils->setAuthUtils($auth_utils);
        $entity_utils->setDateUtils($date_utils);
        $entity_utils->setEntityManager($entity_manager);
        $entity = new OlzEntity();

        $entity_utils->createOlzEntity(
            $entity, ['onOff' => 1, 'ownerUserId' => 1, 'ownerRoleId' => 2]);

        $role_repo = $entity_manager->repositories[Role::class];
        $this->assertSame(1, $entity->getOnOff());
        $this->assertSame(FakeUsers::defaultUser(), $entity->getOwnerUser());
        $this->assertSame($role_repo->admin_role, $entity->getOwnerRole());
        $this->assertSame(
            '2020-03-13 19:30:00',
            $entity->getCreatedAt()->format('Y-m-d H:i:s')
        );
        $this->assertSame(FakeUsers::adminUser(), $entity->getCreatedByUser());
        $this->assertSame(
            '2020-03-13 19:30:00',
            $entity->getLastModifiedAt()->format('Y-m-d H:i:s')
        );
        $this->assertSame(FakeUsers::adminUser(), $entity->getLastModifiedByUser());
    }

    public function testUpdateOlzEntity(): void {
        $auth_utils = new FakeAuthUtils();
        $date_utils = new FixedDateUtils('2020-03-13 19:30:00');
        $entity_manager = new FakeEntityManager();
        $entity_utils = new EntityUtils();
        $entity_utils->setAuthUtils($auth_utils);
        $entity_utils->setDateUtils($date_utils);
        $entity_utils->setEntityManager($entity_manager);
        $then_datetime = new \DateTime('2019-01-01 19:30:00');
        $entity = new OlzEntity();
        $entity->setOnOff(1);
        $entity->setOwnerUser(FakeUsers::vorstandUser());
        $entity->setOwnerRole(null);
        $entity->setCreatedAt($then_datetime);
        $entity->setCreatedByUser(FakeUsers::vorstandUser());
        $entity->setLastModifiedAt($then_datetime);
        $entity->setLastModifiedByUser(FakeUsers::vorstandUser());

        $entity_utils->updateOlzEntity(
            $entity, ['onOff' => 1, 'ownerUserId' => 1, 'ownerRoleId' => 2]);

        $user_repo = $entity_manager->repositories[User::class];
        $role_repo = $entity_manager->repositories[Role::class];
        $this->assertSame(1, $entity->getOnOff());
        $this->assertSame($user_repo->default_user, $entity->getOwnerUser());
        $this->assertSame($role_repo->admin_role, $entity->getOwnerRole());
        $this->assertSame($then_datetime, $entity->getCreatedAt());
        $this->assertSame(FakeUsers::vorstandUser(), $entity->getCreatedByUser());
        $this->assertSame(
            '2020-03-13 19:30:00',
            $entity->getLastModifiedAt()->format('Y-m-d H:i:s')
        );
        $this->assertSame(FakeUsers::adminUser(), $entity->getLastModifiedByUser());
    }

    public function testCanUpdateOlzEntityAllPermissions(): void {
        $auth_utils = new FakeAuthUtils();
        $auth_utils->has_permission_by_query = ['all' => true];
        $entity_utils = new EntityUtils();
        $entity_utils->setAuthUtils($auth_utils);
        $entity = new OlzEntity();

        $result = $entity_utils->canUpdateOlzEntity(
            $entity, []);

        $this->assertSame(true, $result);
    }

    public function testCanUpdateOlzEntityIsOwner(): void {
        $auth_utils = new FakeAuthUtils();
        $auth_utils->has_permission_by_query = ['all' => false];
        $entity_utils = new EntityUtils();
        $entity_utils->setAuthUtils($auth_utils);
        $entity = new OlzEntity();
        $entity->setOwnerUser(FakeUsers::adminUser());

        $result = $entity_utils->canUpdateOlzEntity(
            $entity, []);

        $this->assertSame(true, $result);
    }

    public function testCanUpdateOlzEntityIsCreatedBy(): void {
        $auth_utils = new FakeAuthUtils();
        $auth_utils->has_permission_by_query = ['all' => false];
        $entity_utils = new EntityUtils();
        $entity_utils->setAuthUtils($auth_utils);
        $entity = new OlzEntity();
        $entity->setCreatedByUser(FakeUsers::adminUser());

        $result = $entity_utils->canUpdateOlzEntity(
            $entity, []);

        $this->assertSame(true, $result);
    }

    public function testCanUpdateOlzEntityNoEntityAccess(): void {
        $auth_utils = new FakeAuthUtils();
        $auth_utils->has_permission_by_query = ['all' => false];
        $entity_utils = new EntityUtils();
        $entity_utils->setAuthUtils($auth_utils);
        $entity = new OlzEntity();

        $result = $entity_utils->canUpdateOlzEntity(
            $entity, []);

        $this->assertSame(false, $result);
    }
}
