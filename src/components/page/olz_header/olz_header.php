<?php

function olz_header($args = []): string {
    global $_CONFIG, $_SERVER;

    require_once __DIR__.'/../../../config/server.php';

    $is_insecure_nonlocal = !($_SERVER['HTTPS'] ?? false) && preg_match('/olzimmerberg\.ch/', $_SERVER['HTTP_HOST']);
    $host_has_www = preg_match('/www\./', $_SERVER['HTTP_HOST']);
    $host = str_replace('www.', '', $_SERVER['HTTP_HOST']);
    if ($is_insecure_nonlocal || $host_has_www) {
        $request_uri = $_SERVER['REQUEST_URI'];
        require_once __DIR__.'/../../../utils/client/HttpUtils.php';
        HttpUtils::fromEnv()->redirect("https://{$host}{$request_uri}", 308);
    }

    return olz_header_without_routing($args);
}

function olz_header_without_routing($args = []): string {
    global $_CONFIG, $_DATE, $_SERVER, $entityManager;
    $out = '';

    require_once __DIR__.'/../../../config/date.php';
    require_once __DIR__.'/../../../config/doctrine_db.php';
    require_once __DIR__.'/../../../config/server.php';
    require_once __DIR__.'/../../../model/index.php';
    require_once __DIR__.'/../../schema/olz_organization_data/olz_organization_data.php';

    $entry_points = $args['entry_points'] ?? ['index', 'common', 'vendor'];
    $scripts = [];
    $styles = [];
    foreach ($entry_points as $entry_point) {
        $css_path = "{$_CONFIG->getCodePath()}jsbuild/{$entry_point}.min.css";
        if (is_file($css_path)) {
            $css_modified = filemtime($css_path);
            $css_href = "{$_CONFIG->getCodeHref()}jsbuild/{$entry_point}.min.css?modified={$css_modified}";
            $styles[] = "<link rel='stylesheet' href='{$css_href}' />";
        }
        $js_path = "{$_CONFIG->getCodePath()}jsbuild/{$entry_point}.min.js";
        if (is_file($js_path)) {
            $js_modified = filemtime($js_path);
            $js_href = "{$_CONFIG->getCodeHref()}jsbuild/{$entry_point}.min.js?modified={$js_modified}";
            $scripts[] = "<script type='text/javascript' src='{$js_href}'></script>";
        }
    }
    $load_scripts = implode("\n", $scripts);
    $load_styles = implode("\n", $styles);

    if (!isset($refresh)) {
        $refresh = '';
    }
    $html_title = "OL Zimmerberg";
    if (isset($args['title'])) {
        $title_arg = htmlspecialchars($args['title']);
        $html_title = "OL Zimmerberg - {$title_arg}";
    }
    $html_description = "";
    if (isset($args['description'])) {
        $description_arg = htmlspecialchars(str_replace("\n", " ", $args['description']));
        $html_description = "<meta name='Description' content='{$description_arg}'>";
    }

    $no_robots = isset($_GET['archiv']) || ($args['norobots'] ?? false);
    $olz_organization_data = olz_organization_data([]);

    $additional_headers = implode("\n", $args['additional_headers'] ?? []);


    $out .= "<!DOCTYPE html>
    <html lang='de'>
    <head>
    <meta http-equiv='cache-control' content='public'>
    <meta http-equiv='content-type' content='text/html;charset=utf-8'>
    <meta name='Keywords' content='OL, Orientierungslauf, Sport, Laufsport, Gruppe, Klub, Verein, Zimmerberg, linkes Zürichseeufer, Sihltal, Kilchberg, Rüschlikon, Thalwil, Gattikon, Oberrieden, Horgen, Au ZH, Wädenswil, Richterswil, Schönenberg, Hirzel, Langnau am Albis, Adliswil, Stadt Zürich, Leimbach, Wollishofen, Enge, Friesenberg, Üetliberg, Entlisberg, Albis, Buchenegg, Landforst, Kopfholz, Chopfholz, Reidholz, Schweiz, OLZ, OLG'>
    {$html_description}
    <meta name='Content-Language' content='de'>
    {$refresh}
    ".($no_robots ? "<meta name='robots' content='noindex, nofollow'>" : "")."
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>{$html_title}</title>
    <link rel='shortcut icon' href='{$_CONFIG->getCodeHref()}favicon.ico' />
    {$olz_organization_data}
    {$additional_headers}
    {$load_scripts}
    {$load_styles}
    </head>";
    $out .= "<body class='olz-override-root'>\n";
    $out .= "<a name='top'></a>";

    require_once __DIR__.'/../olz_header_bar/olz_header_bar.php';
    $out .= olz_header_bar();

    $out .= "<div class='site-container'>";
    $out .= "<div class='site-background'>";

    $counter_repo = $entityManager->getRepository(Counter::class);
    $counter_repo->record(
        $_SERVER['REQUEST_URI'] ?? '',
        $_DATE,
        $_SERVER['HTTP_REFERER'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    );

    return $out;
}
