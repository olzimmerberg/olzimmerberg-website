<?php

declare(strict_types=1);

namespace Olz\Tests\UnitTests\Termine\Endpoints;

use Olz\Entity\Role;
use Olz\Entity\Termine\Termin;
use Olz\Entity\User;
use Olz\Termine\Endpoints\UpdateTerminEndpoint;
use Olz\Tests\Fake;
use Olz\Tests\UnitTests\Common\UnitTestCase;
use Olz\Utils\FixedDateUtils;
use PhpTypeScriptApi\HttpError;

class FakeUpdateTerminEndpointTerminRepository {
    public function findOneBy($where) {
        if ($where === ['id' => 123]) {
            $entry = new Termin();
            $entry->setId(123);
            return $entry;
        }
        if ($where === ['id' => 9999]) {
            return null;
        }
        $where_json = json_encode($where);
        throw new \Exception("Query not mocked in findOneBy: {$where_json}", 1);
    }
}

/**
 * @internal
 *
 * @covers \Olz\Termine\Endpoints\UpdateTerminEndpoint
 */
final class UpdateTerminEndpointTest extends UnitTestCase {
    public const VALID_INPUT = [
        'id' => 123,
        'meta' => [
            'ownerUserId' => 1,
            'ownerRoleId' => 1,
            'onOff' => true,
        ],
        'data' => [
            'startDate' => '2020-03-13',
            'startTime' => null,
            'endDate' => null,
            'endTime' => null,
            'title' => 'Test event',
            'text' => 'some info',
            'link' => '<a href="test-anlass.ch">Home</a>',
            'deadline' => null,
            'newsletter' => false,
            'solvId' => null,
            'go2olId' => null,
            'types' => ['training', 'weekend'],
            'onOff' => true,
            'coordinateX' => null,
            'coordinateY' => null,
            'fileIds' => ['uploaded_file.pdf', 'inexistent.txt'],
        ],
    ];

    public function testUpdateTerminEndpointIdent(): void {
        $endpoint = new UpdateTerminEndpoint();
        $this->assertSame('UpdateTerminEndpoint', $endpoint->getIdent());
    }

    public function testUpdateTerminEndpointNoAccess(): void {
        $auth_utils = new Fake\FakeAuthUtils();
        $auth_utils->has_permission_by_query = ['termine' => false];
        $env_utils = new Fake\FakeEnvUtils();
        $logger = Fake\FakeLogger::create();
        $endpoint = new UpdateTerminEndpoint();
        $endpoint->setAuthUtils($auth_utils);
        $endpoint->setEnvUtils($env_utils);
        $endpoint->setLog($logger);

        try {
            $endpoint->call(self::VALID_INPUT);
            $this->fail('Error expected');
        } catch (HttpError $err) {
            $this->assertSame([
                "INFO Valid user request",
                "WARNING HTTP error 403",
            ], $logger->handler->getPrettyRecords());
            $this->assertSame(403, $err->getCode());
        }
    }

    public function testUpdateTerminEndpointNoSuchEntity(): void {
        $entity_manager = new Fake\FakeEntityManager();
        $termin_repo = new FakeUpdateTerminEndpointTerminRepository();
        $entity_manager->repositories[Termin::class] = $termin_repo;
        $auth_utils = new Fake\FakeAuthUtils();
        $auth_utils->has_permission_by_query = ['termine' => true];
        $entity_utils = new Fake\FakeEntityUtils();
        $entity_utils->can_update_olz_entity = true;
        $env_utils = new Fake\FakeEnvUtils();
        $logger = Fake\FakeLogger::create();
        $endpoint = new UpdateTerminEndpoint();
        $endpoint->setAuthUtils($auth_utils);
        $endpoint->setEntityManager($entity_manager);
        $endpoint->setEntityUtils($entity_utils);
        $endpoint->setEnvUtils($env_utils);
        $endpoint->setLog($logger);

        try {
            $endpoint->call([
                ...self::VALID_INPUT,
                'id' => 9999,
            ]);
            $this->fail('Error expected');
        } catch (HttpError $err) {
            $this->assertSame([
                "INFO Valid user request",
                "WARNING HTTP error 404",
            ], $logger->handler->getPrettyRecords());
            $this->assertSame(404, $err->getCode());
        }
    }

