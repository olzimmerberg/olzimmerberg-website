<?php

declare(strict_types=1);

namespace Olz\Tests\UnitTests\Command\SyncSolvCommand;

use Olz\Command\SyncSolvCommand\SolvEventsSyncer;
use Olz\Entity\SolvEvent;
use Olz\Tests\Fake;
use Olz\Tests\UnitTests\Common\UnitTestCase;
use Olz\Utils\WithUtilsCache;

class FakeSolvEventsSyncerSolvEventRepository {
    public $modifiedEvent;
    public $deletedEvent;
    public $deletedSolvUids = [];

    public function __construct() {
        $modified_event = Fake\FakeSolvEvent::defaultSolvEvent(true);
        $modified_event->setSolvUid(20202);
        $modified_event->setName('Modified Event (before)');
        $modified_event->setLastModification('2020-01-11 21:48:36');
        $modified_event->setRankLink(1235);
        $this->modifiedEvent = $modified_event;

        $deleted_event = Fake\FakeSolvEvent::defaultSolvEvent(true);
        $deleted_event->setSolvUid(20203);
        $deleted_event->setName('Deleted Event');
        $deleted_event->setLastModification('2020-01-11 21:36:48');
        $this->deletedEvent = $deleted_event;
    }

    public function getSolvEventsForYear($year) {
        switch ($year) {
            case '2020':
                return [
                    $this->modifiedEvent,
                    $this->deletedEvent,
                ];
            default:
                return [];
        }
    }

    public function deleteBySolvUid($solv_uid) {
        $this->deletedSolvUids[] = $solv_uid;
    }
}

class FakeSolvEventsSyncerSolvFetcher {
    public function fetchEventsCsvForYear($year) {
        switch ($year) {
            case '2020':
                return
                    "unique_id;date;duration;kind;day_night;national;region;type;event_name;event_link;club;map;location;coord_x;coord_y;deadline;entryportal;last_modification\n".
                    "20201;2020-04-01;1;foot;day;0;ZH/SH;*1;Inserted Event;http://staging.olzimmerberg.ch;OLC Kapreolo;Dübendorf;Dübendorf;689225;250900;;2;2020-03-13 09:13:27\n".
                    "20202;2020-04-02;1;foot;day;0;ZH/SH;*1;Modified Event (after);;OL Zimmerberg;Sihlwald;Albispass;681240;237075;;2;2020-03-13 13:09:27";
            default:
                return "unique_id;date;duration;kind;day_night;national;region;type;event_name;event_link;club;map;location;coord_x;coord_y;deadline;entryportal;last_modification\n";
        }
    }
}

/**
 * @internal
 *
 * @covers \Olz\Command\SyncSolvCommand\SolvEventsSyncer
 */
final class SolvEventsSyncerTest extends UnitTestCase {
    public function testSolvEventsSyncer(): void {
        $entity_manager = WithUtilsCache::get('entityManager');
        $solv_event_repo = new FakeSolvEventsSyncerSolvEventRepository();
        $entity_manager->repositories[SolvEvent::class] = $solv_event_repo;
        $solv_fetcher = new FakeSolvEventsSyncerSolvFetcher();

        $job = new SolvEventsSyncer();
        $job->setSolvFetcher($solv_fetcher);

        $job->syncSolvEventsForYear('2020');

        $flushed = $entity_manager->flushed_persisted;
        $this->assertSame(1, count($flushed));
        $this->assertSame('20201', $flushed[0]->getSolvUid());
        $this->assertSame('Inserted Event', $flushed[0]->getName());
        $this->assertSame('2020-03-13 09:13:27', $flushed[0]->getLastModification()->format('Y-m-d H:i:s'));
        $solv_event_repo = $entity_manager->getRepository(SolvEvent::class);
        $modified_event = $solv_event_repo->modifiedEvent;
        $this->assertSame(20202, $modified_event->getSolvUid());
        $this->assertSame('Modified Event (after)', $modified_event->getName());
        $this->assertSame('2020-03-13 13:09:27', $modified_event->getLastModification()->format('Y-m-d H:i:s'));
        $this->assertSame([20203], $solv_event_repo->deletedSolvUids);
    }
}
