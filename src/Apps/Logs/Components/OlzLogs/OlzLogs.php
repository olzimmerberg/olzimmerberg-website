<?php

namespace Olz\Apps\Logs\Components\OlzLogs;

use Olz\Apps\Logs\Metadata;
use Olz\Apps\Logs\Utils\LogsDefinitions;
use Olz\Components\Apps\OlzNoAppAccess\OlzNoAppAccess;
use Olz\Components\Common\OlzComponent;
use Olz\Components\Page\OlzFooter\OlzFooter;
use Olz\Components\Page\OlzHeader\OlzHeader;

class OlzLogs extends OlzComponent {
    public function getHtml($args = []): string {
        require_once __DIR__.'/../../../../../_/config/init.php';
        require_once __DIR__.'/../../../../../_/admin/olz_functions.php';

        $out = '';

        $out .= OlzHeader::render([
            'back_link' => "{$code_href}apps/",
            'title' => "Logs",
            'norobots' => true,
        ]);

        $user = $this->authUtils()->getCurrentUser();
        $metadata = new Metadata();

        $out .= <<<'ZZZZZZZZZZ'
        <style>
        .menu-container {
            max-width: none;
        } 
        .site-container {
            max-width: none;
        }
        </style>
        ZZZZZZZZZZ;

        $out .= "<div class='content-full olz-logs'>";
        if ($user && $user->getPermissions() == 'all') {
            $iso_now = $this->dateUtils()->getIsoNow();
            $esc_now = json_encode($iso_now);
            $channels_data = [];
            foreach (LogsDefinitions::getLogsChannels() as $channel) {
                $channels_data[$channel::getId()] = $channel::getName();
            }
            $esc_channels = json_encode($channels_data);
            $out .= <<<ZZZZZZZZZZ
                <script>
                    window.olzLogsNow = {$esc_now};
                    window.olzLogsChannels = {$esc_channels};
                </script>
                <div id='react-root'></div>
            ZZZZZZZZZZ;
        } else {
            $out .= OlzNoAppAccess::render([
                'app' => $metadata,
            ]);
        }
        $out .= "</div>";

        $out .= $metadata->getJsCssImports();
        $out .= OlzFooter::render();

        return $out;
    }
}