    public function testUpdateTerminEndpoint(): void {
        $entity_manager = new Fake\FakeEntityManager();
        $termin_repo = new FakeUpdateTerminEndpointTerminRepository();
        $entity_manager->repositories[Termin::class] = $termin_repo;
        $auth_utils = new Fake\FakeAuthUtils();
        $auth_utils->has_permission_by_query = ['termine' => true];
        $entity_utils = new Fake\FakeEntityUtils();
        $entity_utils->can_update_olz_entity = true;
        $env_utils = new Fake\FakeEnvUtils();
        $upload_utils = new Fake\FakeUploadUtils();
        $logger = Fake\FakeLogger::create();
        $endpoint = new UpdateTerminEndpoint();
        $endpoint->setAuthUtils($auth_utils);
        $endpoint->setDateUtils(new FixedDateUtils('2020-03-13 19:30:00'));
        $endpoint->setEntityManager($entity_manager);
        $endpoint->setEntityUtils($entity_utils);
        $endpoint->setEnvUtils($env_utils);
        $endpoint->setUploadUtils($upload_utils);
        $endpoint->setLog($logger);

        mkdir(__DIR__.'/../../tmp/temp/');
        file_put_contents(__DIR__.'/../../tmp/temp/uploaded_file.pdf', '');
        mkdir(__DIR__.'/../../tmp/files/');
        mkdir(__DIR__.'/../../tmp/files/termine/');

        $result = $endpoint->call(self::VALID_INPUT);

        $this->assertSame([
            "INFO Valid user request",
            "INFO Valid user response",
        ], $logger->handler->getPrettyRecords());

        $user_repo = $entity_manager->repositories[User::class];
        $role_repo = $entity_manager->repositories[Role::class];
        $this->assertSame([
            'status' => 'OK',
            'id' => 123,
        ], $result);
        $this->assertSame(1, count($entity_manager->persisted));
        $this->assertSame(1, count($entity_manager->flushed_persisted));
        $this->assertSame($entity_manager->persisted, $entity_manager->flushed_persisted);
        $termin = $entity_manager->persisted[0];
        $this->assertSame(123, $termin->getId());
        $this->assertSame('2020-03-13', $termin->getStartsOn()->format('Y-m-d'));
        $this->assertSame(null, $termin->getStartTime());
        $this->assertSame(null, $termin->getEndsOn());
        $this->assertSame(null, $termin->getEndTime());
        $this->assertSame('Test event', $termin->getTitle());
        $this->assertSame('some info', $termin->getText());
        $this->assertSame('<a href="test-anlass.ch">Home</a>', $termin->getLink());
        $this->assertSame(null, $termin->getDeadline());
        $this->assertSame(false, $termin->getNewsletter());
        $this->assertSame(null, $termin->getSolvId());
        $this->assertSame(null, $termin->getGo2olId());
        $this->assertSame(true, $termin->getOnOff());
        $this->assertSame(null, $termin->getCoordinateX());
        $this->assertSame(null, $termin->getCoordinateY());

        // TODO: Enable when Termine is migrated to OlzEntity
        // $this->assertSame([
        //     [$termin, 1, 1, 1],
        // ], $entity_utils->update_olz_entity_calls);

        $id = 123;

        $this->assertSame([
            [
                ['uploaded_file.pdf', 'inexistent.txt'],
                realpath(__DIR__.'/../../../')."/Fake/../UnitTests/tmp/files/termine/{$id}/",
            ],
        ], $upload_utils->move_uploads_calls);
    }
}
