<?php

use PhpTypeScriptApi\HttpError;

require_once __DIR__.'/../../api/OlzCreateEntityEndpoint.php';
require_once __DIR__.'/../AnmeldenConstants.php';
require_once __DIR__.'/RegistrationEndpointTrait.php';

class CreateRegistrationEndpoint extends OlzCreateEntityEndpoint {
    use RegistrationEndpointTrait;

    public static function getIdent() {
        return 'CreateRegistrationEndpoint';
    }

    protected function handle($input) {
        $has_access = $this->authUtils->hasPermission('any');
        if (!$has_access) {
            throw new HttpError(403, "Kein Zugriff!");
        }

        $input_data = $input['data'];

        $registration = new Registration();
        $this->entityUtils->createOlzEntity($registration, $input['meta']);
        $registration->setTitle($input_data['title']);
        $registration->setDescription($input_data['description']);
        $registration->setOpensAt($input_data['opensAt'] ? new DateTime($input_data['opensAt']) : null);
        $registration->setClosesAt($input_data['closesAt'] ? new DateTime($input_data['closesAt']) : null);

        $this->entityManager->persist($registration);

        foreach ($input_data['infos'] as $index => $info_spec) {
            $title_ident = preg_replace('/[^a-zA-Z0-9]+/', '_', $info_spec['title']);
            $ident = "{$index}-{$title_ident}";

            $options_json = json_encode($info_spec['options']);

            $registration_info = new RegistrationInfo();
            $this->entityUtils->createOlzEntity($registration_info, $input['meta']);
            $registration_info->setRegistration($registration);
            $registration_info->setIndexWithinRegistration($index);
            $registration_info->setIdent($ident);
            $registration_info->setTitle($info_spec['title']);
            $registration_info->setDescription($info_spec['description']);
            $registration_info->setType($info_spec['type']);
            $registration_info->setIsOptional($info_spec['isOptional'] ? true : false);
            $registration_info->setOptions($options_json);

            $this->entityManager->persist($registration_info);
        }
        $this->entityManager->flush();

        $internal_id = $registration->getId();
        $external_id = $this->idUtils->toExternalId($internal_id, 'Registration');

        return [
            'status' => 'OK',
            'id' => $external_id,
        ];
    }
}
