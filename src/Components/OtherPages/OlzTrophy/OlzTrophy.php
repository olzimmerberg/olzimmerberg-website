<?php

namespace Olz\Components\OtherPages\OlzTrophy;

use Olz\Components\Common\OlzComponent;
use Olz\Components\Common\OlzLocationMap\OlzLocationMap;
use Olz\Components\Page\OlzFooter\OlzFooter;
use Olz\Components\Page\OlzHeader\OlzHeader;

class OlzTrophy extends OlzComponent {
    public function getHtml($args = []): string {
        $env_utils = $this->envUtils();
        $code_href = $env_utils->getCodeHref();
        $data_path = $env_utils->getDataPath();

        $out = '';

        $host = str_replace('www.', '', $_SERVER['HTTP_HOST']);
        $canonical_url = "https://{$host}{$code_href}fuer_einsteiger";

        $etappen = [
            ["Dienstag, 18.04.2023", "Starts: 18:00 &ndash; 19:30", "Hirzel", "Schulhaus Heerenrainli", 688540, 230129,
                "?",
                "?",
                "Keine, im Freien; WC vorhanden (?)",
                false,
                "<div style='color:red;'>rot <i>(schwierig, 3-4km)</i></div><div style='color:blue;'>blau <i>(einfach, 2-3km)</i></div><div style='color:green;'>grün <i>(einfach, 1-2km)</i></div>",
                "gratis",
                "2023-trophy-hirzel",
                6288,
                "",
            ],
            ["Mittwoch, 10.05.2023", "Starts: 18:00 &ndash; 19:30", "Irchelpark", "Universität UZH Irchel", 683492, 250294,
                "?",
                "?",
                "Keine, im Freien; WC vorhanden (?)",
                false,
                "<div style='color:red;'>rot <i>(schwierig, 3-4km)</i></div><div style='color:blue;'>blau <i>(einfach, 2-3km)</i></div><div style='color:green;'>grün <i>(einfach, 1-2km)</i></div>",
                "gratis",
                "2023-trophy-irchel",
                6293,
                "",
            ],
            ["Dienstag, 06.06.2023", "Starts: 18:00 &ndash; 19:30", "Richterswil", "Jugendherberge Richterswil", 695990, 229630,
                "Bahnhof Richterswil",
                "In der Tiefgarage auf dem Horn",
                "Keine, im Freien; WC vorhanden.",
                "/files/termine/6115/001.pdf?modified=1654330702",
                "<div style='color:red;'>rot <i>(schwierig, 3-4km)</i></div><div style='color:blue;'>blau <i>(einfach, 2-3km)</i></div><div style='color:green;'>grün <i>(einfach, 1-2km)</i></div>",
                "gratis",
                "2023-trophy-richterswil",
                6298,
                "",
            ],
            ["Freitag, 23.06.2023", "Starts: 18:00 &ndash; 19:30", "Zürich", "Lindenhof", 683235, 247495,
                "?",
                "?",
                "Keine, im Freien; WC vorhanden (?)",
                false,
                "<div style='color:red;'>rot <i>(schwierig, 3-4km)</i></div><div style='color:blue;'>blau <i>(einfach, 2-3km)</i></div><div style='color:green;'>grün <i>(einfach, 1-2km)</i></div>",
                "gratis",
                "2023-trophy-zuerich",
                6300,
                "",
            ],
            ["Mittwoch, 30.08.2023", "Starts: 17:00 &ndash; 19:00", "Wädenswil", "Schulhaus Glärnisch", 693000, 231830,
                "Bahnhof Wädenswil, 15 min zu Fuss",
                "Parkhaus Glärnisch beim Schulhaus",
                "Keine, im Freien; WC vorhanden",
                false,
                "<div style='color:red;'>A <i>(schwierig, ~5km)</i></div><div style='color:red;'>B <i>(mittel-schwer, ~4km)</i></div><div style='color:blue;'>C <i>(mittel-einfach, ~3km)</i></div><div style='color:green;'>D <i>(einfach, ~2km)</i></div>",
                "gratis",
                "2023-trophy-waedenswil",
                6280,
                "",
            ],
        ];

        $out .= OlzHeader::render([
            'title' => "Trophy",
            'description' => "Orientierungslauf-Mini-Wettkämpfe, offen für Alle, in den Dörfern und Städten unseres Vereinsgebiets organisiert durch die OL Zimmerberg.",
        ]);

        $out .= "<div class='content-full'>
        <form name='Formularl' method='post' action='{$code_href}trophy#id_edit".($_SESSION['id_edit'] ?? '')."' enctype='multipart/form-data'>
        <div>";

        $out .= <<<'ZZZZZZZZZZ'
        <style>
        td.trophy-map-container .olz-location-map-render {
            height: 300px;
        }
        </style>

        <h2 style='font-size:24px; border:0px; text-align:center;'>OL Zimmerberg Trophy 2023</h2>
        <p style='text-align:center; font-size:15px; max-width:600px; margin:0px auto;'>Kleine Abend-OLs für Jung und Alt, für Schülerinnen und Schüler, Familien, Paare, Hobbysportlerinnen und Hobbysportler &mdash; alleine oder im Team</p>
        <p style='text-align:center;'><i style='font-size:17px;'>Es sind keine speziellen Vorkenntnisse nötig.</i></p>
        <p style='text-align:center;'>Die Versicherung ist Sache der Teilnehmenden. Der Veranstalter lehnt, soweit gesetzlich zulässig, jede Haftung ab.</p>

        <h3 style='font-size:18px;'>Etappen</h3>
        ZZZZZZZZZZ;
        $out .= "<table>";
        for ($i = 0; $i < count($etappen); $i++) {
            $etappe = $etappen[$i];
            $out .= "<tr><td id='id".$etappe[13]."' style='padding:5px 0px;'><div><h4 style='font-size:18px;'>".$etappe[2]."</h4><table>
            <tr><td style='width:100px;'>Datum:</td><td><b>".$etappe[0]."</b></td></tr>
            <tr><td>Besammlung:</td><td>".$etappe[3]."</td></tr>
            <tr><td>Anmeldung:</td><td>".$etappe[1]."</td></tr>
            ".($etappe[14] ? "<tr><td></td><td>".$etappe[14]."</td></tr>" : "")."
            <tr><td>Kategorien:</td><td>".$etappe[10]."</td></tr>
            <tr><td>Kosten:</td><td>".$etappe[11]."</td></tr>
            <tr><td>öV:</td><td>".$etappe[6]."</td></tr>
            <tr><td>Parkplätze:</td><td>".$etappe[7]."</td></tr>
            <tr><td>Garderobe:</td><td>".$etappe[8]."</td></tr>
            <tr><td></td><td><a href='{$code_href}termine/".$etappe[13]."' class='linkint'>Termine-Eintrag</a>".($etappe[9] ? "</td></tr>
            <tr><td></td><td><a href='".$etappe[9]."' class='linkext'>weitere Infos</a>" : "").($etappe[12] && is_file("{$data_path}results/{$etappe[12]}.xml") ? "</td></tr>
            <tr><td></td><td><a href='/apps/resultate/?file=".$etappe[12].".xml' class='linkint'>Resultate</a>" : "")."</td></tr>
            </table></div></td><td style='width:40%; padding:5px 0px 5px 10px;' class='trophy-map-container'>".($etappe[4] != 0 ? OlzLocationMap::render([
                'xkoord' => $etappe[4],
                'ykoord' => $etappe[5],
                'zoom' => 13,
            ]) : "")."</td></tr>";
            if (isset($_SESSION['auth']) && ($_SESSION['auth'] ?? null) == 'all' && $etappe[12]) {
                if (isset($_FILES["resultate_upload_".$etappe[13]])) {
                    move_uploaded_file(
                        $_FILES["resultate_upload_".$etappe[13]]['tmp_name'],
                        "{$data_path}results/{$etappe[12]}.xml",
                    );
                }
                $out .= "<tr><td><b>Resultate hochladen</b><br><input type='file' name='resultate_upload_".$etappe[13]."' /><input type='submit' value='Abschicken' /></td><td>".json_encode($_FILES)."</td></tr>";
            }
        }
        $out .= "</table>";

        $out .= <<<'ZZZZZZZZZZ'
        <h3>Weitere Informationen</h3>
        <table style='max-width:600px; margin:0px auto;'>
        <!--<tr><td>Gesamtrangliste 2020:</td><td style='padding-left:10px;'><a href='https://docs.google.com/spreadsheets/d/19aXk_aJZ954Ub-vBBQkexAjIIK_LXlvSohZBB0C2bQc/edit#gid=0' class='linkext'>Gesamtrangliste (alle Etappen)</a></td></tr>-->
        <tr><td>Ausrüstung:</td><td style='padding-left:10px;'> Joggingdress und Joggingschuhe genügen.</td></tr>
        <tr><td>Trophy:</td><td style='padding-left:10px;'>Jeder Lauf ist eine eigene abgeschlossene Veranstaltung.<br>
            Zusammen bilden sie die OL Zimmerberg Trophy.</td></tr> 
        <tr><td>Preise:</td><td style='padding-left:10px;'>In allen Kategorien gibt es eine Einzelrangliste für jeden Lauf, dem Sieger gebührt Ruhm und Ehre.<br>
            Wer drei oder mehr Läufe absolviert, erhält am dritten Lauf einen Erinnerungspreis.</td></tr>
        <tr><td>Auskunft:</td><td style='padding-left:10px;'>Martin Gross, Kirchstrasse 7, 8805 Richterswil<br>
        044 784 59 77 / <script>olz.MailTo('martin.gross', 'olzimmerberg.ch', 'E-Mail', 'OL Zimmerberg Trophy');</script></td></tr>
        </table>
        ZZZZZZZZZZ;

        $out .= "</div>
        </form>
        </div>";

        $out .= OlzFooter::render();

        return $out;
    }
}
