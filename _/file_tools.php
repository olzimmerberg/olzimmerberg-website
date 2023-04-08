<?php

// =============================================================================
// Funktionen für Datei-Upload, z.B. PDFs in Aktuell-Einträgen.
// =============================================================================

use Olz\Utils\FileUtils;
use Olz\Utils\UploadUtils;

require_once __DIR__.'/config/init.php';
require_once __DIR__."/config/paths.php";

global $tables_file_dirs, $mime_extensions, $extension_icons;

$tables_file_dirs = FileUtils::TABLES_FILE_DIRS;
$mime_extensions = FileUtils::MIME_EXTENSIONS;
$extension_icons = FileUtils::EXTENSION_ICONS;

if (basename($_SERVER["SCRIPT_FILENAME"] ?? '') == basename(__FILE__)) {
    if (!isset($_GET["request"])) {
        header("Content-type:text/plain");
        echo "HIER IST NIX";
    }

    if ($_GET["request"] == "thumb") {
        session_write_close();
        $db_table = $_GET["db_table"];
        if (!isset($tables_file_dirs[$db_table])) {
            echo "Invalid db_table (in thumb)";
            return;
        }
        $db_filepath = $tables_file_dirs[$db_table];
        $id = intval($_GET["id"]);
        if ($id <= 0) {
            echo "Invalid id (in thumb)";
            return;
        }
        $index = $_GET["index"];
        $is_migrated = !(is_numeric($index) && intval($index) > 0 && intval($index) == $index);
        if ($is_migrated) {
            if (!preg_match("/^[0-9A-Za-z_\\-]{24}\\.\\S{1,10}$/", $index)) {
                echo "Invalid index (=hash; in thumb)";
                return;
            }
        } else {
            if ($index <= 0) {
                echo "Invalid index (in thumb)";
                return;
            }
        }
        $dim = 16;
        if (intval($_GET["dim"]) > 16) {
            $dim = 128;
        }
        if ($is_migrated) {
            preg_match("/^[0-9A-Za-z_\\-]{24}\\.(\\S{1,10})$/", $index, $matches);
            $thumbfile = __DIR__."/../public/icns/link_".$extension_icons[$matches[1]]."_16.svg";
            if (!is_file($thumbfile)) {
                $thumbfile = __DIR__."/../public/icns/link_any_16.svg";
            }
            header("Cache-Control: max-age=86400");
            header("Content-Type: image/svg+xml");
            $fp = fopen($thumbfile, "r");
            $buf = fread($fp, 1024);
            while ($buf) {
                echo $buf;
                $buf = fread($fp, 1024);
            }
            fclose($fp);
        } else {
            $files = scandir($data_path.$db_filepath."/".$id);
            for ($i = 0; $i < count($files); $i++) {
                if (preg_match("/^([0-9]{3})\\.([a-zA-Z0-9]+)$/", $files[$i], $matches)) {
                    if (intval($matches[1]) == $index) {
                        $thumbfile = __DIR__."/../public/icns/link_".$extension_icons[$matches[2]]."_16.svg";
                        if (!is_file($thumbfile)) {
                            $thumbfile = __DIR__."/../public/icns/link_any_16.svg";
                        }
                        header("Cache-Control: max-age=86400");
                        header("Content-Type: image/svg+xml");
                        $fp = fopen($thumbfile, "r");
                        $buf = fread($fp, 1024);
                        while ($buf) {
                            echo $buf;
                            $buf = fread($fp, 1024);
                        }
                        fclose($fp);
                    }
                }
            }
        }
    }

    if ($_GET["request"] == "info") {
        $db_table = $_GET["db_table"];
        if (!isset($tables_file_dirs[$db_table])) {
            echo "Invalid db_table";
            return;
        }
        $db_filepath = $tables_file_dirs[$db_table];
        $id = intval($_GET["id"]);
        $files = @scandir($data_path.$db_filepath."/".$id);
        if (!$files) {
            $files = [];
        }
        $indices = [];
        for ($i = 0; $i < count($files); $i++) {
            if (preg_match("/^([0-9]{3})\\.([a-zA-Z0-9]+)$/", $files[$i], $matches)) {
                $indices[intval($matches[1])] = true;
            }
        }
        $newindex = 0;
        for ($i = 0; true; $i++) {
            if (!isset($indices[$i + 1])) {
                $newindex = $i;
                break;
            }
        }
        echo json_encode(["count" => $newindex]);
    }

    if ($_GET["request"] == "uploadpart") {
        session_start();
        // Data sanitization & initialization
        $db_table = $_GET["db_table"];
        if (!isset($tables_file_dirs[$db_table])) {
            echo json_encode([0, "!tables_dirs-dbtable"]);
            return;
        }
        $db_filepath = $tables_file_dirs[$db_table];
        $id = intval($_GET["id"]);
        if ($id <= 0) {
            echo json_encode([0, "!id"]);
            return;
        }
        if (!isset($_SESSION[$db_table."id"]) || $_SESSION[$db_table."id"] != $id) {
            echo json_encode([0, "!permission"]);
            return;
        }
        $part = intval($_POST["part"]);
        if ($part < 0 || $part > 1000) {
            echo json_encode([0, "!part<1000"]);
            return;
        }
        $temppath = $data_path."temp/".md5($data_path.$db_filepath)."-".$id."-".$part;
        $fp = fopen($temppath, "w+");
        $upload_utils = UploadUtils::fromEnv();
        fwrite($fp, $upload_utils->deobfuscateUpload(str_replace(" ", "+", $_POST["content"])));
        fclose($fp);

        if ($_POST["last"] == "1") {
            // Create directory structure, if not yet present
            $abspath = $data_path.$db_filepath."/".$id;
            if (!is_dir($abspath."/")) {
                mkdir($abspath."/");
            }

            $temppath = $data_path."temp/".md5($data_path.$db_filepath)."-".$id."-";
            $firstcontent = file_get_contents($temppath."0");
            @unlink($temppath."0");
            $res = preg_match("/^data\\:([^\\;]*)\\;base64\\,(.+)$/", $firstcontent, $matches);
            if (!$res) {
                echo json_encode([0, "!res"]);
                return;
            }
            if (!isset($mime_extensions[$matches[1]])) {
                $res_fn = preg_match("/\\.([^\\.]*)$/", $_POST["filename"], $matches_fn);
                if (!isset($extension_icons[$matches_fn[1]])) {
                    echo json_encode([0, "!mime:".$matches[1]." - !Filename-Extension:".$_POST["filename"]]);
                    return;
                }
                $ext = $matches_fn[1];
            } else {
                $ext = $mime_extensions[$matches[1]];
            }
            $base64 = $matches[2];
            for ($i = 1; $i <= $part; $i++) {
                if (!is_file($temppath.$i)) {
                    echo json_encode([0, "!part".$i]);
                    return;
                }
                $partcontent = file_get_contents($temppath.$i);
                $base64 .= $partcontent;
                @unlink($temppath.$i);
            }
            $filedata = base64_decode(str_replace(" ", "+", $base64));
            if (!$filedata) {
                echo json_encode([0, "!filedata"]);
                return;
            }
            $files = scandir($abspath);
            $indices = [];
            for ($i = 0; $i < count($files); $i++) {
                if (preg_match("/^([0-9]{3})\\.([a-zA-Z0-9]+)$/", $files[$i], $matches)) {
                    $indices[intval($matches[1])] = true;
                }
            }
            $newindex = 0;
            for ($i = 0; true; $i++) {
                if (!isset($indices[$i + 1])) {
                    $newindex = $i;
                    break;
                }
            }

            // Write uploaded file to destination
            $fp = fopen($abspath."/".str_pad($newindex + 1, 3, "0", STR_PAD_LEFT).".".$ext, "w+");
            fwrite($fp, $filedata);
            fclose($fp);

            echo json_encode([1, str_pad($newindex + 1, 3, "0", STR_PAD_LEFT), $files, strlen($base64), strlen($filedata)]);
        } else {
            echo json_encode([1, "continue"]);
        }
    }

    if ($_GET["request"] == "change") {
        session_start();
        $db_table = $_GET["db_table"];
        if (!isset($tables_file_dirs[$db_table])) {
            echo json_encode([0, "!tables_dirs-dbtable"]);
            return;
        }
        $id = intval($_POST["id"]);
        if ($id <= 0) {
            echo json_encode([0, "!id"]);
            return;
        }
        if (!isset($_SESSION[$db_table."id"]) || $_SESSION[$db_table."id"] != $id) {
            echo json_encode([0, "!permission"]);
            return;
        }
        $index = intval($_POST["index"]);
        if ($index <= 0) {
            echo json_encode([0, "!index"]);
            return;
        }
        $db_filepath = $tables_file_dirs[$db_table];
        if ($_POST["delete"] == 1) {
            $files = scandir($data_path.$db_filepath."/".$id);
            for ($i = 0; $i < count($files); $i++) {
                if (preg_match("/^([0-9]{3})\\.([a-zA-Z0-9]+)$/", $files[$i], $matches)) {
                    if (intval($matches[1]) == $index) {
                        @unlink($data_path.$db_filepath."/".$id."/".$matches[0]);
                    } elseif ($index < intval($matches[1])) {
                        rename(
                            $data_path.$db_filepath."/".$id."/".$matches[0],
                            $data_path.$db_filepath."/".$id."/".str_pad(intval($matches[1]) - 1, 3, "0", STR_PAD_LEFT).".".$matches[2]
                        );
                    }
                }
            }
        }
        echo json_encode([1, intval($_POST["delete"])]);
    }
}

if (!function_exists('olz_files_edit')) {
    function olz_files_edit($db_table, $id) {
        global $tables_file_dirs;
        if (!isset($tables_file_dirs[$db_table])) {
            return "Ungültige db_table (in olz_files_edit)";
        }
        $db_filepath = $tables_file_dirs[$db_table];
        $htmlout = "";
        $ident = "olzfileedit".md5($db_table."-".$id);
        $htmlout .= "<div id='".$ident."'></div>";
        $htmlout .= "<script type='text/javascript'>olz.olz_files_edit_redraw(".json_encode($ident).", ".json_encode($db_table).", ".json_encode($id).");</script>";
        return $htmlout;
    }
}
