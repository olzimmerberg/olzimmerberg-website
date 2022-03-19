<?php

// =============================================================================
// Aktuelle Berichte von offiziellen Vereinsorganen.
// =============================================================================

use Doctrine\Common\Collections\Criteria;

require_once __DIR__.'/../config/paths.php';
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/date.php';
require_once __DIR__.'/../config/doctrine_db.php';
require_once __DIR__.'/../model/index.php';
require_once __DIR__.'/model/NewsEntry.php';
require_once __DIR__.'/utils/NewsFilterUtils.php';

$article_metadata = "";
try {
    require_once __DIR__.'/components/olz_article_metadata/olz_article_metadata.php';
    $article_metadata = olz_article_metadata($id);
} catch (Exception $exc) {
    $http_utils->dieWithHttpError(404);
}

$news_utils = NewsFilterUtils::fromEnv();
$news_repo = $entityManager->getRepository(NewsEntry::class);
$is_not_archived = $news_utils->getIsNotArchivedCriteria();
$criteria = Criteria::create()
    ->where(Criteria::expr()->andX(
        $is_not_archived,
        Criteria::expr()->eq('id', $id),
    ))
    ->setFirstResult(0)
    ->setMaxResults(1)
;
$news_entries = $news_repo->matching($criteria);
$num_news_entries = $news_entries->count();
$no_robots = $num_news_entries !== 1;

echo olz_header([
    'title' => "Aktuell",
    'description' => "Aktuelle Beiträge, Berichte von Anlässen und weitere Neuigkeiten von der OL Zimmerberg.",
    'norobots' => $no_robots,
    'additional_headers' => [
        $article_metadata,
    ],
    'entry_points' => ['news', 'common', 'vendor'],
]);

$button_name = 'button'.$db_table;
if (isset($_GET[$button_name])) {
    $_POST[$button_name] = $_GET[$button_name];
}
if (isset($_POST[$button_name])) {
    $_SESSION['edit']['db_table'] = $db_table;
}

$zugriff = ((($_SESSION['auth'] ?? null) == 'all') or (in_array($db_table, preg_split('/ /', $_SESSION['auth'] ?? '')))) ? '1' : '0';

$sql = "SELECT * FROM {$db_table} WHERE (id = '{$id}') ORDER BY datum DESC";
$result = $db->query($sql);
$row = $result->fetch_assoc();
$pretty_date = $_DATE->olzDate("tt.mm.jjjj", $row['datum']);
$pretty_author = $row['autor'];

$id_edit = $_SESSION['id_edit'] ?? ''; // TODO: Entfernen?
echo <<<ZZZZZZZZZZ
<div id='content_rechts' class='optional'>
    <div style='padding:4px 3px 10px 3px;'>
        <b>Datum: </b>{$pretty_date}<br />
        <b>Autor: </b>{$pretty_author}
    </div>
</div>
<div id='content_mitte'>
<form name='Formularl' method='post' action='aktuell.php?id={$id}#id_edit{$id_edit}' enctype='multipart/form-data'>
ZZZZZZZZZZ;

// -------------------------------------------------------------
// DATENSATZ EDITIEREN
if ($zugriff) {
    $functions = ['neu' => 'Neuer Eintrag',
        'edit' => 'Bearbeiten',
        'abbruch' => 'Abbrechen',
        'vorschau' => 'Vorschau',
        'save' => 'Speichern',
        'delete' => 'Löschen',
        'start' => 'start',
        'upload' => 'Upload',
        'deletebild1' => 'BILD 1 entfernen',
        'deletebild2' => 'BILD 2 entfernen',
        'deletebild3' => 'BILD 3 entfernen',
        'undo' => 'undo', ];
} else {
    $functions = [];
}
$function = array_search($_POST[$button_name] ?? null, $functions);
if ($function != "") {
    include __DIR__.'/../admin/admin_db.php';
}
if (($_SESSION['edit']['table'] ?? null) == $db_table) {
    $db_edit = "1";
} else {
    $db_edit = "0";
}

// -------------------------------------------------------------
// MENÜ
if ($zugriff and ($db_edit == '0')) {
    echo "<div class='buttonbar'>\n".olz_buttons("button".$db_table, [["Neuer Eintrag", "0"]], "")."</div>";
}

// -------------------------------------------------------------
// AKTUELL - VORSCHAU
if (($db_edit == "0") or (($do ?? null) == 'vorschau')) {
    require_once __DIR__.'/components/olz_news_article/olz_news_article.php';
    echo olz_news_article([
        'id' => $id,
        'can_edit' => $zugriff,
        'is_preview' => (($do ?? null) == 'vorschau'),
    ]);
}

echo "</form>
</div>";
