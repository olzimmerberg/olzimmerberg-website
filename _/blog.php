<?php

use Olz\Components\Page\OlzFooter\OlzFooter;
use Olz\Components\Page\OlzHeader\OlzHeader;
use Olz\Utils\HttpUtils;
use Olz\Utils\LogsUtils;
use PhpTypeScriptApi\Fields\FieldTypes;

require_once __DIR__.'/config/init.php';

session_start_if_cookie_set();

require_once __DIR__.'/admin/olz_functions.php';

$logger = LogsUtils::fromEnv()->getLogger(basename(__FILE__));
$http_utils = HttpUtils::fromEnv();
$http_utils->setLogger($logger);
$http_utils->validateGetParams([
    'id' => new FieldTypes\IntegerField(['allow_null' => true]),
    'buttonblog' => new FieldTypes\StringField(['allow_null' => true]),
], $_GET);

echo OlzHeader::render([
    'title' => "Leistungssport",
    'description' => "Beiträge der Spitzensportler und der Leistungssport-Trainingsgruppe \"Team Gold\" der OL Zimmerberg.",
]);

$db_table = 'blog';
$def_folder = 'downloads';

$button_name = 'button'.$db_table;
if (isset($_GET[$button_name])) {
    $_POST[$button_name] = $_GET[$button_name];
    $id = $_GET['id'] ?? null;
}
if (isset($_POST[$button_name])) {
    $_SESSION['edit']['db_table'] = $db_table;
}

echo "
<div class='content-right'>
<form name='Formularr' method='post' action='blog.php#id_edit".($_SESSION['id_edit'] ?? '')."' enctype='multipart/form-data'>
<div>";
include __DIR__.'/blog_r.php';
echo "</div>
</form>
</div>
<div class='content-middle'>
<form name='Formularl' method='post' action='blog.php#id_edit".($_SESSION['id_edit'] ?? '')."' enctype='multipart/form-data'>";
include __DIR__.'/blog_l.php';
echo "</form>
</div>
";

echo OlzFooter::render();
