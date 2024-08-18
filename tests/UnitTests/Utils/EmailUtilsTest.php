<?php

declare(strict_types=1);

namespace Olz\Tests\UnitTests\Utils;

use Olz\Entity\User;
use Olz\Tests\Fake\Entity\FakeUser;
use Olz\Tests\Fake\FakeEnvUtils;
use Olz\Tests\Fake\FakeGeneralUtils;
use Olz\Tests\UnitTests\Common\UnitTestCase;
use Olz\Utils\EmailUtils;
use Olz\Utils\FixedDateUtils;
use Olz\Utils\WithUtilsCache;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;
use Webklex\PHPIMAP\Client;

class DeterministicEmailUtils extends EmailUtils {
    public function __construct() {
        $this->setEnvUtils(new FakeEnvUtils());
        $this->setGeneralUtils(new FakeGeneralUtils());
    }

    public function renderMarkdown(string $markdown): string {
        return $markdown;
    }

    protected function getRandomEmailVerificationToken(): string {
        return 'veryrandom';
    }
}

class TestOnlyEmailUtils extends EmailUtils {
    public function testOnlyGetRandomEmailVerificationToken(): string {
        return $this->getRandomEmailVerificationToken();
    }
}

/**
 * @internal
 *
 * @covers \Olz\Utils\EmailUtils
 */
final class EmailUtilsTest extends UnitTestCase {
    public function testSendEmailVerificationEmail(): void {
        $user = FakeUser::defaultUser();
        $mailer = $this->createPartialMock(MailerInterface::class, ['send']);
        $email_utils = new DeterministicEmailUtils();
        $email_utils->setMailer($mailer);
        $artifacts = [];
        $mailer->expects($this->exactly(1))->method('send')->with(
            $this->callback(function (Email $email) use (&$artifacts) {
                $artifacts['email'] = [...($artifacts['email'] ?? []), $email];
                return true;
            }),
            null,
        );

        $email_utils->sendEmailVerificationEmail($user);

        $this->assertSame([
            "INFO Email verification email sent to user (1).",
        ], $this->getLogs());
        $expected_email = <<<'ZZZZZZZZZZ'
            **!!! Falls du nicht soeben auf olzimmerberg.ch deine E-Mail-Adresse bestätigen wolltest, lösche diese E-Mail !!!**

            Hallo Default,

            *Um deine E-Mail-Adresse zu bestätigen*, klicke [hier](http://fake-base-url/_/email_reaktion?token=eyJhbGdvIjoiYWVzLTI1Ni1nY20iLCJpdiI6IlFVRkJRVUZCUVVGQlFVRkIiLCJ0YWciOiIzSDhXWDdxQWtlUU16R1U0c1ZmZlJBIiwiY2lwaGVydGV4dCI6IlVHOHNfbV9PVXFWX0tQSEVhZkNhbkZVc094dEkwbkdla0dOUFVfZ0ZLTlVmc2ZvMDdRdk10Ri1MUGZGbDMwR0h2UTRVSmFXVktkY01ZcHJGLTdWQ2g3Z1dJOGlZdnNCbGU4SFd2OTk5aEZOSkRZdnc4b19WWDMwM1hhR0kxZW8tc1hWcEFSTF8ifQ) oder auf folgenden Link:

            http://fake-base-url/_/email_reaktion?token=eyJhbGdvIjoiYWVzLTI1Ni1nY20iLCJpdiI6IlFVRkJRVUZCUVVGQlFVRkIiLCJ0YWciOiIzSDhXWDdxQWtlUU16R1U0c1ZmZlJBIiwiY2lwaGVydGV4dCI6IlVHOHNfbV9PVXFWX0tQSEVhZkNhbkZVc094dEkwbkdla0dOUFVfZ0ZLTlVmc2ZvMDdRdk10Ri1MUGZGbDMwR0h2UTRVSmFXVktkY01ZcHJGLTdWQ2g3Z1dJOGlZdnNCbGU4SFd2OTk5aEZOSkRZdnc4b19WWDMwM1hhR0kxZW8tc1hWcEFSTF8ifQ

            ZZZZZZZZZZ;
        $this->assertSame([
            <<<ZZZZZZZZZZ
                From: 
                Reply-To: 
                To: "Default User" <default-user@staging.olzimmerberg.ch>
                Cc: 
                Bcc: 
                Subject: [OLZ] E-Mail bestätigen

                {$expected_email}


                <div style="text-align: right; float: right;">
                    <img src="cid:olz_logo" alt="" style="width:150px;" />
                </div>
                <br /><br /><br />
                {$expected_email}


                olz_logo
                ZZZZZZZZZZ,
        ], array_map(function ($email) {
            return $this->emailUtils()->getComparableEmail($email);
        }, $artifacts['email']));
    }

    public function testSendEmailVerificationEmailFailsSending(): void {
        $user = FakeUser::defaultUser();
        $mailer = $this->createMock(MailerInterface::class);
        $email_utils = new DeterministicEmailUtils();
        $email_utils->setMailer($mailer);
        $mailer
            ->expects($this->exactly(1))
            ->method('send')
            ->will($this->throwException(new \Exception('mocked-error')))
        ;

        try {
            $email_utils->sendEmailVerificationEmail($user);
            $this->fail('Error expected');
        } catch (\Exception $exc) {
            $this->assertSame([
                "CRITICAL Error sending email verification email to user (1): mocked-error",
            ], $this->getLogs());
            $this->assertSame(
                'Error sending email verification email to user (1): mocked-error',
                $exc->getMessage()
            );
        }
    }

    public function testGetImapClient(): void {
        $email_utils = new EmailUtils();

        $client = $email_utils->getImapClient();

        $this->assertTrue($client instanceof Client);
        $this->assertSame('127.0.0.1', $client->host);
        $this->assertSame(143, $client->port);
        $this->assertSame('imap@staging.olzimmerberg.ch', $client->username);
        $this->assertSame('123456', $client->password);
    }

    public function testEmailReactionToken(): void {
        $email_utils = new EmailUtils();

        $token = $email_utils->encryptEmailReactionToken(['test' => 'data']);

        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9\-_]+$/', $token);
        $this->assertSame(
            ['test' => 'data'],
            $email_utils->decryptEmailReactionToken($token)
        );
    }

