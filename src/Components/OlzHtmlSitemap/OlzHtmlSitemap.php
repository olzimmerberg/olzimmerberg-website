<?php

namespace Olz\Components\OlzHtmlSitemap;

use Olz\Components\OlzSitemap\OlzSitemap;
use Olz\Components\Page\OlzFooter\OlzFooter;
use Olz\Components\Page\OlzHeader\OlzHeader;

class OlzHtmlSitemap extends OlzSitemap {
    public static $title = "Sitemap";
    public static $description = "Eine Übersicht über alle aktiven Inhalte der Website der OL Zimmerberg.";

    public function getHtml($args = []): string {
        $out = '';
        $out .= OlzHeader::render([
            'title' => self::$title,
            'description' => self::$description,
        ]);
        $out .= "<div class='content-full olz-html-sitemap'>";
        $out .= "<h1>Sitemap</h1>";

        $entries = $this->getEntries();
        foreach ($entries as $entry) {
            $out .= self::getEntry($entry);
        }

        $out .= "</div>";
        $out .= OlzFooter::render();
        return $out;
    }

    private static function getEntry($entry) {
        $url = $entry['url'];
        $title = $entry['title'];
        $description = $entry['description'];
        $level = $entry['level'];
        return <<<ZZZZZZZZZZ
        <div class="entry level-{$level}">
            <a href="{$url}">
                <span class="title">{$title}</span><br />
                <span class="description">{$description}</span>
            </a>
        </div>
        ZZZZZZZZZZ;
    }
}
