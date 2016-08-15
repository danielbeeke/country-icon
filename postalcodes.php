<?php

function download_base($key) {
    $api_url = 'https://maps.google.com/maps/api/geocode/json?key=' . $key . '&components=country:The%20Netherlands&address=';

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
                    print_r($postal_code_info);
                    $postal_codes_geoms_json = json_encode($postal_codes_geoms, JSON_PRETTY_PRINT);
                    file_put_contents('postalcodes.json', $postal_codes_geoms_json);
                }
            }
        }

        $postal_codes_geoms_json = json_encode($postal_codes_geoms, JSON_PRETTY_PRINT);
        file_put_contents('postalcodes.json', $postal_codes_geoms_json);
    }
}

function download_postal_code($postal_code, $key) {
    $api_url = 'https://maps.google.com/maps/api/geocode/json?key=' . $key . '&components=country:The%20Netherlands&address=';

    $postal_code_info_json = file_get_contents($api_url . $postal_code);
    $postal_code_info = json_decode($postal_code_info_json, TRUE);

    if ($postal_code_info) {
        if (isset($postal_code_info['results'][0]['geometry']['location'])) {
            return $postal_code_info['results'][0]['geometry']['location'];
        }
    }
}


function get_missing() {
    $postal_codes_geoms = json_decode(file_get_contents('postalcodes.json'), TRUE);

    $missing = [];

    for ($i = 1000; $i <= 10000; $i++) {
        if (!isset($postal_codes_geoms[$i])) {
            $missing[] = $i;
        }
    }

    return $missing;
}

function download_missing($key) {
    $postal_codes_geoms = json_decode(file_get_contents('postalcodes.json'), TRUE);

    foreach(get_missing() as $postal_code) {
        if ($geo = download_postal_code($postal_code, $key)) {
            $postal_codes_geoms[$postal_code] = $geo;
            $postal_codes_geoms_json = json_encode($postal_codes_geoms, JSON_PRETTY_PRINT);
            file_put_contents('postalcodes.json', $postal_codes_geoms_json);
        }
        else {
            print 'Missing: ' . $postal_code . "\n";
        }
    }
}

//$argv[1];