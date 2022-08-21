<?php

namespace Olz\News\Endpoints;

use Olz\Api\OlzGetEntityEndpoint;
use Olz\Entity\News\NewsEntry;
use PhpTypeScriptApi\HttpError;

class GetNewsEndpoint extends OlzGetEntityEndpoint {
    use NewsEndpointTrait;

    public static function getIdent() {
        return 'GetNewsEndpoint';
    }

    protected function handle($input) {
        $has_access = $this->authUtils->hasPermission('news');
        if (!$has_access) {
            throw new HttpError(403, "Kein Zugriff!");
        }
        $data_path = $this->envUtils->getDataPath();

        $entity_id = $input['id'];
        $news_repo = $this->entityManager->getRepository(NewsEntry::class);
        $news_entry = $news_repo->findOneBy(['id' => $entity_id]);

        $owner_user = $news_entry->getOwnerUser();
        $owner_role = $news_entry->getOwnerRole();
        $author_user = $news_entry->getAuthorUser();
        $author_role = $news_entry->getAuthorRole();
        $tags_for_api = array_filter(
            explode(' ', trim($news_entry->getTags())),
            function ($item) {
                return trim($item) != '';
            }
        );
        $termin_id = $news_entry->getTermin();

        $file_ids = [];
        $news_entry_files_path = "{$data_path}files/news/{$entity_id}/";
        $files_path_entries = scandir($news_entry_files_path);
        foreach ($files_path_entries as $file_id) {
            if (substr($file_id, 0, 1) != '.') {
                $file_ids[] = $file_id;
            }
        }

        return [
            'id' => $entity_id,
            'data' => [
                'ownerUserId' => $owner_user ? $owner_user->getId() : null,
                'ownerRoleId' => $owner_role ? $owner_role->getId() : null,
                'author' => $news_entry->getAuthor(),
                'authorUserId' => $author_user ? $author_user->getId() : null,
                'authorRoleId' => $author_role ? $author_role->getId() : null,
                'title' => $news_entry->getTitle(),
                'teaser' => $news_entry->getTeaser(),
                'content' => $news_entry->getContent(),
                'externalUrl' => $news_entry->getExternalUrl(),
                'tags' => $tags_for_api,
                'terminId' => $termin_id ? $termin_id : null,
                'onOff' => $news_entry->getOnOff() ? true : false,
                'imageIds' => $news_entry->getImageIds(),
                'fileIds' => $file_ids,
            ],
        ];
    }
}
