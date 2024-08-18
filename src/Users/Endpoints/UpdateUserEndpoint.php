<?php

namespace Olz\Users\Endpoints;

use Olz\Api\OlzUpdateEntityEndpoint;
use Olz\Entity\Roles\Role;
use Olz\Entity\User;
use PhpTypeScriptApi\Fields\ValidationError;
use PhpTypeScriptApi\HttpError;

class UpdateUserEndpoint extends OlzUpdateEntityEndpoint {
    use UserEndpointTrait;

    public static function getIdent(): string {
        return 'UpdateUserEndpoint';
    }

    protected function handle(mixed $input): mixed {
        $user_repo = $this->entityManager()->getRepository(User::class);
        $role_repo = $this->entityManager()->getRepository(Role::class);
        $entity = $this->getEntityById($input['id']);

        $current_user = $this->authUtils()->getCurrentUser();
        $is_me = (
            $current_user
            && $entity->getUsername() === $current_user->getUsername()
            && $entity->getId() === $current_user->getId()
        );
        $can_update = $this->entityUtils()->canUpdateOlzEntity($entity, null, 'users');
        if (!$is_me && !$can_update) {
            throw new HttpError(403, "Kein Zugriff!");
        }

        // Username validation
        $old_username = $entity->getUsername();
        $new_username = $input['data']['username'];
        $is_username_updated = $new_username !== $old_username;
        if (!$this->authUtils()->isUsernameAllowed($new_username)) {
            throw new ValidationError(['username' => ["Der Benutzername darf nur Buchstaben, Zahlen, und die Zeichen -_. enthalten."]]);
        }
        if ($is_username_updated) {
            $same_username_user = $user_repo->findOneBy(['username' => $new_username]);
            $same_old_username_user = $user_repo->findOneBy(['old_username' => $new_username]);
            $same_username_role = $role_repo->findOneBy(['username' => $new_username]);
            $same_old_username_role = $role_repo->findOneBy(['old_username' => $new_username]);
            $is_existing_username = (bool) (
                $same_username_user || $same_old_username_user
                || $same_username_role || $same_old_username_role
            );
            if ($is_existing_username) {
                throw new ValidationError(['username' => ["Dieser Benutzername ist bereits vergeben."]]);
            }
        }

        // Email validation
        $new_email = $input['data']['email'];
        $is_email_updated = $new_email !== $entity->getEmail();
        if (preg_match('/@olzimmerberg\.ch$/i', $new_email)) {
            throw new ValidationError(['email' => ["Bitte keine @olzimmerberg.ch E-Mail verwenden."]]);
        }
        if ($is_email_updated) {
            $same_email_user = $user_repo->findOneBy(['email' => $new_email]);
            if ($same_email_user) {
                throw new ValidationError(['email' => ["Es existiert bereits eine Person mit dieser E-Mail Adresse."]]);
            }
        }

        // TODO Do this more elegantly?
        $old_data = $this->getEntityData($entity);
        $this->log()->notice('OLD:', [$old_data]);

        $this->entityUtils()->updateOlzEntity($entity, $input['meta'] ?? []);
        if ($is_username_updated) {
            $entity->setOldUsername($entity->getUsername());
        }
        $this->updateEntityWithData($entity, $input['data']);
        if ($is_email_updated) {
            $entity->setEmailIsVerified(false);
            $entity->setEmailVerificationToken(null);
            $entity->removePermission('verified_email');
        }

        // TODO Do this more elegantly?
        $new_data = $this->getEntityData($entity);
        $this->log()->notice('NEW:', [$new_data]);

        $this->entityManager()->persist($entity);
        $this->entityManager()->flush();
        $this->persistUploads($entity, $input['data']);

        // TODO: Store avatar ID in DB
        $user_id = $entity->getId();
        $data_path = $this->envUtils()->getDataPath();
        $avatar_id = $input['data']['avatarId'];
        $source_path = "{$data_path}temp/{$avatar_id}";
        $destination_path = "{$data_path}img/users/{$user_id}.jpg";
        if ($avatar_id === '-') {
            $this->unlink($destination_path);
        } elseif ($avatar_id) {
            $this->rename($source_path, $destination_path);
        }

        if ($is_username_updated && $this->session()->get('user') === $old_username) {
            $this->session()->set('user', $new_username);
        }

        if ($is_email_updated) {
            $this->emailUtils()->setLogger($this->log());
            try {
                $this->emailUtils()->sendEmailVerificationEmail($entity);
            } catch (\Throwable $th) {
                return ['status' => 'OK_NO_EMAIL_VERIFICATION'];
            }
            $this->entityManager()->flush();
        }

        return [
            'status' => 'OK',
            'id' => $entity->getId(),
        ];
    }

    // @codeCoverageIgnoreStart
    // Reason: Mocked in tests.

    protected function unlink(string $path): void {
        unlink($path);
    }

    protected function rename(string $source_path, string $destination_path): void {
        rename($source_path, $destination_path);
    }

    // @codeCoverageIgnoreEnd
}
