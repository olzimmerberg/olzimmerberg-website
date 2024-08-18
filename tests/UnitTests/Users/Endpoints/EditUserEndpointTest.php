<?php

declare(strict_types=1);

namespace Olz\Tests\UnitTests\Users\Endpoints;

use Olz\Tests\Fake\Entity\Common\FakeOlzRepository;
use Olz\Tests\Fake\Entity\Roles\FakeRole;
use Olz\Tests\UnitTests\Common\UnitTestCase;
use Olz\Users\Endpoints\EditUserEndpoint;
use Olz\Utils\WithUtilsCache;
use PhpTypeScriptApi\HttpError;

/**
 * @internal
 *
 * @covers \Olz\Users\Endpoints\EditUserEndpoint
 */
final class EditUserEndpointTest extends UnitTestCase {
    public function testEditUserEndpointIdent(): void {
        $endpoint = new EditUserEndpoint();
        $this->assertSame('EditUserEndpoint', $endpoint->getIdent());
    }

    // public function testEditUserEndpointNoAccess(): void {
    //     WithUtilsCache::get('authUtils')->has_permission_by_query = ['roles' => false];
    //     WithUtilsCache::get('entityUtils')->can_update_olz_entity = false;
    //     $endpoint = new EditUserEndpoint();
    //     $endpoint->runtimeSetup();

    //     try {
    //         $endpoint->call([
    //             'id' => FakeOlzRepository::MINIMAL_ID,
    //         ]);
    //         $this->fail('Error expected');
    //     } catch (HttpError $err) {
    //         $this->assertSame([
    //             "INFO Valid user request",
    //             "WARNING HTTP error 403",
    //         ], $this->getLogs());

    //         $this->assertSame([
    //             [FakeRole::minimal(), null, null, null, null, 'roles'],
    //         ], WithUtilsCache::get('entityUtils')->can_update_olz_entity_calls);

    //         $this->assertSame(403, $err->getCode());
    //     }
    // }

    // public function testEditUserEndpointNoSuchEntity(): void {
    //     WithUtilsCache::get('authUtils')->has_permission_by_query = ['roles' => true];
    //     $endpoint = new EditUserEndpoint();
    //     $endpoint->runtimeSetup();

    //     try {
    //         $endpoint->call([
    //             'id' => FakeOlzRepository::NULL_ID,
    //         ]);
    //         $this->fail('Error expected');
    //     } catch (HttpError $err) {
    //         $this->assertSame([
    //             "INFO Valid user request",
    //             "WARNING HTTP error 404",
    //         ], $this->getLogs());
    //         $this->assertSame(404, $err->getCode());
    //     }
    // }

    // public function testEditUserEndpointNoEntityAccess(): void {
    //     WithUtilsCache::get('authUtils')->has_permission_by_query = ['roles' => false];
    //     WithUtilsCache::get('entityUtils')->can_update_olz_entity = false;
    //     $endpoint = new EditUserEndpoint();
    //     $endpoint->runtimeSetup();

    //     try {
    //         $endpoint->call([
    //             'id' => FakeOlzRepository::MINIMAL_ID,
    //         ]);
    //         $this->fail('Error expected');
    //     } catch (HttpError $err) {
    //         $this->assertSame([
    //             "INFO Valid user request",
    //             "WARNING HTTP error 403",
    //         ], $this->getLogs());

    //         $this->assertSame([
    //             [FakeRole::minimal(), null, null, null, null, 'roles'],
    //         ], WithUtilsCache::get('entityUtils')->can_update_olz_entity_calls);

    //         $this->assertSame(403, $err->getCode());
    //     }
    // }

    // public function testEditUserEndpointMinimal(): void {
    //     $id = FakeOlzRepository::MINIMAL_ID;
    //     WithUtilsCache::get('authUtils')->has_permission_by_query = ['roles' => true];
    //     WithUtilsCache::get('entityUtils')->can_update_olz_entity = true;
    //     $endpoint = new EditUserEndpoint();
    //     $endpoint->runtimeSetup();

    //     mkdir(__DIR__.'/../../tmp/files/');
    //     mkdir(__DIR__.'/../../tmp/files/roles/');
    //     mkdir(__DIR__."/../../tmp/files/roles/{$id}/");
    //     mkdir(__DIR__.'/../../tmp/img/');
    //     mkdir(__DIR__.'/../../tmp/img/roles/');
    //     mkdir(__DIR__."/../../tmp/img/roles/{$id}/");
    //     mkdir(__DIR__."/../../tmp/img/roles/{$id}/img/");

    //     $result = $endpoint->call([
    //         'id' => $id,
    //     ]);

    //     $this->assertSame([
    //         "INFO Valid user request",
    //         "INFO Valid user response",
    //     ], $this->getLogs());

    //     $this->assertSame([
    //         [FakeRole::minimal(), null, null, null, null, 'roles'],
    //     ], WithUtilsCache::get('entityUtils')->can_update_olz_entity_calls);