    public function testDecryptInvalidEmailReactionToken(): void {
        $email_utils = new EmailUtils();

        $this->assertNull($email_utils->decryptEmailReactionToken(''));
    }

    public function testBuildOlzEmail(): void {
        $email_utils = new EmailUtils();

        $user = new User();
        $user->setEmail('fake-user@staging.olzimmerberg.ch');
        $user->setFirstName('Fake');
        $user->setLastName('User');
        $email = new Email();

        $email = $email_utils->buildOlzEmail($email, $user, 'Tèśt', [
            'notification_type' => 'monthly_preview',
        ]);

        $this->assertSame([
        ], $this->getLogs());
        $this->assertSame(<<<'ZZZZZZZZZZ'
            From: 
            Reply-To: 
            To: "Fake User" <fake-user@staging.olzimmerberg.ch>
            Cc: 
            Bcc: 
            Subject: 

            Tèśt

            ---
            Abmelden?
            Keine solchen E-Mails mehr: http://fake-base-url/_/email_reaktion?token=eyJhbGdvIjoiYWVzLTI1Ni1nY20iLCJpdiI6IlFVRkJRVUZCUVVGQlFVRkIiLCJ0YWciOiJJemJPZlNkX01pcER3X29mNG13WFZ3IiwiY2lwaGVydGV4dCI6IlVHOHNfbV9PVXFWX0tQSEhZdkdHbUY4UUxCOUwzai1RbmpSSlJlOVZNSUZiOGJSOW9nVEtyQlRYZHZaaHpVbWR2eVZOS2FhVmVhMWRlNVRNNXJOSjBJZ0tLOFNEdXNCZ085ayJ9
            Keine E-Mails von OL Zimmerberg mehr: http://fake-base-url/_/email_reaktion?token=eyJhbGdvIjoiYWVzLTI1Ni1nY20iLCJpdiI6IlFVRkJRVUZCUVVGQlFVRkIiLCJ0YWciOiIwTUROYWljSElnQUt0ZzZURDk2QlVnIiwiY2lwaGVydGV4dCI6IlVHOHNfbV9PVXFWX0tQSEhZdkdHbUY4UUxCOUwzai1RbmpSSlJlOVZNSUZiOGJSOW9nVEtyQlRYZHZaaHpVbWR2eVZOS2FhVkJQWVRldG1ZNXFsUXpLbyJ9

            <div style="text-align: right; float: right;">
                <img src="cid:olz_logo" alt="" style="width:150px;" />
            </div>
            <br /><br /><br />
            <p>Tèśt</p>

            <br /><br />
            <hr style="border: 0; border-top: 1px solid black;">
            Abmelden? <a href="http://fake-base-url/_/email_reaktion?token=eyJhbGdvIjoiYWVzLTI1Ni1nY20iLCJpdiI6IlFVRkJRVUZCUVVGQlFVRkIiLCJ0YWciOiJJemJPZlNkX01pcER3X29mNG13WFZ3IiwiY2lwaGVydGV4dCI6IlVHOHNfbV9PVXFWX0tQSEhZdkdHbUY4UUxCOUwzai1RbmpSSlJlOVZNSUZiOGJSOW9nVEtyQlRYZHZaaHpVbWR2eVZOS2FhVmVhMWRlNVRNNXJOSjBJZ0tLOFNEdXNCZ085ayJ9">Keine solchen E-Mails mehr</a> oder <a href="http://fake-base-url/_/email_reaktion?token=eyJhbGdvIjoiYWVzLTI1Ni1nY20iLCJpdiI6IlFVRkJRVUZCUVVGQlFVRkIiLCJ0YWciOiIwTUROYWljSElnQUt0ZzZURDk2QlVnIiwiY2lwaGVydGV4dCI6IlVHOHNfbV9PVXFWX0tQSEhZdkdHbUY4UUxCOUwzai1RbmpSSlJlOVZNSUZiOGJSOW9nVEtyQlRYZHZaaHpVbWR2eVZOS2FhVkJQWVRldG1ZNXFsUXpLbyJ9">Keine E-Mails von OL Zimmerberg mehr</a>

            olz_logo
            ZZZZZZZZZZ, $email_utils->getComparableEmail($email));
    }

