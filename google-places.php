<?php

define ('API_KEY', 'AIzaSyB72FnWTJQ4duHKk-54ZJRLp-_-LSzb-gU');
define ('SEARCH_API', 'https://maps.googleapis.com/maps/api/place/textsearch/json');
define ('DETAILS_API', 'https://maps.googleapis.com/maps/api/place/details/json');

function http_get($url, $params) {
    $params["key"] = API_KEY; 
    $ch = curl_init($url . '?' . http_build_query($params)); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //var_dump(curl_getinfo($ch));
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function get_place($query) {
    $result_json = json_decode( http_get(SEARCH_API, array("query" => $query) ));
    $place = $result_json->results[0];
    
    return array("name" => $place->name, "id" => $place->place_id, "address" => $place->formatted_address);
}

function get_details($placeid) {
    $result = http_get( DETAILS_API, array('placeid' => $placeid));
    $result_json = json_decode( http_get(DETAILS_API, array("placeid" => $placeid)));
    $result = $result_json->result;
    if (!isset($result->rating) || !$result->rating) {
        return null;
    } else {
        $rating = $result->rating;
        $reviews = $result->reviews;
    }
    return array('rating' => $rating, 'reviews' => $reviews);
}

if (php_sapi_name() == 'cli') {
    if ($argc < 2) die("Missing query");
    $query = $argv[1];
} else {
    if (!isset($_GET['q'])) die("Missing query");

    $query = urlencode($_GET['q']);
}

$place = get_place($query);
$rnr = get_details($place['id']);

echo json_encode($place + $rnr);


?>