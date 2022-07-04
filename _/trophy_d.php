<?php

// =============================================================================
// Zeigt Informationen zur OLZ Trophy an.
// =============================================================================

require_once __DIR__.'/config/paths.php';

?>

<h2 style='font-size:24px; border:0px; text-align:center;'>OL Zimmerberg Trophy 2022</h2>
<p style='text-align:center; font-size:15px; max-width:600px; margin:0px auto;'>Kleine Abend-OLs für Jung und Alt, für Schülerinnen und Schüler, Familien, Paare, Hobbysportlerinnen und Hobbysportler &mdash; alleine oder im Team</p>
<p style='text-align:center;'><i style='font-size:17px;'>Es sind keine speziellen Vorkenntnisse nötig.</i></p>
<p style='text-align:center;'>Die Versicherung ist Sache der Teilnehmenden. Der Veranstalter lehnt, soweit gesetzlich zulässig, jede Haftung ab.</p>

<h3 style='font-size:18px;'>Etappen</h3>

<?php

$etappen = [
    ["Dienstag, 05.04.2022", "Starts: 17:30 &ndash; 19:00", "Thalwil", "Schulhaus Sonnenberg", 684630, 238650,
        "Bushaltestelle Thalwil Sonnenberg",
        "Kostenpflichtige Parkplätze vorhanden",
        "Keine, im Freien; WC vorhanden",
        false,
        "<div style='color:red;'>rot <i>(schwierig, 3-4km)</i></div><div style='color:blue;'>blau <i>(einfach, 2-3km)</i></div><div style='color:green;'>grün <i>(einfach, 1-2km)</i></div>",
        "gratis",
        "2022-trophy-thalwil",
        6113,
        "",
    ],
    ["Dienstag, 24.05.2022", "Starts: 17:30 &ndash; 19:00", "Oberrieden", "Schule Tannenbach, Horgen", 686660, 235745,
        "Bushaltestelle Horgen Widmerheim",
        "Parkplätze beim Spital, keine bei der Schule",
        "Keine, im Freien; WC vorhanden (?)",
        false,
        "<div style='color:red;'>rot <i>(schwierig, 3-4km)</i></div><div style='color:blue;'>blau <i>(einfach, 2-3km)</i></div><div style='color:green;'>grün <i>(einfach, 1-2km)</i></div>",
        "gratis",
        "2022-trophy-oberrieden",
        6114,
        "",
    ],
    ["Dienstag, 14.06.2022", "Starts: 18:00 &ndash; 19:30", "Küsnacht", "Kantonsschule Küsnacht", 686480, 241335,
        "Bahnhof Küsnacht ZH",
        "?",
        "Keine, im Freien; WC vorhanden (?)",
        "/files/termine/6115/001.pdf?modified=1654330702",
        "<div style='color:red;'>rot <i>(schwierig, 3-4km)</i></div><div style='color:blue;'>blau <i>(einfach, 2-3km)</i></div><div style='color:green;'>grün <i>(einfach, 1-2km)</i></div>",
        "gratis",
        "2022-trophy-kuesnacht",
        6115,
        "",
    ],
    ["Dienstag, 16.08.2022", "Starts: 18:00 &ndash; 19:30", "Au ZH", "Schulhaus Ort", 691423, 233407,
        "Bahnhof Au ZH",
        "?",
        "Keine, im Freien; WC vorhanden (?)",
        false,
        "<div style='color:red;'>rot <i>(schwierig, 3-4km)</i></div><div style='color:blue;'>blau <i>(einfach, 2-3km)</i></div><div style='color:green;'>grün <i>(einfach, 1-2km)</i></div>",
        "gratis",
        "2022-trophy-au",
        6116,
        "",
    ],
    ["Mittwoch, 31.08.2022", "Starts: 17:00 &ndash; 19:00", "Richterswil", "Schulhaus Töss", 695698, 229281,
        "Bahnhof Richterswil",
        "",
        "Keine, im Freien; WC vorhanden (?)",
        false,
        "<div style='color:red;'>A <i>(schwierig, ~5km)</i></div><div style='color:red;'>B <i>(mittel-schwer, ~4km)</i></div><div style='color:blue;'>C <i>(mittel-einfach, ~3km)</i></div><div style='color:green;'>D <i>(einfach, ~2km)</i></div>",
        "gratis",
        "2022-trophy-richti",
        6117,
        "",
    ],
];

$gemeindeduell = [
];

