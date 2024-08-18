<?php

namespace Olz\Users\Endpoints;

use Olz\Api\OlzGetEntityEndpoint;

class GetUserEndpoint extends OlzGetEntityEndpoint {
    use UserEndpointTrait;

    public static function getIdent(): string {
        return 'GetUserEndpoint';
    }

    protected function handle(mixed $input): mixed {
        $this->checkPermission('users');

        $entity = $this->getEntityById($input['id']);

        return [
            'id' => $entity->getId(),
            'meta' => $entity->getMetaData(),
            'data' => $this->getEntityData($entity),
        ];
    }
}
