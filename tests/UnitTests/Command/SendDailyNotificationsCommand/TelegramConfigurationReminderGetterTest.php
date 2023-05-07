<?php

declare(strict_types=1);

namespace Olz\Tests\UnitTests\Command\SendDailyNotificationsCommand;

use Olz\Command\SendDailyNotificationsCommand\TelegramConfigurationReminderGetter;
use Olz\Entity\User;
use Olz\Tests\UnitTests\Common\UnitTestCase;
use Olz\Utils\FixedDateUtils;
use Olz\Utils\WithUtilsCache;

/**
 * @internal
 *
 * @covers \Olz\Command\SendDailyNotificationsCommand\TelegramConfigurationReminderGetter
 */
final class TelegramConfigurationReminderGetterTest extends UnitTestCase {
    public function testTelegramConfigurationReminderGetterOnWrongDay(): void {
        $entity_manager = WithUtilsCache::get('entityManager');
        $not_the_day = TelegramConfigurationReminderGetter::DAY_OF_MONTH + 1;
        $not_the_day_str = str_pad("{$not_the_day}", 2, '0', STR_PAD_LEFT);
        $date_utils = new FixedDateUtils("2020-03-{$not_the_day_str} 19:30:00");

        $job = new TelegramConfigurationReminderGetter();
        $job->setDateUtils($date_utils);

        $notification = $job->getNotification(['cancelled' => false]);

        $this->assertSame(null, $notification);
    }

    public function testTelegramConfigurationReminderGetterCancelled(): void {
        $entity_manager = WithUtilsCache::get('entityManager');
        $the_day = TelegramConfigurationReminderGetter::DAY_OF_MONTH;
        $the_day_str = str_pad("{$the_day}", 2, '0', STR_PAD_LEFT);
        $date_utils = new FixedDateUtils("2020-03-{$the_day_str} 19:00:00");

        $job = new TelegramConfigurationReminderGetter();
        $job->setDateUtils($date_utils);

        $notification = $job->getNotification(['cancelled' => true]);

        $this->assertSame(null, $notification);
    }

    public function testTelegramConfigurationReminderGetter(): void {
        $the_day = TelegramConfigurationReminderGetter::DAY_OF_MONTH;
        $the_day_str = str_pad("{$the_day}", 2, '0', STR_PAD_LEFT);
        $date_utils = new FixedDateUtils("2020-03-{$the_day_str} 19:00:00");
        $user = new User();
        $user->setFirstName('First');

        $job = new TelegramConfigurationReminderGetter();
        $job->setDateUtils($date_utils);

        $notification = $job->getNotification(['cancelled' => false]);

        $expected_text = <<<'ZZZZZZZZZZ'
        Hallo First,
        
        Du hast bisher keinerlei Push-Nachrichten für Telegram abonniert.


        **Du möchtest eigentlich Push-Nachrichten erhalten?**
        
        In diesem Fall musst du dich auf der Website *einloggen*, und unter ["Service"](http://fake-base-url/_/service.php) bei "Nachrichten-Push" die gewünschten Benachrichtigungen auswählen.


        **Du möchtest gar keine Push-Nachrichten erhalten?**

        Dann lösche einfach diesen Chat.


        ZZZZZZZZZZ;
        $this->assertSame('Keine Push-Nachrichten abonniert', $notification->title);
        $this->assertSame($expected_text, $notification->getTextForUser($user));
    }
}
