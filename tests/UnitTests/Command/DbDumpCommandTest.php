<?php

declare(strict_types=1);

namespace Olz\Tests\UnitTests\Command;

use Olz\Command\DbDumpCommand;
use Olz\Tests\Fake;
use Olz\Tests\UnitTests\Common\UnitTestCase;
use Olz\Utils\WithUtilsCache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @internal
 *
 * @covers \Olz\Command\DbDumpCommand
 */
final class DbDumpCommandTest extends UnitTestCase {
    public function testDbDumpCommandSuccess(): void {
        $logger = Fake\FakeLogger::create();
        $command = new DbDumpCommand();
        $command->setLog($logger);
        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        $return_code = $command->run($input, $output);

        $this->assertSame([
            "INFO Running command Olz\\Command\\DbDumpCommand...",
            "INFO Successfully ran command Olz\\Command\\DbDumpCommand.",
        ], $logger->handler->getPrettyRecords());
        $this->assertSame(Command::SUCCESS, $return_code);
        $this->assertSame("", $output->fetch());
        $this->assertSame([
            'dumpDb',
        ], WithUtilsCache::get('devDataUtils')->commands_called);
    }
}
