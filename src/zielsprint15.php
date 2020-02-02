<?php

//echo "<p style='color:#ff0000;'>Wir arbeiten daran. Das POLITT-Büro</p>";

echo "<style type='text/css'>
sup, sub {
    font-size:0.8em;
    height: 0;
    line-height: 1;
    vertical-align: baseline;
    position: relative;
}

sup {
    bottom: .8ex;
}

sub {
    top: .5ex;
}
</style>
<script type='text/javascript'>
function toggle(id) {
    var elem = document.getElementById(id);
    if (elem.style.display==\"none\") {
        elem.style.display = \"table-row\";
    } else {
        elem.style.display = \"none\";
    }
}
</script>
<a name='cup_top'></a><h2>Zieleinlauf-Cup 2015 <span style='font-size:10px; font-weight:normal; padding-left:30px;'><a href='pdf/OLZ-3-4-Meisterschaft-2014.pdf' onclick='alert(&quot;Das Reglement ist noch nicht online&quot;); return false;'>Reglement</a></span><span style='font-size:10px; font-weight:normal; padding-left:30px;'><script>document.write(MailTo(\"simon.hatt\",\"olzimmerberg.ch\",\"Motzen\",\"3-4-Cup 2014\"));</script></span></h2>";

if ($_SESSION["auth"]=="all") {
    if (isset($_GET["year"]) && isset($_GET["event"])) {
        echo "<a href='?year=".$_GET["year"]."'>Zurück</a><br>";
        if ($_GET["action"]=="reevaluate") {
			$sql = "SELECT id from solvlaufe WHERE jahr LIKE '".intval($_GET["year"])."' AND name LIKE '".mysqli_real_escape_string($_GET["event"])."'";
            $result_l = $db->query($sql);
            $num_l = mysqli_num_rows($result_l);
            if ($num_l==1) {
                $row_l = mysqli_fetch_array($result_l);
                $db->query("DELETE from solvlaufe WHERE id='".$row_l["id"]."'");
                $db->query("DELETE from solvresults WHERE lauf='".$row_l["id"]."'");
            }
        }
		$sql = "SELECT id from solvlaufe WHERE jahr LIKE '".intval($_GET["year"])."' AND name LIKE '".mysqli_real_escape_string($_GET["event"])."'";
        $result_l = $db->query($sql);
        $num_l = mysqli_num_rows($result_l);
        if ($num_l==1) {
            $row_l = mysqli_fetch_array($result_l);
            $laufid = $row_l["id"];
            echo "LAUF FOUND: ".$laufid."<br>";
        } else {
            $db->query("INSERT solvlaufe SET jahr='".intval($_GET["year"])."', name='".mysqli_real_escape_string($_GET["event"])."'");
            $laufid = mysqli_insert_id();
            echo "LAUF INSERTED: ".$laufid."<br>";
            $url = "http://www.o-l.ch/cgi-bin/results?type=rang&year=".intval($_GET["year"])."&event=".urlencode( $_GET["event"])."&kat=--&kind=club&club=zimmerberg&zwizt=1";
            echo "<input type='text' value='".str_replace("'","&#39;",$url)."' style='width:100%;' readonly='readonly'><br>";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $file = curl_exec($ch);
            curl_close($ch);
            
            // PARSE
            $pres = array();
            $pre = "";
            $ltext = "";
            $text = "";
            $num = strlen($file);
            for ($i=0; $i<$num; $i++) {
                if ($file[$i]=="<") {
                    $intext = false;
                    if (2<=strlen($text)) {
                        $ltext = $text;
                        $text = "";
                    }
                }
                if (substr($file,$i,6)=="</pre>") {
                    $inpre = false;
                    if (2<=strlen($ltext)) $pres[$ltext] = $pre;
                    else echo "ERROR: Kategorie nicht angegeben";
                }
                if ($inpre) { $pre .= $file[$i]; }
                else if ($intext) { $text .= $file[$i]; }
                if (substr($file,$i,5)=="<pre>") {
                    $inpre = true;
                    $pre = "";
                    $i += 4;
                }
                if ($file[$i]==">") {
                    $intext = true;
                    $text = "";
                }
            }
            $lauf = array();
            $keys = array_keys($pres);
            $num = count($keys);
            for ($i=0; $i<$num; $i++) {
                $kat = array();
                $pre = $pres[$keys[$i]];
                $res = preg_match("/^\s*\(\s*([0-9\.]+)\s*km\s*,\s*([0-9]+)\s*m\s*,\s*([0-9]+)\s*Po\.\s*\)\s*([0-9]+)\s*Teilnehmer/", $pre, $matches);
                if ($res) {
                    $kat["strecke"] = floatval($matches[1])*1000;
                    $kat["hoehe"] = intval($matches[2]);
                    $kat["numposten"] = intval($matches[3]);
                    $kat["numteilnehmer"] = intval($matches[4]);
                } else {
                    echo "ERROR: preg_match fail";
                }
                $pre = substr($pre, strlen($matches[0]));
                $laeufer = array();
                while (true) {
                    $l = array();
                    $res = preg_match("/^\s*(\<b\>)?\s*(\s*([0-9]+)\.)?\s*(((?![\s]{2,})[^0-9])+)\s+([0-9]{2}|)\s+(((?![\s]{2,})[\S ])+)\s+((((?![\s]{2,})[\S ])+)\s+)?([0-9\:]+|[A-Za-z\.\/ ]+)\s*(\<\/b\>\s*)?([^\<]+)/", $pre, $matches);
                    $pre = substr($pre, strlen($matches[0]));
                    if (!$res) break;
                    $l["rang"] = intval($matches[3]);
                    $l["name"] = $matches[4];
                    $l["jahrgang"] = $matches[6];
                    $l["wohnort"] = $matches[7];
                    $l["club"] = $matches[10];
                    $l["zeit"] = zeitins($matches[12]);
                    $posten = array();
                    $zwzt = $matches[14];
                    $status = array(0, 1, 1);
                    while (true) {
                        if ($status[0]==0) {
                            $res = preg_match("/^\s*$/", $zwzt);
                            if ($res) break;
                            $nr = $status[1];
                            $res = preg_match("/^\ *((".intval($status[2]).")\.\ |Zi|)\ *([0-9\.\:\-]+)\ *\(([0-9]*)\)/", $zwzt, $matches);
                            if ($res) {
                                $zwzt = substr($zwzt, strlen($matches[0]));
                                if (!isset($posten[$nr])) $posten[$nr] = array();
                                $posten[$status[2]]["nr"] = $matches[2];
                                $posten[$status[2]]["gzeit"] = zeitins($matches[3]);
                                $posten[$status[2]]["grang"] = intval($matches[4]);
                                $status[1] += 1;
                                $status[2] += 1;
                            } else {
                                $res = preg_match("/^\ *\n/", $zwzt, $matches);
                                if ($res) {
                                    $zwzt = substr($zwzt, strlen($matches[0]));
                                    $status[0] = 1;
                                    $status[1] = $status[1]-($status[1]-2)%5-1;
                                } else {
                                    echo $l["name"]."break 0, <pre>".json_encode($zwzt)."</pre><br>";
                                    break;
                                }
                            }
                        }
                        if ($status[0]==1) {
                            $nr = $status[1];
                            $res = preg_match("/^\ *([0-9]+|Zi)\ *([0-9\.\:\-]+)\ *\(([0-9]*)\)/", $zwzt, $matches);
                            if ($res) {
                                $zwzt = substr($zwzt, strlen($matches[0]));
                                if (!isset($posten[$nr])) $posten[$nr] = array();
                                $posten[$nr]["code"] = $matches[1];
                                $posten[$nr]["pzeit"] = zeitins($matches[2]);
                                $posten[$nr]["prang"] = intval($matches[3]);
                                $status[1] += 1;
                            } else {
                                $res = preg_match("/^\ *\n/", $zwzt, $matches);
                                if ($res) {
                                    $zwzt = substr($zwzt, strlen($matches[0]));
                                    $status[0] = 2;
                                    $status[1] = $status[1]-($status[1]-2)%5-1;
                                } else {
                                    echo "break 1, ".json_encode($zwzt)."<br>";
                                    break;
                                }
                            }
                        }
                        if ($status[0]==2) {
                            $nr = $status[1];
                            $res = preg_match("/^\ *([0-9\.\:]+)/", $zwzt, $matches);
                            if ($res) {
                                $zwzt = substr($zwzt, strlen($matches[0]));
                                if (!isset($posten[$nr])) $posten[$nr] = array();
                                $posten[$nr]["rueckstand"] = zeitins($matches[1]);
                                $status[1] += 1;
                            } else {
                                $res = preg_match("/^\ *\n/", $zwzt, $matches);
                                if ($res) {
                                    $zwzt = substr($zwzt, strlen($matches[0]));
                                    $status[0] = 0;
                                } else {
                                    echo "break 2, ".json_encode($zwzt)."<br>";
                                    break;
                                }
                            }
                        }
                    }
                    $l["posten"] = $posten;
                    $laeufer[] = $l;
                }
                $kat["laeufer"] = $laeufer;
                $lauf[$keys[$i]] = $kat;
            }
            //echo "<pre>";print_r($lauf);echo "</pre>";
            //echo $fehler;
            
            // TESTS
            echo "<br><b>TESTS</b><br>";
            $keys = array_keys($lauf);
            for ($i=0; $i<count($keys); $i++) {
                $kat = $lauf[$keys[$i]];
                $numposten = $kat["numposten"];
                for ($l=0; $l<count($kat["laeufer"]); $l++) {
                    $laeufer = $kat["laeufer"][$l];
                    if (count($laeufer["posten"])==0 && $laeufer["zeit"]==-1) {
                        echo "WARNING: Po.f.: ".$laeufer["name"]."<br>";
                    } else {
                        if (count($laeufer["posten"])-1!=$numposten) echo "PARSE ERROR: Nicht alle Posten: ".json_encode($laeufer)."<br>";
                        if ($laeufer["zeit"]!=$laeufer["posten"][count($laeufer["posten"])]["gzeit"]) echo $laeufer["name"].": ".$laeufer["zeit"]." vs. ".$laeufer["posten"][count($laeufer["posten"])-1]["gzeit"]."<br>";
                    }
                }
            }
            echo "<br>";
        
            // EVALUATION
            echo "<br><b>EVALUATION</b><br>";
            $keys = array_keys($lauf);
            for ($i=0; $i<count($keys); $i++) {
                $kat = $lauf[$keys[$i]];
                for ($l=0; $l<count($kat["laeufer"]); $l++) {
                    $laeufer = $kat["laeufer"][$l];
                    $haszimmer = preg_match("/[Zz]immerb/",$laeufer["wohnort"].$laeufer["club"]);
                    if ($haszimmer) {
                        $result_p = $db->query("SELECT * from solvpeople WHERE name LIKE '".trim($laeufer["name"])."' AND jahrgang LIKE '".trim($laeufer["jahrgang"])."' AND wohnort LIKE '".trim($laeufer["wohnort"])."'");
                        $num_p = mysqli_num_rows($result_p);
                        if ($num_p==1) {
                            $row_p = mysqli_fetch_array($result_p);
                            $person = $row_p["id"];
                            echo $laeufer["name"].": PERSON FOUND: ".$person."<br>";
                        } else {
                            $db->query("INSERT solvpeople SET name='".trim($laeufer["name"])."', jahrgang='".trim($laeufer["jahrgang"])."', wohnort='".trim($laeufer["wohnort"])."'");
                            $person = mysqli_insert_id();
                            echo $laeufer["name"].": PERSON INSERTED: ".$person."<br>";
                        }
                        $zieleinlauf = $laeufer["posten"][count($laeufer["posten"])];
                        echo json_encode($zieleinlauf)."<br>";
                        if (0<intval($zieleinlauf["pzeit"]) && (strtolower($zieleinlauf["code"])=="zi" || strtolower($zieleinlauf["nr"])=="zi")) {
                            $db->query("INSERT INTO solvresults (person, lauf, kategorie, zieleinlauf) VALUES ('".intval($person)."', '".intval($laufid)."', '".mysqli_real_escape_string($keys[$i])."', '".intval($zieleinlauf["pzeit"])."')");
                        } else {
                            echo "INFO: NOT INSERTED ".json_encode($laeufer)."<br>";
                        }
                    }
                }
            }
            echo "<br>";
        }
        

        $result_r = $db->query("SELECT * from solvresults WHERE lauf='".intval($laufid)."'");
        $num_r = mysqli_num_rows($result_r);
        echo "<a href='?year=".$_GET["year"]."&event=".urlencode($_GET["event"])."&action=reevaluate'>Re-evaluate</a><table>";
        echo "<tr><td><b>Name</b></td><td><b>Zieleinlauf</b></td></tr>";
        for ($k=0; $k<$num_r; $k++) {
            $row_r = mysqli_fetch_array($result_r);
            $result_p = $db->query("SELECT name from solvpeople WHERE id='".intval($row_r["person"])."'");
            $row_p = mysqli_fetch_array($result_p);
            echo "<tr><td>".$row_p["name"]."</td><td>".$row_r["zieleinlauf"]."</td><td>".$row_r["summe"]."</td></tr>";
        }
        echo "</table>";
    } else if (isset($_GET["year"])) {
        $url = "http://www.o-l.ch/cgi-bin/results?type=rang&year=".intval($_GET["year"])."&event=Auswahl";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $file = curl_exec($ch);
        curl_close($ch);
        $res = preg_match_all("/<input[^>]*name=event[^>]*value=\"([^\"]+)\"[^>]*>/i",$file,$matches);
        $events = $matches[1];
        $result = $db->query("SELECT COUNT(*) from solvresults WHERE lauf='".intval($lauf)."'");
        $row = mysqli_fetch_array($result);
        for ($i=0; $i<count($events); $i++) {
            echo "<a href='?year=".$_GET["year"]."&event=".htmlentities(urlencode($events[$i]))."'>".$events[$i]." ".($row["COUNT(*)"]!="0"?"(".$row["COUNT(*)"].")":"")."</a><br>";
        }
    } else {
        for ($year=2000; $year<=date("Y"); $year++) {
            echo "<a href='?year=".$year."'>".$year."</a><br>";
        }
        echo "Remove Duplicates:<br><br>";
        $res1 = $db->query("SELECT id, name FROM solvpeople");
        $num1 = mysqli_num_rows($res1);
        echo "UPDATE solvresults SET person='<span id='pmer'>?</span>' WHERE person='<span id='pdel'>?</span>'; DELETE FROM solvpeople WHERE id='<span id='pdel1'>?</span>';<br>";
        for ($i=0; $i<$num1; $i++) {
            $row1 = mysqli_fetch_array($res1);
            echo "<a onclick='document.getElementById(&quot;pmer&quot;).innerHTML = &quot;".$row1["id"]."&quot;;'>mer</a> <a onclick='document.getElementById(&quot;pdel&quot;).innerHTML = &quot;".$row1["id"]."&quot;; document.getElementById(&quot;pdel1&quot;).innerHTML = &quot;".$row1["id"]."&quot;'>del</a> ".$row1["id"]." ".$row1["name"]."<br>";
        }
        $result = $db->query("SELECT s1.name AS name, s1.id AS id1, s2.id AS id2, s1.jahrgang AS jahrgang1, s2.jahrgang AS jahrgang2, s1.wohnort AS wohnort1, s2.wohnort AS wohnort2 FROM solvresults sr1 JOIN solvpeople s1 ON (sr1.person=s1.id) JOIN solvpeople s2 ON (s2.name=s1.name) JOIN solvresults sr2 ON (sr2.person=s2.id) WHERE s1.id!=s2.id ORDER BY name ASC");
        $num = mysqli_num_rows($result);
        for ($i=0; $i<$num; $i++) {
            $row = mysqli_fetch_array($result);
            echo $row["name"]." - ".$row["id1"]."-<span style='color:rgb(255,0,0);'>".$row["id2"]."</span> - ".$row["jahrgang1"]."-<span style='color:rgb(255,0,0);'>".$row["jahrgang2"]."</span> - ".$row["wohnort1"]."-<span style='color:rgb(255,0,0);'>".$row["wohnort2"]."</span><br>";
            echo "UPDATE solvresults SET person='".$row["id1"]."' WHERE person='".$row["id2"]."'<br>";
        }
    }
} else {
    $wertung = array();
	$sql = "SELECT * from solvpeople";
    $result_p = $db->query($sql);
    $num_p = mysqli_num_rows($result_p);
    for ($i=0; $i<$num_p; $i++) {
        $row_p = mysqli_fetch_array($result_p);
        $wertung[$row_p["id"]] = array();
    }
	$sql = "SELECT * from solvlaufe WHERE jahr='2015'";
    $result_l = $db->query($sql);
    $num_l = mysqli_num_rows($result_l);
    for ($i=0; $i<$num_l; $i++) {
        $row_l = mysqli_fetch_array($result_l);
        $result_r = $db->query("SELECT * from solvresults WHERE lauf='".$row_l["id"]."'");
        $num_r = mysqli_num_rows($result_r);
        $results = array();
        for ($j=0; $j<$num_r; $j++) {
            $row_r = mysqli_fetch_array($result_r);
            array_push($results, array(intval($row_r["person"]), intval($row_r["zieleinlauf"]), $row_r["kategorie"]));
        }
        usort($results, "ranglistesort");
        for ($j=0; $j<count($results); $j++) {
            array_push($wertung[$results[$j][0]], array($row_l["id"], ($j+1), $results[$j][1], $results[$j][2], count($results)));
        }
    }
    $people = array_keys($wertung);
    $html_details = "";
    $rangliste = array();
    for ($i=0; $i<count($people); $i++) {
        $result_p = $db->query("SELECT * from solvpeople WHERE id='".intval($people[$i])."'");
        $row_p = mysqli_fetch_array($result_p);
        $html_details .= "<tr><td style='padding-top:20px;border-bottom:solid 1px;'><a name='details".$row_p["id"]."'></a><b>".$row_p["name"]."</b></td><td style='padding-top:20px;border-bottom:solid 1px;text-align:right;'><a href='#cup_top'><img src='icons/up.gif' class='noborder' style='height:16px;'></a></td></tr>";
        //$html_details .= "<tr><td colspan='2'><br><br><br><a name='details".$row_p["id"]."'></a><h3>".$row_p["name"]."</h3></td></tr>";
        $tmp = $wertung[$people[$i]];
        $sum = 0;
        $num = 0;
        $htmlsum = array();
        for ($j=0; $j<count($tmp); $j++) {
            $result_l = $db->query("SELECT * from solvlaufe WHERE id='".intval($tmp[$j][0])."'");
            $row_l = mysqli_fetch_array($result_l);
            $html_details .= "<tr><td><a href='javascript:toggle(\"math-".$i."-".$j."\")'>".$row_l["name"]." (".$tmp[$j][3].")</a></td><td style='text-align:right;'>".$tmp[$j][2]."</td></tr><tr id='math-".$i."-".$j."' style='display:none;'><td colspan='2' style='padding-left:20px;'>Zieleinlauf Zeit: <b>".$tmp[$j][2]."s</b></td></tr>";
            if ($tmp[$j][1]!=-1) {
                $sum += $tmp[$j][2];
                $num += 1;
                array_push($htmlsum, $tmp[$j][2]);
            }
        }
        $div = ($num==0?7777777:round($sum*10/$num)/10);
        $html_details .= "<tr><td style='border-top:1px solid black;'><b><a href='javascript:toggle(\"math-total-".$i."\")'>TOTAL</a></b></td><td style='border-top:1px solid black; text-align:right;'>
<b>".$div."</b></td></tr><tr id='math-total-".$i."' style='display:none;'><td colspan='2'>
<table style='width:auto;'><tr><td rowspan='2' style='vertical-align:middle;'>TOTAL =&nbsp;</td><td>".implode(" + ", $htmlsum)."</td><td rowspan='2' style='vertical-align:middle;'>&nbsp;= ".($div)."</td></tr><tr><td style='border-top:1px solid black; text-align:center;'>".$num."</td></tr></table>
</td></tr>";
        array_push($rangliste, array($people[$i], $div, $num));
    }
    usort($rangliste, "ranglistesort");
    echo "<div>Die <span style='font-weight:bold; opacity:0.5;'>etwas ausgegrauten</span> Läufer haben die minimale Anzahl von 5 Läufen noch nicht erreicht.</div>
    <table>";
    for ($i=0; $i<count($rangliste); $i++) {
        $result_p = $db->query("SELECT * from solvpeople WHERE id='".intval($rangliste[$i][0])."'");
        $row_p = mysqli_fetch_array($result_p);
        echo "<tr style='".($i<3?"font-weight:bold;":"")."".($rangliste[$i][2]<5?"opacity:0.5;":"")."'><td style='width:1px; padding:1px 10px 1px 0px; text-align:right;'>".($i+1)."</td><td><a href='#details".$row_p["id"]."'>".$row_p["name"]."</a></td><td style='text-align:right;'>".$rangliste[$i][1]."</td></tr>";
    }
    echo "</table>";
    echo "<table>".$html_details."</table>";
}

function floatsort($a,$b) {
    return $a>$b?1:-1;
}

function ranglistesort($a,$b) {
    return $a[1]<$b[1]?-1:1;
}

function zeitins($zeit) {
    $zeit = trim($zeit);
    $res = preg_match("/(([0-9]+)[:\.])?([0-9]+)[:\.]([0-9]+)/",$zeit,$matches);
    if (!$res) return -1;
    $h = $matches[2];
    $m = $matches[3];
    $s = $matches[4];
    return $h*3600+$m*60+$s;
}

function roemisch($zahl) {
    $rz = "";
    while (1000<=$zahl) {
        $rz .= "M";
        $zahl -= 1000;
    }
    while (500<=$zahl) {
        $rz .= "D";
        $zahl -= 500;
    }
    while (100<=$zahl) {
        $rz .= "C";
        $zahl -= 100;
    }
    while (50<=$zahl) {
        $rz .= "L";
        $zahl -= 50;
    }
    while (10<=$zahl) {
        $rz .= "X";
        $zahl -= 10;
    }
    while (5<=$zahl) {
        $rz .= "V";
        $zahl -= 5;
    }
    while (0<$zahl) {
        $rz .= "I";
        $zahl -= 1;
    }
    $rz = str_replace("VIIII","IX",$rz);
    $rz = str_replace("IIII","IV",$rz);
    $rz = str_replace("LXXXX","XC",$rz);
    $rz = str_replace("XXXX","XL",$rz);
    $rz = str_replace("DCCCC","CM",$rz);
    $rz = str_replace("CCCC","CD",$rz);
    return $rz;
}

?>