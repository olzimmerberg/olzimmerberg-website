<?php

declare(strict_types=1);

namespace Olz\Tests\Fake\Entity\Roles;

use Olz\Entity\Roles\Role;
use Olz\Repository\Roles\PredefinedRole;
use Olz\Tests\Fake\Entity\Common\FakeOlzRepository;

/**
 * @extends FakeOlzRepository<Role>
 */
class FakeRoleRepository extends FakeOlzRepository {
    public string $olzEntityClass = Role::class;
    public string $fakeOlzEntityClass = FakeRole::class;

    public function getPredefinedRole(PredefinedRole $predefined_role): ?Role {
        $role = FakeRole::defaultRole(fresh: true);
        $role->setUsername("{$predefined_role->value}.fake");
        return $role;
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?object {
        if ($criteria === ['username' => 'role'] || $criteria === ['id' => 1]) {
            return FakeRole::defaultRole();
        }
        if ($criteria === ['username' => 'admin_role'] || $criteria === ['id' => 2]) {
            return FakeRole::adminRole();
        }
        if ($criteria === ['username' => 'vorstand_role'] || $criteria === ['id' => 3]) {
            return FakeRole::vorstandRole();
        }
        if (preg_match('/^[3]+$/', strval($criteria['id'] ?? ''))) {
            return FakeRole::subVorstandRole(false, strlen(strval($criteria['id'] ?? '')) - 1);
        }
        if (
            $criteria === ['username' => 'inexistent']
            || $criteria === ['old_username' => 'inexistent']
            || $criteria === ['username' => 'test']
            || $criteria === ['old_username' => 'test']
            || $criteria === ['username' => 'admin'] // the user, not the role
            || $criteria === ['username' => 'admin-user'] // the user, not the role
            || $criteria === ['username' => 'vorstand'] // the user, not the role
            || $criteria === ['username' => 'vorstand-user'] // the user, not the role
        ) {
            return null;
        }
        // if ($criteria === ['username' => 'specific']) {
        //     $this->specific_role = FakeRole::defaultRole(true);
        //     $this->specific_role->setPermissions('test');
        //     return $this->specific_role;
        // }
        // if ($criteria === ['username' => 'no']) {
        //     $this->no_access_role = FakeRole::defaultRole(true);
        //     $this->no_access_role->setPermissions('');
        //     return $this->no_access_role;
        // }
        return parent::findOneBy($criteria);
    }

    public function findRoleFuzzilyByUsername(string $username): ?Role {
        if ($username === 'somerole') {
            return FakeRole::someRole();
        }
        if ($username === 'no-role-permission') {
            $role = FakeRole::defaultRole(true);
            $role->setUsername('no-role-permission');
            return $role;
        }
        return null;
    }

    public function findRoleFuzzilyByOldUsername(string $old_username): ?Role {
        if ($old_username === 'somerole-old') {
            return FakeRole::someRole();
        }
        return null;
    }
}
