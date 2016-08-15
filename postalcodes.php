<?php

$api_url = 'https://maps.google.com/maps/api/geocode/json?key=AIzaSyBbvfdgUmI8t8289qFA-_o5yw4har1F0g8&components=country:The%20Netherlands&address=';

$postal_codes_geoms = json_decode(file_get_contents('postalcodes.json'), TRUE);

for ($i = 1000; $i <= 10000; $i++) {
    if (!isset($postal_codes_geoms[$i])) {
        $postal_code_info_json = file_get_contents($api_url . $i);
        $postal_code_info = json_decode($postal_code_info_json, TRUE);

        if ($postal_code_info) {
            if (isset($postal_code_info['results'][0]['geometry']['location'])) {
                $postal_codes_geoms[$i] = $postal_code_info['results'][0]['geometry']['location'];
            }
            else {
                $postal_codes_geoms_json = json_encode($postal_codes_geoms, JSON_PRETTY_PRINT);
                file_put_contents('postalcodes.json', $postal_codes_geoms_json);
            }
        }
    }

    $postal_codes_geoms_json = json_encode($postal_codes_geoms, JSON_PRETTY_PRINT);
    file_put_contents('postalcodes.json', $postal_codes_geoms_json);
}

