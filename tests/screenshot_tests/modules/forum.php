<?php

namespace Facebook\WebDriver;

require_once __DIR__.'/../utils/database.php';
require_once __DIR__.'/../utils/screenshot.php';

$forum_url = '/forum.php';

function test_forum($driver, $base_url) {
    global $forum_url;
    test_forum_readonly($driver, $base_url);

    $new_button = $driver->findElement(
        WebDriverBy::cssSelector('#buttonforum-neuer-eintrag')
    );
    $new_button->click();
    $title_input = $driver->findElement(
        WebDriverBy::cssSelector('#forumname')
    );
    $title_input->sendKeys('Testeintrag');
    $name_input = $driver->findElement(
        WebDriverBy::cssSelector('#forumname2')
    );
    $name_input->sendKeys('Test User');
    $email_input = $driver->findElement(
        WebDriverBy::cssSelector('#forumemail')
    );
    $email_input->sendKeys('forum-test@olzimmerberg.ch');
    $text_input = $driver->findElement(
        WebDriverBy::cssSelector('#forumeintrag')
    );
    $text_input->sendKeys('Viel Inhalt');
    take_pageshot($driver, 'forum_new_edit');

    $preview_button = $driver->findElement(
        WebDriverBy::cssSelector('#buttonforum-vorschau')
    );
    $preview_button->click();
    take_pageshot($driver, 'forum_new_preview');

    $save_button = $driver->findElement(
        WebDriverBy::cssSelector('#buttonforum-speichern')
    );
    $save_button->click();
    take_pageshot($driver, 'forum_new_finished');

    reset_dev_data();
}

function test_forum_readonly($driver, $base_url) {
    global $forum_url;
    $driver->get("{$base_url}{$forum_url}");
    take_pageshot($driver, 'forum');
}
