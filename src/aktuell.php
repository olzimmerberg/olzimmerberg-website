<?php

if (!defined('CALLED_THROUGH_INDEX')) {
    global $db;
    require_once __DIR__.'/config/init.php';
    require_once __DIR__.'/config/database.php';
    require_once __DIR__.'/config/paths.php';

    session_start_if_cookie_set();

    require_once __DIR__.'/admin/olz_functions.php';

    require_once __DIR__.'/fields/BooleanField.php';
    require_once __DIR__.'/fields/IntegerField.php';
    require_once __DIR__.'/fields/StringField.php';
    require_once __DIR__.'/utils/client/HttpUtils.php';
    require_once __DIR__.'/utils/env/EnvUtils.php';
    $env_utils = EnvUtils::fromEnv();
    $logger = $env_utils->getLogsUtils()->getLogger(basename(__FILE__));
    $http_utils = HttpUtils::fromEnv();
    $http_utils->setLogger($logger);
    $http_utils->validateGetParams([
        new IntegerField('id', ['allow_null' => true]),
        new BooleanField('archiv', ['allow_null' => true]),
        new StringField('buttonaktuell', ['allow_null' => true]),
    ], $_GET);

    $html_title = "Aktuell";
    $article_metadata = "";
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $sql = "SELECT titel, datum, zeit FROM aktuell WHERE id='{$id}'";
        $res = $db->query($sql);
        if ($res->num_rows == 0) {
            $http_utils->dieWithHttpError(404);
        }
        while ($row = $res->fetch_assoc()) {
            $html_title = $row['titel'];
            $json_title = json_encode($html_title);
            $iso_date = $row['datum'].'T'.$row['zeit'];
            $json_iso_date = json_encode($iso_date);
            $images = [];
            $image_index = 1;
            while (true) {
                $fixed_width_index = str_pad("{$image_index}", 3, "0", STR_PAD_LEFT);
                $image_relative_path = "img/aktuell/{$id}/img/{$fixed_width_index}.jpg";
                if (!is_file("{$data_path}{$image_relative_path}")) {
                    break;
                }
                $images[] = "{$data_href}{$image_relative_path}";
                $image_index++;
            }
            $json_images = json_encode($images);
            $article_metadata = <<<ZZZZZZZZZZ
            <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "Article",
                "headline": {$json_title},
                "image": {$json_images},
                "datePublished": {$json_iso_date},
                "dateModified": {$json_iso_date}
            }
            </script>
            ZZZZZZZZZZ;
        }
    }

    require_once __DIR__.'/components/page/olz_header/olz_header.php';
    echo olz_header([
        'title' => $html_title,
        'description' => "Aktuelle Beiträge, Berichte von Anlässen und weitere Neuigkeiten von der OL Zimmerberg.",
        'additional_headers' => [
            $article_metadata,
        ],
    ]);
}

require_once __DIR__.'/file_tools.php';
require_once __DIR__.'/image_tools.php';

$db_table = 'aktuell';
$id = $_GET['id'] ?? null;

$button_name = 'button'.$db_table;
if (isset($_GET[$button_name])) {
    $_POST[$button_name] = $_GET[$button_name];
}
if (isset($_POST[$button_name])) {
    $_SESSION['edit']['db_table'] = $db_table;
}

$zugriff = ((($_SESSION['auth'] ?? null) == 'all') or (in_array($db_table, preg_split('/ /', $_SESSION['auth'] ?? '')))) ? '1' : '0';

echo "
<div id='content_rechts'>
<form name='Formularr' method='post' action='aktuell.php#id_edit".($_SESSION['id_edit'] ?? '')."' enctype='multipart/form-data'>
<div>";
include __DIR__.'/aktuell_r.php';
echo "</div>
</form>
</div>
<div id='content_mitte'>
<form name='Formularl' method='post' action='aktuell.php#id_edit".($_SESSION['id_edit'] ?? '')."' enctype='multipart/form-data'>";
include __DIR__.'/aktuell_l.php';
echo "</form>
</div>
";

if (!defined('CALLED_THROUGH_INDEX')) {
    require_once __DIR__.'/components/page/olz_footer/olz_footer.php';
    echo olz_footer();
}