    public function testGetUserAddressWithName(): void {
        $email_utils = new EmailUtils();
        $user = new User();
        $user->setEmail('fake@staging.olzimmerberg.ch');
        $user->setFirstName('First');
        $user->setLastName('Last');
        $this->assertSame(
            '"First Last" <fake@staging.olzimmerberg.ch>',
            $email_utils->getUserAddress($user)->toString(),
        );
    }

    public function testGetComparableEmail(): void {
        $email_utils = new EmailUtils();
        $email = (new Email())
            ->from(new Address('fake-from@staging.olzimmerberg.ch', 'Fake From'))
            ->replyTo(new Address('fake-reply-to@staging.olzimmerberg.ch', 'Fake Reply-To'))
            ->to(
                new Address('to1@staging.olzimmerberg.ch', 'Fake To1'),
                new Address('to2@staging.olzimmerberg.ch', 'Fake To2'),
            )
            ->cc(
                new Address('cc1@staging.olzimmerberg.ch', 'Fake Cc1'),
                new Address('cc2@staging.olzimmerberg.ch', 'Fake Cc2'),
            )
            ->bcc(
                new Address('bcc1@staging.olzimmerberg.ch', 'Fake Bcc1'),
                new Address('bcc2@staging.olzimmerberg.ch', 'Fake Bcc2'),
            )
            ->subject('Fake Subject')
            ->text('Fake Text')
            ->html('Fake HTML')
            ->addPart((new DataPart(new File(__DIR__.'/../../../assets/icns/olz_logo_schwarzweiss_300.png'), 'olz_logo', 'image/png'))->asInline())
        ;

        $this->assertSame(<<<'ZZZZZZZZZZ'
            From: "Fake From" <fake-from@staging.olzimmerberg.ch>
            Reply-To: "Fake Reply-To" <fake-reply-to@staging.olzimmerberg.ch>
            To: "Fake To1" <to1@staging.olzimmerberg.ch>, "Fake To2" <to2@staging.olzimmerberg.ch>
            Cc: "Fake Cc1" <cc1@staging.olzimmerberg.ch>, "Fake Cc2" <cc2@staging.olzimmerberg.ch>
            Bcc: "Fake Bcc1" <bcc1@staging.olzimmerberg.ch>, "Fake Bcc2" <bcc2@staging.olzimmerberg.ch>
            Subject: Fake Subject

            Fake Text

            Fake HTML

            olz_logo
            ZZZZZZZZZZ, $email_utils->getComparableEmail($email));
    }

