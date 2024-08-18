<?php

declare(strict_types=1);

namespace Olz\Tests\Fake;

use Olz\Entity\User;
use Olz\Utils\EmailUtils;
use Olz\Utils\GeneralUtils;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\Folder;
use Webklex\PHPIMAP\Message;
use Webklex\PHPIMAP\Query\WhereQuery;
use Webklex\PHPIMAP\Support\MessageCollection;

class FakeEmailUtils extends EmailUtils {
    use \Psr\Log\LoggerAwareTrait;

    /** @var array<array{user: User, token: string}> */
    public array $email_verification_emails_sent = [];
    public ?\Exception $send_email_verification_email_error = null;

    public FakeImapClient $client;

    public function __construct() {
        $this->client = new FakeImapClient();
    }

    public function sendEmailVerificationEmail(User $user): void {
        if ($this->send_email_verification_email_error !== null) {
            if ($this->logger) {
                $this->logger->error('Error sending fake verification email');
            }
            throw $this->send_email_verification_email_error;
        }
        $this->email_verification_emails_sent[] = ['user' => $user];
    }

    public function getImapClient(): Client {
        return $this->client;
    }

    public function encryptEmailReactionToken(mixed $data): string {
        $general_utils = new GeneralUtils();
        return $general_utils->base64EncodeUrl(json_encode($data));
    }

    public function decryptEmailReactionToken(string $token): mixed {
        return json_decode($token, true);
    }

    public function renderMarkdown(string $markdown): string {
        return $markdown;
    }
}

class FakeImapClient extends Client {
    public bool $exception = false;
    /** @var array<string, array<Message>> */
    public array $folders = [];
    public bool $is_connected = false;

    public function __construct() {
    }

    public function createFolder(string $folder_path, bool $expunge = true, bool $utf7 = false): Folder {
        return new Folder($this, $folder_path, '/', []);
    }

    public function connect(): Client {
        if ($this->exception) {
            throw new \Exception("Failed at something");
        }
        $this->is_connected = true;
        return $this;
    }

    // @phpstan-ignore-next-line
    public function getFolderByPath($folder_path, bool $utf7 = false, bool $soft_fail = false): Folder {
        return new FakeImapFolder($this, $this->folders[$folder_path] ?? []);
    }
}

class FakeImapFolder extends Folder {
    /** @param array<Message> $mails */
    public function __construct(
        protected Client $client,
        public array $mails = [],
    ) {
    }

    /** @param array<mixed> $extensions */
    public function messages(array $extensions = []): WhereQuery {
        return new FakeWhereQuery($this->client, $this->mails);
    }
}

class FakeWhereQuery extends WhereQuery {
    public bool $should_leave_unread = false;
    public bool $should_fetch_body = true;

    /** @param array<Message> $mails */
    public function __construct(
        protected Client $client,
        public array $mails = [],
    ) {
    }

    public function leaveUnread(): WhereQuery {
        $this->should_leave_unread = true;
        return $this;
    }

    public function setFetchBody(bool $value): WhereQuery {
        $this->should_fetch_body = $value;
        return $this;
    }

    public function all(): WhereQuery {
        return $this;
    }

    public function get(): MessageCollection {
        return new MessageCollection($this->mails ?? []);
    }
}
