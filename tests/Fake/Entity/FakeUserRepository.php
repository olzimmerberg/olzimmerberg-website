<?php

declare(strict_types=1);

namespace Olz\Tests\Fake\Entity;

use Olz\Entity\User;
use Olz\Tests\Fake\Entity\Common\FakeOlzRepository;

/**
 * @extends FakeOlzRepository<User>
 */
class FakeUserRepository extends FakeOlzRepository {
    public string $olzEntityClass = User::class;
    public string $fakeOlzEntityClass = FakeUser::class;

    public ?User $userToBeFound = null;
    public mixed $userToBeFoundForQuery = null;
    public ?User $fakeProcessEmailCommandUser = null;

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array {
        if ($criteria == ['parent_user' => 2]) {
            return [
                FakeUser::vorstandUser(),
                FakeUser::defaultUser(),
            ];
        }
        $json_criteria = json_encode($criteria);
        throw new \Exception("criteria no mocked: {$json_criteria}");
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?object {
        if ($this->userToBeFound !== null) {
            return $this->userToBeFound;
        }
        if ($this->userToBeFoundForQuery !== null) {
            $fn = $this->userToBeFoundForQuery;
            return $fn($criteria);
        }
        if (
            $criteria === ['username' => 'minimal-user']
            || $criteria === ['old_username' => 'minimal-user-old']
            || $criteria === ['email' => 'minimal-user@staging.olzimmerberg.ch']
        ) {
            return FakeUser::minimal();
        }
        if (
            $criteria === ['username' => 'empty-user']
            || $criteria === ['old_username' => 'empty-user-old']
            || $criteria === ['email' => 'empty-user@staging.olzimmerberg.ch']
        ) {
            return FakeUser::empty();
        }
        if (
            $criteria === ['username' => 'maximal-user']
            || $criteria === ['old_username' => 'maximal-user-old']
            || $criteria === ['email' => 'maximal-user@staging.olzimmerberg.ch']
        ) {
            return FakeUser::maximal();
        }
        if ($criteria === ['username' => 'user'] || $criteria === ['id' => 1]) {
            return FakeUser::defaultUser();
        }
        if (
            $criteria === ['username' => 'admin']
            || $criteria === ['old_username' => 'admin-old']
            || $criteria === ['email' => 'admin@gmail.com']
            || $criteria === ['id' => 2]
        ) {
            return FakeUser::adminUser();
        }
        if (
            $criteria === ['username' => 'vorstand']
            || $criteria === ['email' => 'vorstand@staging.olzimmerberg.ch']
            || $criteria === ['id' => 3]
        ) {
            return FakeUser::vorstandUser();
        }
        if ($criteria === ['username' => 'parent'] || $criteria === ['id' => 4]) {
            return FakeUser::parentUser();
        }
        if (
            $criteria === ['username' => 'child1']
            || $criteria === ['email' => 'child1@gmail.com']
            || $criteria === ['id' => 5]
        ) {
            return FakeUser::child1User();
        }
        if ($criteria === ['username' => 'child2'] || $criteria === ['id' => 6]) {
            return FakeUser::child2User();
        }
        if ($criteria === ['username' => 'no']) {
            return FakeUser::noAccessUser();
        }
        if ($criteria === ['username' => 'specific']) {
            return FakeUser::specificAccessUser();
        }
        if (
            $criteria === ['username' => 'inexistent']
            || $criteria === ['old_username' => 'inexistent']
            || $criteria === ['email' => 'inexistent']
            || $criteria === ['email' => 'inexistent@staging.olzimmerberg.ch']
        ) {
            return null;
        }
        // Wrong versions of minimal-user
        if (
            $criteria === ['old_username' => 'minimal-user']
        ) {
            return null;
        }
        // Wrong versions of admin
        if (
            $criteria === ['username' => 'admin@olzimmerberg.ch']
            || $criteria === ['old_username' => 'admin@olzimmerberg.ch']
            || $criteria === ['email' => 'admin@olzimmerberg.ch']
            || $criteria === ['username' => 'admin@gmail.com']
            || $criteria === ['username' => 'admin-old@olzimmerberg.ch']
            || $criteria === ['old_username' => 'admin-old@olzimmerberg.ch']
            || $criteria === ['email' => 'admin-old@olzimmerberg.ch']
            || $criteria === ['username' => 'admin-old']
            || $criteria === ['email' => 'admin-old']
        ) {
            return null;
        }
        return parent::findOneBy($criteria);
    }

    public function findUserFuzzilyByUsername(string $username): ?User {
        if ($username === 'someone') {
            $fake_process_email_command_user = FakeUser::defaultUser(true);
            $fake_process_email_command_user->setId(1);
            $fake_process_email_command_user->setUsername('someone');
            $fake_process_email_command_user->setFirstName('First');
            $fake_process_email_command_user->setLastName('User');
            $fake_process_email_command_user->setEmail('someone@gmail.com');
            $this->fakeProcessEmailCommandUser = $fake_process_email_command_user;
            return $fake_process_email_command_user;
        }
        if ($username === 'empty-email') {
            $user = FakeUser::defaultUser(true);
            $user->setId(1);
            $user->setUsername('empty-email');
            $user->setFirstName('Empty');
            $user->setLastName('Email');
            $user->setEmail('');
            return $user;
        }
        if ($username === 'no-permission') {
            $user = FakeUser::defaultUser(true);
            $user->setUsername('no-permission');
            return $user;
        }
        return null;
    }

    public function findUserFuzzilyByOldUsername(string $old_username): ?User {
        if ($old_username === 'someone-old') {
            $fake_process_email_command_user = FakeUser::defaultUser(true);
            $fake_process_email_command_user->setId(2);
            $fake_process_email_command_user->setUsername('someone');
            $fake_process_email_command_user->setOldUsername('someone-old');
            $fake_process_email_command_user->setFirstName('Old');
            $fake_process_email_command_user->setLastName('User');
            $fake_process_email_command_user->setEmail('someone-old@gmail.com');
            $this->fakeProcessEmailCommandUser = $fake_process_email_command_user;
            return $fake_process_email_command_user;
        }
        return null;
    }

    /** @return array<User> */
    public function getUsersWithLogin(): array {
        return [
            FakeUser::adminUser(),
            FakeUser::vorstandUser(),
        ];
    }
}
