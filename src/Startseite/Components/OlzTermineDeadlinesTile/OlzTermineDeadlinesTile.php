<?php

// =============================================================================
// Zeigt eine Startseiten-Kachel mit den nächsten Meldeschlüssen an.
// =============================================================================

namespace Olz\Startseite\Components\OlzTermineDeadlinesTile;

use Olz\Apps\OlzApps;
use Olz\Entity\User;
use Olz\Startseite\Components\AbstractOlzTile\AbstractOlzTile;

class OlzTermineDeadlinesTile extends AbstractOlzTile {
    public function getRelevance(?User $user): float {
        return 0.75;
    }

    public function getHtml($args = []): string {
        $db = $this->dbUtils()->getDb();
        $date_utils = $this->dateUtils();
        $code_href = $this->envUtils()->getCodeHref();
        $today = $date_utils->getIsoToday();
        $now = $date_utils->getIsoNow();

        $newsletter_link = '';
        $newsletter_app = OlzApps::getApp('Newsletter');
        if ($newsletter_app) {
            $newsletter_link = <<<ZZZZZZZZZZ
            <a href='{$code_href}{$newsletter_app->getHref()}' class='newsletter-link'>
                <img
                    src='{$newsletter_app->getIcon()}'
                    alt='newsletter'
                    class='newsletter-link-icon'
                    title='Newsletter abonnieren!'
                />
            </a>
            ZZZZZZZZZZ;
        } else {
            $this->log()->error('Newsletter App does not exist!');
        }
        $out = "<h2>Meldeschlüsse {$newsletter_link}</h2>";

        $out .= "<ul class='links'>";
        $res = $db->query(<<<ZZZZZZZZZZ
        (
            SELECT
                se.deadline as deadline,
                t.datum as date,
                t.titel as title,
                t.id as id
            FROM termine t JOIN solv_events se ON (t.solv_uid = se.solv_uid)
            WHERE se.deadline IS NOT NULL AND se.deadline >= '{$today}'
        ) UNION ALL (
            SELECT
                DATE(t.deadline) as deadline,
                t.datum as date,
                t.titel as title,
                t.id as id
            FROM termine t
            WHERE t.deadline IS NOT NULL AND t.deadline >= '{$now}'
        )
        ORDER BY deadline ASC
        LIMIT 5
        ZZZZZZZZZZ);
        while ($row = $res->fetch_assoc()) {
            $id = $row['id'];
            $deadline = date('d.m.', strtotime($row['deadline']));
            $date = date('d.m.', strtotime($row['date']));
            $title = $row['title'];
            $out .= "<li><a href='{$code_href}termine.php?id={$id}' class='linkint'><b>{$deadline}</b>: Meldeschluss für {$title} vom {$date}</a></li>";
        }
        $out .= "</ul>";
        return $out;
    }
}
