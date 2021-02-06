<?php

if (!defined('CALLED_THROUGH_INDEX')) {
    global $db;
    require_once __DIR__.'/config/init.php';
    require_once __DIR__.'/config/database.php';

    session_start();

    require_once __DIR__.'/admin/olz_functions.php';

    $id = intval($id);
    $sql = "SELECT titel FROM galerie WHERE id='{$id}'";
    $res = $db->query($sql);
    $html_title = "Galerie";
    while ($row = $res->fetch_assoc()) {
        $html_title = $row['titel'];
    }

    include __DIR__.'/components/page/olz_header/olz_header.php';
    echo olz_header([
        'title' => $html_title,
        'description' => "Bilder und Videos von Anlässen der OL Zimmerberg.",
    ]);
}

require_once __DIR__.'/image_tools.php';

$db_table = 'galerie';
$id = $_GET['id'];

$button_name = 'button'.$db_table;
if (isset($_GET[$button_name])) {
    $_POST[$button_name] = $_GET[$button_name];
}
if (isset($_POST[$button_name])) {
    $_SESSION['edit']['db_table'] = $db_table;
}

echo "
<div id='content_rechts'>
<form name='Formularr' method='post' action='galerie.php#id_edit".$_SESSION['id_edit']."' enctype='multipart/form-data'>
<div>";
include __DIR__.'/galerie_r.php';
echo "</div>
</form>
</div>
<div id='content_mitte'>
<form name='Formularl' method='post' action='galerie.php#id_edit".$_SESSION['id_edit']."' enctype='multipart/form-data'>";
include __DIR__.'/galerie_l.php';
echo "</form>
</div>
";

if (!defined('CALLED_THROUGH_INDEX')) {
    include __DIR__.'/components/page/olz_footer/olz_footer.php';
}
