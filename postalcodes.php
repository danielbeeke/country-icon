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

function create_compressed_file() {
    $postal_codes_geoms = json_decode(file_get_contents('postalcodes.json'), TRUE);
    $rows = [];

    $smallest_lat = 50.7599552; // 0%
    $biggest_lat = 53.4944646; // 100%

    $smallest_lng = 3.3933198; // 0%
    $biggest_lng = 7.1928279; // 100%

    $empty_counter = 0;

    foreach ($postal_codes_geoms as $postal_code => $geom) {
        if ($geom['lat'] != 52.132633 && $geom['lng'] != 5.291266) {
            if ($empty_counter) {
                $rows[] = $empty_counter;
                $empty_counter = 0;
            }

            $lat_percentage = ($geom['lat'] - $smallest_lat) / (($biggest_lat - $smallest_lat) / 100);
            $lng_percentage = ($geom['lng'] - $smallest_lng) / (($biggest_lng - $smallest_lng) / 100);

            $rows[] = round($lat_percentage, 2) . ',' . round($lng_percentage, 2);
        }
        else {
            $empty_counter++;
        }

//        if ($postal_code == 3815) {
//            print round($lat_percentage, 2) . ',' . round($lng_percentage, 2) . "\n";
//        }
    }

    $compressed_data = implode("\n", $rows);

    file_put_contents('compressed.data', $compressed_data);
}

function create_reversed_tree() {
    $raw_file = file_get_contents('compressed.data');
    $reversed_tree = [];

    $rows = explode("\n", $raw_file);

    foreach ($rows as $row) {
        $row_parts = explode(',', $row);

        foreach ($row_parts as $row_part) {
            for ($i = 1; $i <= strlen($row_part) + 1; $i++) {
                if (strlen(substr($row_part, 0, $i)) > 2) {
                    $reversed_tree[substr($row_part, 0, $i)] = substr_count($raw_file, substr($row_part, 0, $i));
                }
            }
        }
    }

    $compress_characters = 'abcdefghijklmnopqrstuvwxyzAVCDEFGHIJKLMNOPQRSTUVWXYZ';

    asort($reversed_tree);
    $reversed_tree_sorted = array_reverse($reversed_tree);

    $reversed_tree_sorted_keys = array_keys($reversed_tree_sorted);

    foreach (str_split($compress_characters) as $delta => $letter) {
        $raw_file = str_replace($reversed_tree_sorted[$reversed_tree_sorted_keys[$delta]], $letter, $raw_file);
    }

    file_put_contents('more-compressed.data', $raw_file);
}

//$argv[1];

create_compressed_file();