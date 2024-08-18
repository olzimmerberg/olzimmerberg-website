<?php

namespace Olz\Users\Endpoints;

use Olz\Api\OlzCreateEntityEndpoint;
use Olz\Entity\AuthRequest;
use Olz\Entity\User;
use PhpTypeScriptApi\Fields\FieldTypes;
use PhpTypeScriptApi\Fields\ValidationError;

class CreateUserEndpoint extends OlzCreateEntityEndpoint {
    use UserEndpointTrait;

    public static function getIdent(): string {
        return 'CreateUserEndpoint';
    }

    protected function getCustomRequestField(): ?FieldTypes\Field {
        return new FieldTypes\ObjectField([
            'field_structure' => [
                'recaptchaToken' => new FieldTypes\StringField(['allow_null' => true]),
            ],
        ]);
    }

    protected function getStatusField(): FieldTypes\Field {
        return new FieldTypes\EnumField(['allowed_values' => [
            'OK',
            'OK_NO_EMAIL_VERIFICATION',
            'DENIED',
            'ERROR',
        ]]);
    }

    protected function handle(mixed $input): mixed {
        $current_user = $this->authUtils()->getCurrentUser();
        $token = $input['custom']['recaptchaToken'];
        if (!$current_user && !$this->recaptchaUtils()->validateRecaptchaToken($token)) {
            return ['status' => 'DENIED', 'id' => null];
        }

        // TODO: The user should specify the desired parent user; to be validated here.
        $parent_user = $current_user;
        $parent_user_id = $parent_user ? $parent_user->getId() : null;

        $first_name = $input['data']['firstName'];
        $last_name = $input['data']['lastName'];
        $username = $input['data']['username'];
        $email = $input['data']['email'];
        $this->log()->info("New sign-up (using password): {$first_name} {$last_name} ({$username}@) <{$email}>");
        if (!$parent_user && !$email) {
            throw new ValidationError(['email' => ["Feld darf nicht leer sein."]]);
        }
        if (!$parent_user && !$input['data']['password']) {
            throw new ValidationError(['password' => ["Feld darf nicht leer sein."]]);
        }
        if (!$this->authUtils()->isUsernameAllowed($username)) {
            throw new ValidationError(['username' => ["Der Benutzername darf nur Buchstaben, Zahlen, und die Zeichen -_. enthalten."]]);
        }
        if (!$parent_user && !$this->authUtils()->isPasswordAllowed($input['data']['password'])) {
            throw new ValidationError(['password' => ["Das Passwort muss mindestens 8 Zeichen lang sein."]]);
        }
        if ($email && preg_match('/@olzimmerberg\.ch$/i', $email)) {
            throw new ValidationError(['email' => ["Bitte keine @olzimmerberg.ch E-Mail verwenden."]]);
        }
        $ip_address = $this->server()['REMOTE_ADDR'];
        $auth_request_repo = $this->entityManager()->getRepository(AuthRequest::class);
        $user_repo = $this->entityManager()->getRepository(User::class);

        $same_username_user = $user_repo->findOneBy(['username' => $username]);
        $same_email_user = $user_repo->findOneBy(['email' => $email]);
        // TODO: Test users with old username, roles
        if ($username && $same_username_user) {
            if ($same_username_user->getPasswordHash()) {
                throw new ValidationError(['username' => ["Es existiert bereits eine Person mit diesem Benutzernamen. Wolltest du gar kein Konto erstellen, sondern dich nur einloggen?"]]);
            }
            // If it's an existing user WITHOUT password, we just update that existing user!
            $entity = $same_username_user;
            $this->entityUtils()->updateOlzEntity($entity, ['on_off' => true]);
        } elseif ($email && $same_email_user) {
            if ($same_email_user->getPasswordHash()) {
                throw new ValidationError(['email' => ["Es existiert bereits eine Person mit dieser E-Mail Adresse. Wolltest du gar kein Konto erstellen, sondern dich nur einloggen?"]]);
            }
            // If it's an existing user WITHOUT password, we just update that existing user!
            $entity = $same_email_user;
            $this->entityUtils()->updateOlzEntity($entity, ['on_off' => true]);
        } else {
            $entity = new User();
            $this->entityUtils()->createOlzEntity($entity, ['on_off' => true]);
        }

        $entity->setOldUsername(null);
        $this->updateEntityWithData($entity, $input['data']);
        $entity->setEmailIsVerified(false);
        $entity->setEmailVerificationToken(null);

        $password_hash = $input['data']['password'] ? $this->authUtils()->hashPassword($input['data']['password']) : null;

        $entity->setPasswordHash($password_hash);
        $entity->setParentUserId($parent_user_id);
        $entity->setPermissions('');
        $entity->setRoot(null);
        $entity->setMemberType(null);
        $entity->setMemberLastPaid(null);
        $entity->setWantsPostalMail(false);
        $entity->setPostalTitle(null);
        $entity->setPostalName(null);
        $entity->setJoinedOn(null);
        $entity->setJoinedReason(null);
        $entity->setLeftOn(null);
        $entity->setLeftReason(null);
        $entity->setNotes('');
        $entity->setLastLoginAt(null);

        $this->entityManager()->persist($entity);
        $this->entityManager()->flush();
        $this->persistUploads($entity, $input['data']);

        if (!$parent_user) {
            $this->session()->resetConfigure(['timeout' => 3600]);

            $root = $entity->getRoot() !== '' ? $entity->getRoot() : './';
            $this->session()->set('auth', $entity->getPermissions());
            $this->session()->set('root', $root);
            $this->session()->set('user', $entity->getUsername());
            $this->session()->set('user_id', "{$entity->getId()}");
            $this->session()->set('auth_user', $entity->getUsername());
            $this->session()->set('auth_user_id', "{$entity->getId()}");
            $auth_request_repo->addAuthRequest($ip_address, 'AUTHENTICATED_PASSWORD', $entity->getUsername());

            $this->emailUtils()->setLogger($this->log());
            try {
                $this->emailUtils()->sendEmailVerificationEmail($entity);
            } catch (\Throwable $th) {
                return [
                    'status' => 'OK_NO_EMAIL_VERIFICATION',
                    'id' => $entity->getId(),
                ];
            }
            $this->entityManager()->flush();
        }

        return [
            'status' => 'OK',
            'id' => $entity->getId(),
        ];
    }
}
