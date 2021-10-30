<?php

use PhpTypeScriptApi\Fields\FieldTypes;
use PhpTypeScriptApi\Fields\ValidationError;

require_once __DIR__.'/../OlzEndpoint.php';

class UpdateUserPasswordEndpoint extends OlzEndpoint {
    public function runtimeSetup() {
        parent::runtimeSetup();
        global $entityManager;
        require_once __DIR__.'/../../config/doctrine_db.php';
        require_once __DIR__.'/../../model/index.php';
        require_once __DIR__.'/../../utils/auth/AuthUtils.php';
        $auth_utils = AuthUtils::fromEnv();
        $this->setAuthUtils($auth_utils);
        $this->setEntityManager($entityManager);
    }

    public function setAuthUtils($new_auth_utils) {
        $this->authUtils = $new_auth_utils;
    }

    public function setEntityManager($new_entity_manager) {
        $this->entityManager = $new_entity_manager;
    }

    public static function getIdent() {
        return 'UpdateUserPasswordEndpoint';
    }

    public function getResponseField() {
        return new FieldTypes\ObjectField(['field_structure' => [
            'status' => new FieldTypes\EnumField(['allowed_values' => [
                'OK',
                'OTHER_USER',
                'INVALID_OLD',
            ]]),
        ]]);
    }

    public function getRequestField() {
        return new FieldTypes\ObjectField(['field_structure' => [
            'id' => new FieldTypes\IntegerField([]),
            'oldPassword' => new FieldTypes\StringField(['allow_empty' => false]),
            'newPassword' => new FieldTypes\StringField(['allow_empty' => false]),
        ]]);
    }

    protected function handle($input) {
        $auth_username = $this->session->get('user');

        $old_password = $input['oldPassword'];
        $new_password = $input['newPassword'];

        if (!$this->authUtils->isPasswordAllowed($new_password)) {
            throw new ValidationError(['newPassword' => ["Das neue Passwort muss mindestens 8 Zeichen lang sein."]]);
        }

        $user_repo = $this->entityManager->getRepository(User::class);
        $user = $user_repo->findOneBy(['id' => $input['id']]);

        if ($user->getUsername() !== $auth_username) {
            return ['status' => 'OTHER_USER'];
        }

        if (!password_verify($old_password, $user->getPasswordHash())) {
            return ['status' => 'INVALID_OLD'];
        }

        $user->setPasswordHash(password_hash($new_password, PASSWORD_DEFAULT));
        $this->entityManager->flush();

        return ['status' => 'OK'];
    }
}
