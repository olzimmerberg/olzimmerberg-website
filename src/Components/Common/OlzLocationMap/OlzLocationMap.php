<?php

namespace Olz\Components\Common\OlzLocationMap;

use Olz\Components\Common\OlzComponent;

class OlzLocationMap extends OlzComponent {
    public function getHtml($args = []): string {
        $xkoord = $args['xkoord'] ?? null;
        $ykoord = $args['ykoord'] ?? null;
        $latitude = $args['latitude'] ?? null;
        $longitude = $args['longitude'] ?? null;
        $zoom = $args['zoom'] ?? 13;
        $width = $args['width'] ?? 400;
        $height = $args['height'] ?? 300;

        $lat = null;
        $lng = null;
        require_once __DIR__.'/../../../../_/library/wgs84_ch1903/wgs84_ch1903.php';
        if ($latitude !== null && $longitude !== null) {
            $lat = number_format($latitude, 6, '.', '');
            $lng = number_format($longitude, 6, '.', '');
            $xkoord = WGStoCHy($latitude, $longitude);
            $ykoord = WGStoCHx($latitude, $longitude);
        } elseif ($xkoord !== null && $ykoord !== null) {
            $lat = number_format(CHtoWGSlat($xkoord, $ykoord), 6, '.', '');
            $lng = number_format(CHtoWGSlng($xkoord, $ykoord), 6, '.', '');
        } else {
            throw new \Exception("Either xkoord/ykoord or latitude/longitude must be set in OlzLocationMap");
        }

        $mapbox_access_token = 'pk.eyJ1IjoiYWxsZXN0dWV0c21lcndlaCIsImEiOiJHbG9tTzYwIn0.kaEGNBd9zMvc0XkzP70r8Q';
        $mapbox_base_url = 'https://api.mapbox.com/styles/v1/allestuetsmerweh/ckgf9qdzm1pn319ohqghudvbz/static';
        $mapbox_url = "{$mapbox_base_url}/pin-l+009000({$lng},{$lat})/{$lng},{$lat},{$zoom},0/{$width}x{$height}?access_token={$mapbox_access_token}";

        $lv95_e = $xkoord + 2000000;
        $lv95_n = $ykoord + 1000000;
        $swisstopo_url = "https://map.geo.admin.ch/?lang=de&bgLayer=ch.swisstopo.pixelkarte-farbe&layers=ch.bav.haltestellen-oev&E={$lv95_e}&N={$lv95_n}&zoom=8&crosshair=marker";
        return <<<ZZZZZZZZZZ
        <a
            href='{$swisstopo_url}'
            target='_blank'
            class='olz-location-map-link'
        >
            <img
                src='{$mapbox_url}'
                class='olz-location-map-img test-flaky'
            />
        </a>
        ZZZZZZZZZZ;
    }
}