    public function testGetComparableEnvelope(): void {
        $email_utils = new EmailUtils();
        $envelope = new Envelope(
            new Address('fake-sender@staging.olzimmerberg.ch', 'Fake Sender'),
            [
                new Address('recipient1@staging.olzimmerberg.ch', 'Fake Recipient1'),
                new Address('recipient2@staging.olzimmerberg.ch', 'Fake Recipient2'),
            ],
        );
        $this->assertSame(<<<'ZZZZZZZZZZ'
            Sender: "Fake Sender" <fake-sender@staging.olzimmerberg.ch>
            Recipients: recipient1@staging.olzimmerberg.ch, recipient2@staging.olzimmerberg.ch
            ZZZZZZZZZZ, $email_utils->getComparableEnvelope($envelope));
    }

    public function testRenderMarkdown(): void {
        $email_utils = new EmailUtils();

        // Ignore HTML
        $html = $email_utils->renderMarkdown("Normal<h1>H1</h1><script>alert('not good!');</script>");
        $this->assertSame("<p>NormalH1alert('not good!');</p>\n", $html);

        // Headings
        $html = $email_utils->renderMarkdown("Normal\n# H1\n## H2\n### H3\nNormal");
        $this->assertSame("<p>Normal</p>\n<h1>H1</h1>\n<h2>H2</h2>\n<h3>H3</h3>\n<p>Normal</p>\n", $html);

        // Font style
        $html = $email_utils->renderMarkdown("Normal **fe\\*\\*tt** __fe\\_\\_tt__");
        $this->assertSame("<p>Normal <strong>fe**tt</strong> <strong>fe__tt</strong></p>\n", $html);
        $html = $email_utils->renderMarkdown("Normal *kur\\*siv* _kur\\_siv_");
        $this->assertSame("<p>Normal <em>kur*siv</em> <em>kur_siv</em></p>\n", $html);
        $html = $email_utils->renderMarkdown("Normal ~~durch\\~\\~gestrichen~~");
        $this->assertSame("<p>Normal <del>durch~~gestrichen</del></p>\n", $html);

        // Quotes
        $html = $email_utils->renderMarkdown("Normal\n> quote\nstill quote\n\nnot anymore");
        $this->assertSame("<p>Normal</p>\n<blockquote>\n<p>quote\nstill quote</p>\n</blockquote>\n<p>not anymore</p>\n", $html);

        // Ordered lists
        $html = $email_utils->renderMarkdown("Normal\n1. one\n2. two\n3. three\nstill three\n\nnot anymore");
        $this->assertSame("<p>Normal</p>\n<ol>\n<li>one</li>\n<li>two</li>\n<li>three\nstill three</li>\n</ol>\n<p>not anymore</p>\n", $html);

        // Unordered lists
        $html = $email_utils->renderMarkdown("Normal\n- one\n- two\n- three\nstill three\n\nnot anymore");
        $this->assertSame("<p>Normal</p>\n<ul>\n<li>one</li>\n<li>two</li>\n<li>three\nstill three</li>\n</ul>\n<p>not anymore</p>\n", $html);

        // Code
        $html = $email_utils->renderMarkdown("Normal `co\\`de`");
        $this->assertSame("<p>Normal <code>co\\</code>de`</p>\n", $html);
        $html = $email_utils->renderMarkdown("Normal ```co`de```");
        $this->assertSame("<p>Normal <code>co`de</code></p>\n", $html);
        $html = $email_utils->renderMarkdown("Normal\n```python\nco`de\n```");
        $this->assertSame("<p>Normal</p>\n<pre><code class=\"language-python\">co`de\n</code></pre>\n", $html);

        // Horizontal rule
        $html = $email_utils->renderMarkdown("something\n\n---\n\ndifferent");
        $this->assertSame("<p>something</p>\n<hr />\n<p>different</p>\n", $html);

        // Links
        $html = $email_utils->renderMarkdown("Normal [link](http://127.0.0.1/)");
        $this->assertSame("<p>Normal <a href=\"http://127.0.0.1/\">link</a></p>\n", $html);
        $html = $email_utils->renderMarkdown("Normal http://127.0.0.1/");
        $this->assertSame("<p>Normal <a href=\"http://127.0.0.1/\">http://127.0.0.1/</a></p>\n", $html);

        // Image
        $html = $email_utils->renderMarkdown("Normal ![bird](img/bird.jpg)");
        $this->assertSame("<p>Normal <img src=\"img/bird.jpg\" alt=\"bird\" /></p>\n", $html);

        // Table
        $html = $email_utils->renderMarkdown("Normal\n\n| left | middle | right |\n| --- | --- | --- |\n| 1 | 2 | 3 |\n\nafter");
        $this->assertSame("<p>Normal</p>\n<table>\n<thead>\n<tr>\n<th>left</th>\n<th>middle</th>\n<th>right</th>\n</tr>\n</thead>\n<tbody>\n<tr>\n<td>1</td>\n<td>2</td>\n<td>3</td>\n</tr>\n</tbody>\n</table>\n<p>after</p>\n", $html);

        // Footnote
        $html = $email_utils->renderMarkdown("This. [^1]\n\n[^1]: explains everything\n");
        // does not work
        $this->assertSame("<p>This. [^1]</p>\n<p>[^1]: explains everything</p>\n", $html);

        // Heading ID
        $html = $email_utils->renderMarkdown("# So linkable {#anchor}\n");
        // does not work
        $this->assertSame("<h1>So linkable {#anchor}</h1>\n", $html);

        // Heading ID
        $html = $email_utils->renderMarkdown("- [x] finish\n- [ ] this\n- [ ] list\n");
        $this->assertSame("<ul>\n<li><input checked=\"\" disabled=\"\" type=\"checkbox\"> finish</li>\n<li><input disabled=\"\" type=\"checkbox\"> this</li>\n<li><input disabled=\"\" type=\"checkbox\"> list</li>\n</ul>\n", $html);
    }

