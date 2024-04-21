<?php
/* yc73 4/15/23 */
function fetch_quote($keyword) {
    $data = ["countryCode" => "us", "keyword" => $keyword];
    $endpoint = "https://ikea-api.p.rapidapi.com/keywordSearch";
    $isRapidAPI = true;
    $rapidAPIHost = "ikea-api.p.rapidapi.com";
    $result = get($endpoint, "STORE_API_KEY", $data, $isRapidAPI, $rapidAPIHost);
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        $result = json_decode($result["response"], true);
    } else {
        $result = [];
    }
    if (isset($result)) {
        $items = $result;
        foreach (array_keys($items) as $array) {
            $key = $items[$array];
            
            if (isset($key['id'])) {
                $items[$array]['api_id'] = $key['id'];
                unset($items[$array]['id']);
            }
            if (is_array($key['price']) && isset($key['price']['currentPrice'])) {
                $items[$array]['price'] = $key['price']['currentPrice'];
            }
            
            $items[$array]['measurement'] = str_replace('"', '', $key['measurement']);
    
            $items[$array]['imageAlt'] = str_replace('"', '', $key['imageAlt']);
            
            if (is_array($key['categoryPath']) && isset($key['categoryPath'][1]['name'])) {
                $items[$array]['categoryPath'] = $key['categoryPath'][1]['name'];
            }
            if (is_array($key['variants'])) {
                unset($items[$array]['variants']);           
            }
            if (!isset($key['contextualImageUrl'])) {
                $items[$array]['contextualImageUrl'] = '';
            }
        }
        $result = $items;
    }
    return $result;
}