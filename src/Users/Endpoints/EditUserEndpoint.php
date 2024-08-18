<?php

namespace Olz\Users\Endpoints;

use Olz\Api\OlzEditEntityEndpoint;
use PhpTypeScriptApi\HttpError;

class EditUserEndpoint extends OlzEditEntityEndpoint {
    use UserEndpointTrait;

    public static function getIdent(): string {
        return 'EditUserEndpoint';
    }

    protected function handle(mixed $input): mixed {
        $entity = $this->getEntityById($input['id']);

        $is_superior = $this->authUtils()->hasRoleEditPermission($input['id']);
        $is_owner = $this->entityUtils()->canUpdateOlzEntity($entity, null, 'roles');
        if (!$is_superior && !$is_owner) {
            throw new HttpError(403, "Kein Zugriff!");
        }

        $this->editUploads($entity);

        return [
            'id' => $entity->getId(),
            'meta' => $entity->getMetaData(),
            'data' => $this->getEntityData($entity),
        ];
    }
}