echo "<table>";
for ($i = 0; $i < count($etappen); $i++) {
    $etappe = $etappen[$i];
    echo "<tr><td id='id".$etappe[13]."' style='padding:5px 0px;'><div><h4 style='font-size:18px;'>".$etappe[2]."</h4><table>
    <tr><td style='width:100px;'>Datum:</td><td><b>".$etappe[0]."</b></td></tr>
    <tr><td>Besammlung:</td><td>".$etappe[3]."</td></tr>
    <tr><td>Anmeldung:</td><td>".$etappe[1]."</td></tr>
    ".($etappe[14] ? "<tr><td></td><td>".$etappe[14]."</td></tr>" : "")."
    <tr><td>Kategorien:</td><td>".$etappe[10]."</td></tr>
    <tr><td>Kosten:</td><td>".$etappe[11]."</td></tr>
    <tr><td>öV:</td><td>".$etappe[6]."</td></tr>
    <tr><td>Parkplätze:</td><td>".$etappe[7]."</td></tr>
    <tr><td>Garderobe:</td><td>".$etappe[8]."</td></tr>
    <tr><td></td><td><a href='termine.php#id".$etappe[13]."' class='linkint'>Termine-Eintrag</a>".($etappe[9] ? "</td></tr>
    <tr><td></td><td><a href='".$etappe[9]."' class='linkext'>weitere Infos</a>" : "").($etappe[12] && is_file("{$data_path}results/{$etappe[12]}.xml") ? "</td></tr>
    <tr><td></td><td><a href='/apps/resultate/?file=".$etappe[12].".xml' class='linkint'>Resultate</a>" : "")."</td></tr>
    </table></div></td><td style='width:20%; padding:5px 0px 5px 10px;'>".($etappe[4] != 0 ? "<script>document.write(getMapHtml(".$etappe[4].",".$etappe[5]."))</script>" : "")."</td></tr>";
    if (isset($_SESSION['auth']) && ($_SESSION['auth'] ?? null) == 'all' && $etappe[12]) {
        if (isset($_FILES["resultate_upload_".$etappe[13]])) {
            move_uploaded_file(
                $_FILES["resultate_upload_".$etappe[13]]['tmp_name'],
                "{$data_path}results/{$etappe[12]}.xml",
            );
        }
        echo "<tr><td><b>Resultate hochladen</b><br><input type='file' name='resultate_upload_".$etappe[13]."' /><input type='submit' value='Abschicken' /></td><td>".json_encode($_FILES)."</td></tr>";
    }
}
echo "</table>";

?>

<h3>Weitere Informationen</h3>
<table style='max-width:600px; margin:0px auto;'>
<!--<tr><td>Gesamtrangliste 2020:</td><td style='padding-left:10px;'><a href='https://docs.google.com/spreadsheets/d/19aXk_aJZ954Ub-vBBQkexAjIIK_LXlvSohZBB0C2bQc/edit#gid=0' class='linkext'>Gesamtrangliste (alle Etappen)</a></td></tr>-->
<tr><td>Ausrüstung:</td><td style='padding-left:10px;'> Joggingdress und Joggingschuhe genügen.</td></tr>
<tr><td>Trophy:</td><td style='padding-left:10px;'>Jeder Lauf ist eine eigene abgeschlossene Veranstaltung.<br>
    Zusammen bilden sie die OL Zimmerberg Trophy.</td></tr>
<!--<tr><td>Gemeindeduell:</td><td style='padding-left:10px;'>Jeder Teilnehmende sammelt pro Lauf einen Punkt für seine Gemeinde.<br>
    Die Gemeinde mit den meisten Punkten erhält am Ende einen Wanderpreis!<br><br>
    <b>Rangliste (vorläufig)</b><br>
    <table><tr><th style='width:1%;'>Gemeinde</th><th style='width:40px;'>&nbsp;</th><th style='width:auto;'>Starts</th></tr><?php
    arsort($gemeindeduell);
    foreach ($gemeindeduell as $k => $v) {
        if (strlen($k) > 0) {
            echo "<tr><td>".$k."</td><td></td><td>".$v."</td></tr>";
        }
    }
    ?></table><br><br></td></tr>-->
<tr><td>Preise:</td><td style='padding-left:10px;'>In allen Kategorien gibt es eine Einzelrangliste für jeden Lauf, dem Sieger gebührt Ruhm und Ehre.<br>
    Wer drei oder mehr Läufe absolviert, erhält am dritten Lauf einen Erinnerungspreis.</td></tr>
<tr><td>Auskunft:</td><td style='padding-left:10px;'>Martin Gross, Kirchstrasse 7, 8805 Richterswil<br>
044 784 59 77 / <script>MailTo('martin.gross', 'olzimmerberg.ch', 'E-Mail', 'OL Zimmerberg Trophy');</script></td></tr>
</table>
