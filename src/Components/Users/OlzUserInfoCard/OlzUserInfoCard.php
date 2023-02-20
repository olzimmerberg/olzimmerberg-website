<?php

namespace Olz\Components\Users\OlzUserInfoCard;

use Olz\Utils\AuthUtils;
use Olz\Utils\EnvUtils;

class OlzUserInfoCard {
    public static function render($args = []) {
        $user = $args['user'];

        require_once __DIR__.'/../../../../_/admin/olz_functions.php';

        $auth_utils = AuthUtils::fromEnv();
        $env_utils = EnvUtils::fromEnv();
        $code_href = $env_utils->getCodeHref();
        $data_href = $env_utils->getDataHref();
        $data_path = $env_utils->getDataPath();

        $image_base_path = "img/users/{$user->getId()}";
        $initials = strtoupper($user->getFirstName()[0].$user->getLastName()[0]);
        $img_html = "<img src='{$code_href}icns/user.php?initials={$initials}' alt='' class='image'>";
        if (is_file("{$data_path}{$image_base_path}.jpg")) {
            $img_html = "<img src='{$data_href}{$image_base_path}.jpg' alt='' class='image'>";
        }

        $out = "<div class='olz-user-info-card'>";
        $out .= "<div class='image-container'>{$img_html}</div>";
        $out .= "<div class='name-container'>{$user->getFullName()}</div>";
        // $out .= ($row["adresse"] ? "<br>".$row["adresse"] : "");
        // $out .= ($row["tel"] ? "<br>Tel. ".$row["tel"] : "");
        $has_official_email = $auth_utils->hasPermission('user_email', $user);
        $email_html = '';
        if ($has_official_email) {
            $email = $user->getUsername().'@olzimmerberg.ch';
            $email_html = olz_mask_email($email, "Email", "");
        } else {
            $email_html = ($user->getEmail() ? olz_mask_email($user->getEmail(), "Email", "") : '');
        }
        if ($email_html) {
            $out .= "<div class='email-container'>{$email_html}</div>";
        }
        $out .= "</div>";
        return $out;
    }
}
