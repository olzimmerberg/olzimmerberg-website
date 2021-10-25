<?php

use PhpTypeScriptApi\Fields\FieldTypes;

require_once __DIR__.'/../../api/OlzEndpoint.php';
require_once __DIR__.'/../../model/Role.php';
require_once __DIR__.'/../../model/User.php';
require_once __DIR__.'/../model/NewsEntry.php';

class CreateNewsEndpoint extends OlzEndpoint {
    public function runtimeSetup() {
        parent::runtimeSetup();
        global $_CONFIG, $_DATE, $entityManager;
        require_once __DIR__.'/../../config/date.php';
        require_once __DIR__.'/../../config/doctrine_db.php';
        require_once __DIR__.'/../../model/index.php';
        require_once __DIR__.'/../../utils/auth/AuthUtils.php';
        require_once __DIR__.'/../../utils/env/EnvUtils.php';
        $auth_utils = AuthUtils::fromEnv();
        $env_utils = EnvUtils::fromEnv();
        $this->setAuthUtils($auth_utils);
        $this->setDateUtils($_DATE);
        $this->setEntityManager($entityManager);
        $this->setEnvUtils($env_utils);
    }

    public function setAuthUtils($new_auth_utils) {
        $this->authUtils = $new_auth_utils;
    }

    public function setDateUtils($dateUtils) {
        $this->dateUtils = $dateUtils;
    }

    public function setEntityManager($new_entity_manager) {
        $this->entityManager = $new_entity_manager;
    }

    public function setEnvUtils($envUtils) {
        $this->envUtils = $envUtils;
    }

    public static function getIdent() {
        return 'CreateNewsEndpoint';
    }

    public function getResponseField() {
        return new FieldTypes\ObjectField(['field_structure' => [
            'status' => new FieldTypes\EnumField(['allowed_values' => [
                'OK',
                'ERROR',
            ]]),
            'newsId' => new FieldTypes\IntegerField(['allow_null' => true, 'min_value' => 1]),
        ]]);
    }

    public function getRequestField() {
        return new FieldTypes\ObjectField(['field_structure' => [
            'ownerUserId' => new FieldTypes\IntegerField(['allow_null' => true, 'min_value' => 1]),
            'ownerRoleId' => new FieldTypes\IntegerField(['allow_null' => true, 'min_value' => 1]),
            'author' => new FieldTypes\StringField(['allow_null' => true]),
            'authorUserId' => new FieldTypes\IntegerField(['allow_null' => true, 'min_value' => 1]),
            'authorRoleId' => new FieldTypes\IntegerField(['allow_null' => true, 'min_value' => 1]),
            'title' => new FieldTypes\StringField([]),
            'teaser' => new FieldTypes\StringField(['allow_empty' => true]),
            'content' => new FieldTypes\StringField(['allow_empty' => true]),
            'externalUrl' => new FieldTypes\StringField(['allow_null' => true]),
            'tags' => new FieldTypes\ArrayField([
                'item_field' => new FieldTypes\StringField([]),
            ]),
            'terminId' => new FieldTypes\IntegerField(['allow_null' => true, 'min_value' => 1]),
            'onOff' => new FieldTypes\BooleanField(['default_value' => true]),
            'imageIds' => new FieldTypes\ArrayField([
                'item_field' => new FieldTypes\StringField([]),
            ]),
            'fileIds' => new FieldTypes\ArrayField([
                'item_field' => new FieldTypes\StringField([]),
            ]),
        ]]);
    }

