<?php

namespace Facebook\WebDriver;

require_once __DIR__.'/../utils/screenshot.php';

$aktuell_url = '/aktuell.php';
$aktuell_id_3_url = "{$aktuell_url}?id=3";

function test_aktuell($driver, $base_url) {
    global $aktuell_id_3_url;
    $driver->get("{$base_url}{$aktuell_id_3_url}");
    take_pageshot($driver, 'aktuell_id_3');

    login($driver, $base_url, 'admin', 'adm1n');
    $driver->get("{$base_url}{$aktuell_id_3_url}");
    $driver->navigate()->refresh();
    $driver->get("{$base_url}{$aktuell_id_3_url}");

    $new_button = $driver->findElement(
        WebDriverBy::cssSelector('#buttonaktuell-neuer-eintrag')
    );
    $new_button->click();
    $title_input = $driver->findElement(
        WebDriverBy::cssSelector('#aktuelltitel')
    );
    $title_input->sendKeys('Das Geschehnis');
    $teaser_input = $driver->findElement(
        WebDriverBy::cssSelector('#aktuelltext')
    );
    $teaser_input->sendKeys('Kleiner Teaser für den Artikel.');
    $text_input = $driver->findElement(
        WebDriverBy::cssSelector('#aktuelltextlang')
    );
    $text_input->sendKeys('Detailierte Schilderung des Geschehnisses.');
    $author_input = $driver->findElement(
        WebDriverBy::cssSelector('#aktuellautor')
    );
    $author_input->sendKeys('t.e., s.t.');
    take_pageshot($driver, 'aktuell_new_edit');

    $preview_button = $driver->findElement(
        WebDriverBy::cssSelector('#buttonaktuell-vorschau')
    );
    $preview_button->click();
    take_pageshot($driver, 'aktuell_new_preview');

    $save_button = $driver->findElement(
        WebDriverBy::cssSelector('#buttonaktuell-speichern')
    );
    $save_button->click();
    take_pageshot($driver, 'aktuell_new_finished');

    logout($driver, $base_url);
}
