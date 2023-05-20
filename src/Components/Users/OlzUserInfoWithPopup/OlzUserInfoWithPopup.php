<?php

namespace Olz\Components\Users\OlzUserInfoWithPopup;

use Olz\Components\Common\OlzComponent;
use Olz\Components\Users\OlzPopup\OlzPopup;
use Olz\Components\Users\OlzUserInfoCard\OlzUserInfoCard;

class OlzUserInfoWithPopup extends OlzComponent {
    public function getHtml($args = []): string {
        $user = $args['user'];
        $mode = $args['mode'] ?? 'name';

        $code_href = $this->envUtils()->getCodeHref();
        $data_href = $this->envUtils()->getDataHref();
        $data_path = $this->envUtils()->getDataPath();

        if ($mode == 'name') {
            $trigger = "<div class='olz-user-info-with-popup'>{$user->getFullName()}</div>";
            $popup = OlzUserInfoCard::render(['user' => $user]);
            return OlzPopup::render(['trigger' => $trigger, 'popup' => $popup]);
        }
        if ($mode == 'name_picture') {
            $image_base_path = "img/users/{$user->getId()}";
            $img_html = "<img src='{$data_href}assets/icns/user.jpg' alt=''>";
            if (is_file("{$data_path}{$image_base_path}.jpg")) {
                $img_html = "<img src='{$data_href}{$image_base_path}.jpg' alt=''>";
                if (is_file("{$data_path}{$image_base_path}@2x.jpg")) {
                    $img_html = <<<ZZZZZZZZZZ
                    <img
                        srcset='
                            {$data_href}{$image_base_path}@2x.jpg 2x,
                            {$data_href}{$image_base_path}.jpg 1x
                        '
                        src='{$data_href}{$image_base_path}.jpg'
                        alt=''
                    >
                    ZZZZZZZZZZ;
                }
            }
            $trigger = "<div class='olz-user-info-with-popup'>{$img_html}<br>{$user->getFullName()}</div>";
            $popup = OlzUserInfoCard::render(['user' => $user]);
            return OlzPopup::render(['trigger' => $trigger, 'popup' => $popup]);
        }
        return "olz_user_info_with_popup: mode {$mode} nicht definiert";
    }
}
