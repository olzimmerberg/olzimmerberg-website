<?php

require_once __DIR__.'/config/init.php';

session_start_if_cookie_set();

require_once __DIR__.'/admin/olz_functions.php';
require_once __DIR__.'/components/page/olz_header/olz_header.php';
require_once __DIR__.'/components/page/olz_footer/olz_footer.php';
require_once __DIR__.'/config/doctrine_db.php';
require_once __DIR__.'/model/index.php';

if (isset($_GET['abteilung'])) {
    $role_username = $_GET['abteilung'];
    $role_repo = $entityManager->getRepository(Role::class);
    $role = $role_repo->findOneBy(['username' => $role_username]);

    if (!$role) {
        require_once __DIR__.'/error_utils.php';
        die_with_http_error(404);
    }

    echo olz_header([
        'title' => $role->getName(),
        'description' => $role->getDescription(),
        'norobots' => true,
    ]);

    require_once __DIR__.'/components/verein/olz_role_page/olz_role_page.php';
    echo olz_role_page(['role' => $role]);

    echo olz_footer();
} else {
    echo olz_header([
        'title' => "Kontakt",
        'description' => "Die wichtigsten Kontaktadressen und eine Liste aller Vereinsorgane der OL Zimmerberg.",
    ]);
    echo <<<'ZZZZZZZZZZ'
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "url": "https://olzimmerberg.ch",
        "logo": "https://olzimmerberg.ch/_/icns/olz_logo_mit_hintergrund.svg",
        "name": "OL Zimmerberg",
        "description": "Wir sind ein Orientierungslauf-Sportverein in der Region um den Zimmerberg am linken Zürichseeufer und im Sihltal. Unsere Mitglieder kommen aus Kilchberg, Rüschlikon, Thalwil, Oberrieden, Horgen, Au ZH, Wädenswil, Richterswil, Schönenberg, Hirzel, Langnau am Albis, Gattikon, Adliswil und nahe gelegenen Teilen der Stadt Zürich (Wollishofen, Enge, Leimbach, Friesenberg).",
        "foundingDate": "2006-01-13",
        "sameAs": [
            "https://www.youtube.com/channel/UCMhMdPRJOqdXHlmB9kEpmXQ",
            "https://www.strava.com/clubs/ol-zimmerberg-158910",
            "https://www.facebook.com/olzimmerberg/",
            "https://www.instagram.com/olzimmerberg/",
            "https://github.com/olzimmerberg"
        ]
    }
    </script>
    ZZZZZZZZZZ;

    echo "<div id='content_double'>";
    require_once __DIR__.'/components/verein/olz_organigramm/olz_organigramm.php';
    echo olz_organigramm();
    echo "</div>";

    echo olz_footer();
}
