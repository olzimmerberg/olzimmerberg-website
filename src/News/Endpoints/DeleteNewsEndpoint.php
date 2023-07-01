<?php

namespace Olz\News\Endpoints;

use Olz\Api\OlzDeleteEntityEndpoint;
use Olz\Entity\News\NewsEntry;
use PhpTypeScriptApi\HttpError;

class DeleteNewsEndpoint extends OlzDeleteEntityEndpoint {
    use NewsEndpointTrait;

    public static function getIdent() {
        return 'DeleteNewsEndpoint';
    }

    protected function handle($input) {
        $has_access = $this->authUtils()->hasPermission('any');
        if (!$has_access) {
            throw new HttpError(403, "Kein Zugriff!");
        }

        $entity_id = $input['id'];
        $news_repo = $this->entityManager()->getRepository(NewsEntry::class);
        $news_entry = $news_repo->findOneBy(['id' => $entity_id]);

        if (!$news_entry) {
            return ['status' => 'ERROR'];
        }

        if (!$this->entityUtils()->canUpdateOlzEntity($news_entry, null, 'news')) {
            throw new HttpError(403, "Kein Zugriff!");
        }

        $this->entityManager()->remove($news_entry);
        $this->entityManager()->flush();
        return ['status' => 'OK'];
    }
}