    public function testGetRandomEmailVerificationToken(): void {
        $email_utils = new TestOnlyEmailUtils();
        $this->assertMatchesRegularExpression(
            '/^[a-zA-Z0-9_-]{8}$/',
            $email_utils->testOnlyGetRandomEmailVerificationToken()
        );
    }

    public function testGenerateSpamEmailAddressIsDeterministic(): void {
        $email_utils = new DeterministicEmailUtils();
        $this->assertSame(
            'stefan.paul.andreas.munz',
            $email_utils->generateSpamEmailAddress()
        );
        $this->assertSame(
            'stefan.paul.andreas.munz',
            $email_utils->generateSpamEmailAddress()
        );
    }

    public function testGenerateSpamEmailAddressChangesWithTime(): void {
        $email_utils = new DeterministicEmailUtils();
        $this->assertSame(
            'stefan.paul.andreas.munz',
            $email_utils->generateSpamEmailAddress()
        );

        WithUtilsCache::set('dateUtils', new FixedDateUtils('2006-01-13 18:00:00'));

        $this->assertSame(
            'salome.philippa.alena.matheisen',
            $email_utils->generateSpamEmailAddress()
        );
    }

    public function testGenerateSpamEmailAddressChangesWithRequestURI(): void {
        $email_utils = new DeterministicEmailUtils();
        $this->assertSame(
            'stefan.paul.andreas.munz',
            $email_utils->generateSpamEmailAddress()
        );

        WithUtilsCache::set('server', ['REQUEST_URI' => 'test']);
        $this->assertSame(
            'samuel.patrick.alex.melzer',
            $email_utils->generateSpamEmailAddress()
        );
    }

    public function testIsSpamEmailAddress(): void {
        $email_utils = new EmailUtils();

        // Matching
        $this->assertTrue($email_utils->isSpamEmailAddress('s.p.a.m'));
        $this->assertTrue($email_utils->isSpamEmailAddress('simon.peter.alex.may'));

        // Denylist
        $this->assertTrue($email_utils->isSpamEmailAddress('jeweils'));
        $this->assertTrue($email_utils->isSpamEmailAddress('fotoposten'));

        // Valid E-Mail
        $this->assertFalse($email_utils->isSpamEmailAddress('max.muster'));
    }

