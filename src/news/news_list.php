<?php

require_once __DIR__.'/../components/common/olz_posting_list_item/olz_posting_list_item.php';
require_once __DIR__.'/components/olz_news_filter/olz_news_filter.php';
require_once __DIR__.'/components/olz_news_list_item/olz_news_list_item.php';
require_once __DIR__.'/model/NewsEntry.php';
require_once __DIR__.'/utils/NewsFilterUtils.php';

$news_utils = NewsFilterUtils::fromEnv();
$current_filter = json_decode($_GET['filter'] ?? '{}', true);

if (!$news_utils->isValidFilter($current_filter)) {
    $enc_json_filter = urlencode(json_encode($news_utils->getDefaultFilter()));
    $http_utils->redirect("?filter={$enc_json_filter}", 308);
}

$is_not_archived = $news_utils->isFilterNotArchived($current_filter);
$allow_robots = $is_not_archived;

echo olz_header([
    'title' => "Aktuell",
    'description' => "Aktuelle Beiträge, Berichte von Anlässen und weitere Neuigkeiten von der OL Zimmerberg.",
    'norobots' => !$allow_robots,
    'entry_points' => ['news', 'common', 'vendor'],
]);

echo "<div id='content_rechts'>";
echo "<h2>Filter</h2>";
echo olz_news_filter([]);
echo "</div>";
echo "<div id='content_mitte'>";
echo "<form method='post' action='aktuell.php#id_edit".($_SESSION['id_edit'] ?? '')."' enctype='multipart/form-data'>";

// -------------------------------------------------------------
// DATENSATZ EDITIEREN
if ($zugriff) {
    $functions = [
        'neu' => 'Neuer Eintrag',
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
        'undo' => 'undo',
    ];
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
    echo <<<'ZZZZZZZZZZ'
    <div class='feature news'>
        <button
            id='create-news-button'
            class='btn btn-primary'
            onclick='return initOlzEditNewsModal()'
        >
            <img src='icns/new_white_16.svg' class='noborder' />
            Neuer Eintrag
        </button>
        &nbsp; &lt;-- Neu! &mdash; Falls es nicht funktioniert, "Neuer Eintrag" unten (grün) klicken.
    </div>
    ZZZZZZZZZZ;
    echo "<div class='buttonbar'>\n".olz_buttons("button".$db_table, [["Neuer Eintrag", "0"]], "")."</div>";
}

if (($do ?? null) == 'vorschau') {
    $id = $_SESSION[$db_table."id"];
    require_once __DIR__.'/components/olz_news_article/olz_news_article.php';
    echo olz_news_article([
        'id' => $id,
        'row' => $vorschau,
        'can_edit' => $zugriff,
        'is_preview' => true,
    ]);
    echo "<hr /><br/><br/>";
}

$filter_where = $news_utils->getSqlFromFilter($current_filter);
$sql = <<<ZZZZZZZZZZ
SELECT
    id,
    datum,
    zeit,
    titel,
    text,
    image_ids
FROM aktuell n
WHERE
    {$filter_where}
    AND n.on_off='1'
    AND n.typ NOT LIKE 'box%'
ORDER BY datum DESC, zeit DESC
ZZZZZZZZZZ;
$res = $db->query($sql);

while ($row = $res->fetch_assoc()) {
    // TODO: Directly use doctrine to run the DB query.
    $news_entry = new NewsEntry();
    $news_entry->setDate($row['datum']);
    $news_entry->setTitle($row['titel']);
    $news_entry->setTeaser($row['text']);
    $news_entry->setId($row['id']);
    $news_entry->setImageIds(json_decode($row['image_ids'] ?? '[]', true));

    echo olz_news_list_item(['news_entry' => $news_entry]);
}

echo "</form>";
echo "</div>";