    //     $this->assertSame([
    //         'id' => $id,
    //         'meta' => [
    //             'ownerUserId' => null,
    //             'ownerRoleId' => null,
    //             'onOff' => true,
    //         ],
    //         'data' => [
    //             'username' => '-',
    //             'name' => '-',
    //             'title' => null,
    //             'description' => '',
    //             'guide' => '',
    //             'imageIds' => [],
    //             'fileIds' => [],
    //             'parentRole' => null,
    //             'indexWithinParent' => null,
    //             'featuredIndex' => null,
    //             'canHaveChildRoles' => false,
    //         ],
    //     ], $result);
    // }

    // public function testEditUserEndpointEmpty(): void {
    //     $id = FakeOlzRepository::EMPTY_ID;
    //     WithUtilsCache::get('authUtils')->has_permission_by_query = ['roles' => true];
    //     WithUtilsCache::get('entityUtils')->can_update_olz_entity = true;
    //     $endpoint = new EditUserEndpoint();
    //     $endpoint->runtimeSetup();

    //     mkdir(__DIR__.'/../../tmp/files/');
    //     mkdir(__DIR__.'/../../tmp/files/roles/');
    //     mkdir(__DIR__."/../../tmp/files/roles/{$id}/");
    //     mkdir(__DIR__.'/../../tmp/img/');
    //     mkdir(__DIR__.'/../../tmp/img/roles/');
    //     mkdir(__DIR__."/../../tmp/img/roles/{$id}/");
    //     mkdir(__DIR__."/../../tmp/img/roles/{$id}/img/");

    //     $result = $endpoint->call([
    //         'id' => $id,
    //     ]);

    //     $this->assertSame([
    //         "INFO Valid user request",
    //         "INFO Valid user response",
    //     ], $this->getLogs());

    //     $this->assertSame([
    //         [FakeRole::empty(), null, null, null, null, 'roles'],
    //     ], WithUtilsCache::get('entityUtils')->can_update_olz_entity_calls);

    //     $this->assertSame([
    //         'id' => $id,
    //         'meta' => [
    //             'ownerUserId' => null,
    //             'ownerRoleId' => null,
    //             'onOff' => false,
    //         ],
    //         'data' => [
    //             'username' => '-',
    //             'name' => '-',
    //             'title' => null,
    //             'description' => '',
    //             'guide' => '',
    //             'imageIds' => [],
    //             'fileIds' => [],
    //             'parentRole' => null,
    //             'indexWithinParent' => null,
    //             'featuredIndex' => null,
    //             'canHaveChildRoles' => false,
    //         ],
    //     ], $result);
    // }

    // public function testEditUserEndpointMaximal(): void {
    //     $id = FakeOlzRepository::MAXIMAL_ID;
    //     WithUtilsCache::get('authUtils')->has_permission_by_query = ['roles' => true];
    //     WithUtilsCache::get('entityUtils')->can_update_olz_entity = true;
    //     $endpoint = new EditUserEndpoint();
    //     $endpoint->runtimeSetup();

    //     mkdir(__DIR__.'/../../tmp/temp/');
    //     mkdir(__DIR__.'/../../tmp/files/');
    //     mkdir(__DIR__.'/../../tmp/files/roles/');
    //     mkdir(__DIR__."/../../tmp/files/roles/{$id}/");
    //     file_put_contents(__DIR__."/../../tmp/files/roles/{$id}/file___________________1.pdf", '');
    //     file_put_contents(__DIR__."/../../tmp/files/roles/{$id}/file___________________2.txt", '');
    //     mkdir(__DIR__.'/../../tmp/img/');
    //     mkdir(__DIR__.'/../../tmp/img/roles/');
    //     mkdir(__DIR__."/../../tmp/img/roles/{$id}/");
    //     mkdir(__DIR__."/../../tmp/img/roles/{$id}/img");
    //     file_put_contents(__DIR__."/../../tmp/img/roles/{$id}/img/picture________________A.jpg", '');
    //     file_put_contents(__DIR__."/../../tmp/img/roles/{$id}/img/picture________________B.jpg", '');

    //     $result = $endpoint->call([
    //         'id' => $id,
    //     ]);

    //     $this->assertSame([
    //         "INFO Valid user request",
    //         "INFO Valid user response",
    //     ], $this->getLogs());

    //     $this->assertSame([
    //         [FakeRole::maximal(), 'default', 'default', 'role', null, 'roles'],
    //     ], WithUtilsCache::get('entityUtils')->can_update_olz_entity_calls);

    //     $this->assertSame([
    //         'id' => $id,
    //         'meta' => [
    //             'ownerUserId' => 1,
    //             'ownerRoleId' => 1,
    //             'onOff' => true,
    //         ],
    //         'data' => [
    //             'username' => 'test-role',
    //             'name' => 'Test Role',
    //             'title' => 'Title Test Role',
    //             'description' => 'Description Test Role',
    //             'guide' => 'Just do it!',
    //             'imageIds' => ['picture________________A.jpg', 'picture________________B.jpg'],
    //             'fileIds' => ['file___________________1.pdf', 'file___________________2.txt'],
    //             'parentRole' => 3,
    //             'indexWithinParent' => 2,
    //             'featuredIndex' => 6,
    //             'canHaveChildRoles' => true,
    //         ],
    //     ], $result);
    // }
}