    public function testSend(): void {
        $data_path = $this->envUtils()->getDataPath();
        $last_email_file = "{$data_path}last_email.txt";
        $mailer = $this->createPartialMock(MailerInterface::class, ['send']);
        $email_utils = new DeterministicEmailUtils();
        $email_utils->setMailer($mailer);
        $artifacts = [];
        $mailer->expects($this->exactly(1))->method('send')->with(
            $this->callback(function (Email $email) use (&$artifacts) {
                $artifacts['email'] = [...($artifacts['email'] ?? []), $email];
                return true;
            }),
            $this->callback(function (Envelope $envelope) use (&$artifacts) {
                $artifacts['envelope'] = [...($artifacts['envelope'] ?? []), $envelope];
                return true;
            }),
        );
        $email = (new Email())
            ->from(new Address('fake-from@staging.olzimmerberg.ch', 'Fake From'))
            ->replyTo(new Address('fake-reply-to@staging.olzimmerberg.ch', 'Fake Reply-To'))
            ->to(
                new Address('to1@staging.olzimmerberg.ch', 'Fake To1'),
                new Address('to2@staging.olzimmerberg.ch', 'Fake To2'),
            )
            ->cc(
                new Address('cc1@staging.olzimmerberg.ch', 'Fake Cc1'),
                new Address('cc2@staging.olzimmerberg.ch', 'Fake Cc2'),
            )
            ->bcc(
                new Address('bcc1@staging.olzimmerberg.ch', 'Fake Bcc1'),
                new Address('bcc2@staging.olzimmerberg.ch', 'Fake Bcc2'),
            )
            ->subject('Fake Subject')
            ->text('Fake Text')
            ->html('Fake HTML')
            ->addPart((new DataPart(new File(__DIR__.'/../../../assets/icns/olz_logo_schwarzweiss_300.png'), 'olz_logo', 'image/png'))->asInline())
        ;
        $envelope = new Envelope(
            new Address('fake-sender@staging.olzimmerberg.ch', 'Fake Sender'),
            [
                new Address('recipient1@staging.olzimmerberg.ch', 'Fake Recipient1'),
                new Address('recipient2@staging.olzimmerberg.ch', 'Fake Recipient2'),
            ],
        );

        $this->assertFileDoesNotExist($last_email_file);

        $email_utils->send($email, $envelope);

        $this->assertFileExists($last_email_file);
        $expected_email = <<<'ZZZZZZZZZZ'
            From: "Fake From" <fake-from@staging.olzimmerberg.ch>
            Reply-To: "Fake Reply-To" <fake-reply-to@staging.olzimmerberg.ch>
            To: "Fake To1" <to1@staging.olzimmerberg.ch>, "Fake To2" <to2@staging.olzimmerberg.ch>
            Cc: "Fake Cc1" <cc1@staging.olzimmerberg.ch>, "Fake Cc2" <cc2@staging.olzimmerberg.ch>
            Bcc: "Fake Bcc1" <bcc1@staging.olzimmerberg.ch>, "Fake Bcc2" <bcc2@staging.olzimmerberg.ch>
            Subject: Fake Subject

            Fake Text

            Fake HTML

            olz_logo
            ZZZZZZZZZZ;
        $expected_envelope = <<<'ZZZZZZZZZZ'
            Sender: "Fake Sender" <fake-sender@staging.olzimmerberg.ch>
            Recipients: recipient1@staging.olzimmerberg.ch, recipient2@staging.olzimmerberg.ch
            ZZZZZZZZZZ;
        $this->assertSame(<<<ZZZZZZZZZZ
            {$expected_envelope}

            {$expected_email}
            ZZZZZZZZZZ, file_get_contents($last_email_file));
        $this->assertSame([
            $expected_email,
        ], array_map(function ($email) {
            return $this->emailUtils()->getComparableEmail($email);
        }, $artifacts['email']));
        $this->assertSame([
            $expected_envelope,
        ], array_map(function ($envelope) {
            return $this->emailUtils()->getComparableEnvelope($envelope);
        }, $artifacts['envelope']));
    }
}
