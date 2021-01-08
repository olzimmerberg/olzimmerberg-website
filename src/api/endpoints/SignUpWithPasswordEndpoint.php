<?php

require_once __DIR__.'/../common/Endpoint.php';
require_once __DIR__.'/../../fields/DateTimeField.php';
require_once __DIR__.'/../../fields/EnumField.php';
require_once __DIR__.'/../../fields/StringField.php';

class SignUpWithPasswordEndpoint extends Endpoint {
    public function __construct($entityManager) {
        $this->entityManager = $entityManager;
    }

    public function getResponseFields() {
        return [
            new EnumField('status', ['allowed_values' => [
                'OK',
            ]]),
        ];
    }

    public function getRequestFields() {
        return [
            new StringField('firstName', ['allow_empty' => false]),
            new StringField('lastName', ['allow_empty' => false]),
            new StringField('username', ['allow_empty' => false]),
            new StringField('password', ['allow_empty' => false]),
            new StringField('email', ['allow_empty' => false]),
            new EnumField('gender', ['allowed_values' => ['M', 'F', 'O'], 'allow_null' => true]),
            new DateTimeField('birthdate', ['allow_null' => true]),
            new StringField('street', ['allow_empty' => true]),
            new StringField('postalCode', ['allow_empty' => true]),
            new StringField('city', ['allow_empty' => true]),
            new StringField('region', ['allow_empty' => true]),
            new StringField('countryCode', ['allow_empty' => true]),
        ];
    }

    protected function handle($input) {
        $ip_address = $this->server['REMOTE_ADDR'];
        $auth_request_repo = $this->entityManager->getRepository(AuthRequest::class);

        $user = new User();
        $user->setUsername($input['username']);
        $user->setEmail($input['email']);
        $user->setEmailIsVerified(false);
        $user->setEmailVerificationToken(null);
        $user->setPasswordHash(password_hash($input['password'], PASSWORD_DEFAULT));
        $user->setFirstName($input['firstName']);
        $user->setLastName($input['lastName']);
        $user->setGender($input['gender']);
        $user->setBirthdate($input['birthdate']);
        $user->setStreet($input['street']);
        $user->setPostalCode($input['postalCode']);
        $user->setCity($input['city']);
        $user->setRegion($input['region']);
        $user->setCountryCode($input['countryCode']);
        $user->setZugriff('');
        $user->setRoot(null);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $root = $user->getRoot() !== '' ? $user->getRoot() : './';
        // Mögliche Werte für 'zugriff': all, ftp, termine, mail
        $this->session->set('auth', $user->getZugriff());
        $this->session->set('root', $root);
        $this->session->set('user', $user->getUsername());
        $auth_request_repo->addAuthRequest($ip_address, 'AUTHENTICATED_PASSWORD', $user->getUsername());

        return ['status' => 'OK'];
    }
}
