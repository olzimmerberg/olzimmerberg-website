<?php

namespace Olz\Api\Endpoints;

use Olz\Api\OlzEndpoint;
use Olz\Exceptions\RecaptchaDeniedException;
use PhpTypeScriptApi\Fields\FieldTypes;
use PhpTypeScriptApi\HttpError;

class VerifyUserEmailEndpoint extends OlzEndpoint {
    public static function getIdent(): string {
        return 'VerifyUserEmailEndpoint';
    }

    public function getResponseField(): FieldTypes\Field {
        return new FieldTypes\ObjectField(['field_structure' => [
            'status' => new FieldTypes\EnumField(['allowed_values' => [
                'OK',
                'DENIED',
                'ERROR',
            ]]),
        ]]);
    }

    public function getRequestField(): FieldTypes\Field {
        return new FieldTypes\ObjectField(['field_structure' => [
            'recaptchaToken' => new FieldTypes\StringField([]),
        ]]);
    }

    protected function handle(mixed $input): mixed {
        $auth_utils = $this->authUtils();
        $user = $auth_utils->getCurrentUser();
        if (!$user) {
            throw new HttpError(401, "Nicht eingeloggt!");
        }

        $token = $input['recaptchaToken'];
        $this->emailUtils()->setLogger($this->log());
        try {
            if (!$this->recaptchaUtils()->validateRecaptchaToken($token)) {
                $this->log()->warning("reCaptcha token was invalid");
                throw new RecaptchaDeniedException("ReCaptcha Token ist ungültig");
            }
            $this->emailUtils()->sendEmailVerificationEmail($user);
        } catch (RecaptchaDeniedException $exc) {
            $this->log()->notice("Recaptcha denied for user (ID:{$user->getId()})");
            return ['status' => 'DENIED'];
        } catch (\Throwable $th) {
            $this->log()->error("Error verifying email for user (ID:{$user->getId()})", [$th]);
            return ['status' => 'ERROR'];
        }
        $this->entityManager()->flush();

        return [
            'status' => 'OK',
        ];
    }
}
