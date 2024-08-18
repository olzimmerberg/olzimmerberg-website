<?php

namespace Olz\Roles\Endpoints;

use Olz\Entity\Roles\Role;
use Olz\Utils\WithUtilsTrait;
use PhpTypeScriptApi\Fields\FieldTypes;
use PhpTypeScriptApi\HttpError;

trait RoleEndpointTrait {
    use WithUtilsTrait;

    public function usesExternalId(): bool {
        return false;
    }

    public function getEntityDataField(bool $allow_null): FieldTypes\Field {
        return new FieldTypes\ObjectField([
            'export_as' => $allow_null ? 'OlzRoleDataOrNull' : 'OlzRoleData',
            'field_structure' => [
                'username' => new FieldTypes\StringField([]),
                'name' => new FieldTypes\StringField([]),
                'title' => new FieldTypes\StringField(['allow_null' => true]),
                'description' => new FieldTypes\StringField(['allow_empty' => true]),
                'guide' => new FieldTypes\StringField(['allow_empty' => true]),
                'imageIds' => new FieldTypes\ArrayField([
                    'item_field' => new FieldTypes\StringField([]),
                ]),
                'fileIds' => new FieldTypes\ArrayField([
                    'item_field' => new FieldTypes\StringField([]),
                ]),
                // TODO permissions
                'parentRole' => new FieldTypes\NumberField(['min_value' => 1, 'allow_null' => true]),
                'indexWithinParent' => new FieldTypes\NumberField(['min_value' => 0, 'allow_null' => true]),
                'featuredIndex' => new FieldTypes\NumberField(['allow_null' => true]),
                'canHaveChildRoles' => new FieldTypes\BooleanField([]),
            ],
            'allow_null' => $allow_null,
        ]);
    }

    /** @return array<string, mixed> */
    public function getEntityData(Role $entity): array {
        return [
            'username' => $entity->getUsername() ? $entity->getUsername() : '-',
            'name' => $entity->getName() ? $entity->getName() : '-',
            'title' => $entity->getTitle() ? $entity->getTitle() : null,
            'description' => $entity->getDescription(),
            'guide' => $entity->getGuide(),
            'imageIds' => $entity->getStoredImageUploadIds(),
            'fileIds' => $entity->getStoredFileUploadIds(),
            'parentRole' => $entity->getParentRoleId() ?? null,
            'indexWithinParent' => ($entity->getIndexWithinParent() ?? -1) < 0 ? null : $entity->getIndexWithinParent(),
            'featuredIndex' => $entity->getFeaturedIndex() ?? null,
            'canHaveChildRoles' => $entity->getCanHaveChildRoles(),
        ];
    }

    /** @param array<string, mixed> $input_data */
    public function updateEntityWithData(Role $entity, array $input_data): void {
        $this->updateEntityWithNonParentData($entity, $input_data);
        $entity->setParentRoleId($input_data['parentRole'] ?? null);
        $entity->setIndexWithinParent($input_data['indexWithinParent'] ?? null);
        $entity->setFeaturedIndex($input_data['featuredIndex']);
        $entity->setCanHaveChildRoles($input_data['canHaveChildRoles']);
    }

    /** @param array<string, mixed> $input_data */
    public function updateEntityWithNonParentData(Role $entity, array $input_data): void {
        $entity->setUsername($input_data['username']);
        $entity->setName($input_data['name']);
        $entity->setTitle($input_data['title']);
        $entity->setDescription($input_data['description']);
        $entity->setGuide($input_data['guide']);
    }

    /** @param array<string, mixed> $input_data */
    public function persistUploads(Role $entity, array $input_data): void {
        $this->persistOlzImages($entity, $input_data['imageIds']);
        $this->persistOlzFiles($entity, $input_data['fileIds']);
    }

    public function editUploads(Role $entity): void {
        $image_ids = $this->uploadUtils()->getStoredUploadIds("{$entity->getImagesPathForStorage()}img/");
        $this->editOlzImages($entity, $image_ids);
        $this->editOlzFiles($entity);
    }

    protected function getEntityById(int $id): Role {
        $repo = $this->entityManager()->getRepository(Role::class);
        $entity = $repo->findOneBy(['id' => $id]);
        if (!$entity) {
            throw new HttpError(404, "Nicht gefunden.");
        }
        return $entity;
    }
}
