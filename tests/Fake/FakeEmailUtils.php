<?php

declare(strict_types=1);

namespace Olz\Tests\Fake;

use Olz\Utils\GeneralUtils;
use PhpImap\Exceptions\ConnectionException;

class FakeEmailUtils {
    use \Psr\Log\LoggerAwareTrait;

    public function __construct() {
        $this->mailbox = new FakeMailbox();
        $this->olzMailer = new FakeOlzMailer();
    }

    public function getImapMailbox() {
        return $this->mailbox;
    }

    public function createEmail() {
        return $this->olzMailer;
    }

    public function encryptEmailReactionToken($data) {
        $general_utils = new GeneralUtils();
        return $general_utils->base64EncodeUrl(json_encode($data));
    }

    public function decryptEmailReactionToken($token) {
        return json_decode($token, true);
    }

    public function renderMarkdown($markdown) {
        return $markdown;
    }
}

class FakeMailbox {
    public $unexpected_value_exception = false;
    public $connection_exception = false;
    public $exception = false;
    public $mail_dict = [];
    public $deleted_mail_dict = [];
    public $moved_mail = [];
    public $expunged_mail_dict = [];
    public $current_mailbox;

    public function setAttachmentsIgnore($should_ignore_attachments) {
    }

    public function createMailbox($name) {
    }

    public function switchMailbox($name) {
        $this->current_mailbox = $name;
    }

    public function searchMailbox($query) {
        if ($this->unexpected_value_exception) {
            throw new \UnexpectedValueException("Phew, that was unexpected");
        }
        if ($this->connection_exception) {
            throw new ConnectionException(["Host not found or something"]);
        }
        if ($this->exception) {
            throw new \Exception("Failed at something else");
        }
        if ($query === 'ALL') {
            if ($this->current_mailbox === 'INBOX.Processed') {
                return [];
            }
            if ($this->current_mailbox === 'INBOX') {
                return array_keys($this->mail_dict);
            }
            throw new \Exception("No such mailbox: {$this->current_mailbox}");
        }
        throw new \Exception("Expected 'ALL' query to searchMailbox");
    }

    public function getMailsInfo($mail_ids) {
        return array_map(function ($mail_id) {
            return new FakeMailInfo($mail_id);
        }, $mail_ids);
    }

    public function getMail($mail_id, $should_mark_read) {
        return $this->mail_dict[$mail_id];
    }

    public function moveMail($mail_id, $mailbox) {
        $this->moved_mail[] = "{$mail_id} => {$mailbox}";
    }

    public function deleteMail($mail_id) {
        $this->deleted_mail_dict[$mail_id] = true;
    }

    public function expungeDeletedMails() {
        $this->expunged_mail_dict = $this->deleted_mail_dict;
    }
}

class FakeMailInfo {
    public function __construct($mail_id) {
        $this->uid = $mail_id;
        $this->message_id = $mail_id;
    }
}

class FakeOlzMailer {
    public $emails_sent = [];
    public $email_to_send;
    public $reply_to;

    public function configure($user, $title, $text) {
        $this->email_to_send = [$user, $title, $text];
    }

    public function addReplyTo($address, $name) {
        $this->reply_to = [$address, $name];
    }

    public function send() {
        $title_provokes_error = str_contains(
            $this->email_to_send[1], 'provoke_error');
        $text_provokes_error = str_contains(
            $this->email_to_send[2], 'provoke_error');
        if ($title_provokes_error || $text_provokes_error) {
            throw new \Exception("Provoked Mailer Error");
        }
        $this->emails_sent[] = $this->email_to_send;
    }
}