    protected function handle($input) {
        $has_access = $this->authUtils->hasPermission('news');
        if (!$has_access) {
            return ['status' => 'ERROR', 'newsId' => null];
        }

        $user_repo = $this->entityManager->getRepository(User::class);
        $role_repo = $this->entityManager->getRepository(Role::class);
        $current_user = $this->authUtils->getSessionUser();

        $owner_user_id = $input['ownerUserId'] ?? null;
        $owner_user = $current_user;
        if ($owner_user_id) {
            $owner_user = $user_repo->findOneBy(['id' => $owner_user_id]);
        }

        $owner_role_id = $input['ownerRoleId'] ?? null;
        $owner_role = null;
        if ($owner_role_id) {
            $owner_role = $role_repo->findOneBy(['id' => $owner_role_id]);
        }

        $author_user_id = $input['authorUserId'] ?? null;
        $author_user = $current_user;
        if ($author_user_id) {
            $author_user = $user_repo->findOneBy(['id' => $author_user_id]);
        }

        $author_role_id = $input['authorRoleId'] ?? null;
        $author_role = null;
        if ($author_role_id) {
            $author_role = $role_repo->findOneBy(['id' => $author_role_id]);
        }

        $today = new DateTime($this->dateUtils->getIsoToday());
        $now = new DateTime($this->dateUtils->getIsoNow());

        $tags_for_db = ' '.implode(' ', $input['tags']).' ';

        $data_path = $this->envUtils->getDataPath();
        $valid_image_ids = [];
        foreach ($input['imageIds'] as $image_id) {
            $image_path = "{$data_path}temp/{$image_id}";
            if (!is_file($image_path)) {
                $this->logger->warning("Image file {$image_path} does not exist.");
                continue;
            }
            $valid_image_ids[] = $image_id;
        }

        $news_entry = new NewsEntry();
        $news_entry->setCreatedAt($now);
        $news_entry->setLastModifiedAt($now);
        $news_entry->setOwnerUser($owner_user);
        $news_entry->setOwnerRole($owner_role);
        $news_entry->setAuthor($input['author']);
        $news_entry->setAuthorUser($author_user);
        $news_entry->setAuthorRole($author_role);
        $news_entry->setDate($today);
        $news_entry->setTitle($input['title']);
        $news_entry->setTeaser($input['teaser']);
        $news_entry->setContent($input['content']);
        $news_entry->setExternalUrl($input['externalUrl']);
        $news_entry->setTags($tags_for_db);
        $news_entry->setImageIds($valid_image_ids);
        // TODO: Do not ignore
        $news_entry->setTermin(0);
        $news_entry->setOnOff($input['onOff'] ? 1 : 0);
        $news_entry->setCounter(0);
        $news_entry->setType('aktuell');
        $news_entry->setNewsletter(1);

        $this->entityManager->persist($news_entry);
        $this->entityManager->flush();

        $news_entry_id = $news_entry->getId();

        $news_entry_img_path = "{$data_path}img/news/{$news_entry_id}/";
        mkdir($news_entry_img_path);
        mkdir("{$news_entry_img_path}img/");
        mkdir("{$news_entry_img_path}thumb/");
        foreach ($valid_image_ids as $image_id) {
            $image_path = "{$data_path}temp/{$image_id}";
            if (!is_file($image_path)) {
                // @codeCoverageIgnoreStart
                // Reason: Should never happen in reality.
                throw new Exception("Image file {$image_path} previously existed, but not anymore?!?");
                // @codeCoverageIgnoreEnd
            }
            $destination_path = "{$news_entry_img_path}img/{$image_id}";
            rename($image_path, $destination_path);

            // TODO: Generate default thumbnails.
        }

        $news_entry_files_path = "{$data_path}files/news/{$news_entry_id}/";
        mkdir($news_entry_files_path);
        foreach ($input['fileIds'] as $file_id) {
            $file_path = "{$data_path}temp/{$file_id}";
            if (!is_file($file_path)) {
                $this->logger->warning("File {$file_path} does not exist.");
                continue;
            }
            $destination_path = "{$news_entry_files_path}{$file_id}";
            rename($file_path, $destination_path);
        }

        return [
            'status' => 'OK',
            'newsId' => $news_entry_id,
        ];
    }
}
