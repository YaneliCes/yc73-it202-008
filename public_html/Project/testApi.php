<?php
require(__DIR__ . "/../../partials/nav.php");

$result = [];
if (isset($_GET["keyword"])) {
    //function=GLOBAL_QUOTE&symbol=MSFT&datatype=json
    $data = ["countryCode" => "us", "keyword" => $_GET["keyword"]];
    $endpoint = "https://ikea-api.p.rapidapi.com/keywordSearch";
    $isRapidAPI = true;
    $rapidAPIHost = "ikea-api.p.rapidapi.com";
    //$result = get($endpoint, "STORE_API_KEY", $data, $isRapidAPI, $rapidAPIHost);
    //example of cached data to save the quotas, don't forget to comment out the get() if using the cached data for testing
   
/*
    $result = ["status" => 200, "response" => '[{

        "id": "80275887"
        "name": "KALLAX",
        "price": "79.99",
        "measurement": "30 1/8x57 5/8 ",
        "typeName": "Shelf unit",
        "image": "https://www.ikea.com/us/en/images/products/kallax-shelf-unit-white__0644757_pe702939_s5.jpg",
        "contextualImageUrl": "https://www.ikea.com/us/en/images/products/kallax-shelf-unit-white__1051325_pe845148_s5.jpg",
        "imageAlt": "KALLAX Shelf unit, white, 30 1/8x57 5/8 ",
        "url": "https://www.ikea.com/us/en/p/kallax-shelf-unit-white-80275887/",
        "categoryPath": "Living room & entryway tables",
        
    
    }]'];
*/
/*
    ,
    {
        "name": "DKAJSDK",
        "price": "79.99",
        "measurement": "30 1/8x57 5/8 \"",
        "typeName": "Shelf unit",
        "image": "https://www.ikea.com/us/en/images/products/kallax-shelf-unit-white__0644757_pe702939_s5.jpg",
        "contextualImageUrl": "https://www.ikea.com/us/en/images/products/kallax-shelf-unit-white__1051325_pe845148_s5.jpg",
        "imageAlt": "KALLAX Shelf unit, white, 30 1/8x57 5/8 \"",
        "url": "https://www.ikea.com/us/en/p/kallax-shelf-unit-white-80275887/",
        "categoryPath": "Living room & entryway tables",
        "api_id": "91275887"
    
    },
    {   
        "name": "JSFKAFF",
        "price": "79.99",
        "measurement": "30 1/8x57 5/8 \"",
        "typeName": "Shelf unit",
        "image": "https://www.ikea.com/us/en/images/products/kallax-shelf-unit-white__0644757_pe702939_s5.jpg",
        "contextualImageUrl": "https://www.ikea.com/us/en/images/products/kallax-shelf-unit-white__1051325_pe845148_s5.jpg",
        "imageAlt": "KALLAX Shelf unit, white, 30 1/8x57 5/8 \"",
        "url": "https://www.ikea.com/us/en/p/kallax-shelf-unit-white-80275887/",
        "categoryPath": "Living room & entryway tables",
        "api_id": "73275887"
    
    },
    {
        "name": "LKDJFLS",
        "price": "79.99",
        "measurement": "30 1/8x57 5/8 \"",
        "typeName": "Shelf unit",
        "image": "https://www.ikea.com/us/en/images/products/kallax-shelf-unit-white__0644757_pe702939_s5.jpg",
        "contextualImageUrl": "https://www.ikea.com/us/en/images/products/kallax-shelf-unit-white__1051325_pe845148_s5.jpg",
        "imageAlt": "KALLAX Shelf unit, white, 30 1/8x57 5/8 \"",
        "url": "https://www.ikea.com/us/en/p/kallax-shelf-unit-white-80275887/",
        "categoryPath": "Living room & entryway tables",
        "api_id": "23275887"
    
    }]'];
*/

    
    error_log("Response: " . var_export($result, true));
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        $result = json_decode($result["response"], true);
    } else {
        $result = [];
    }
}



/*
if (isset($items)) {
*/

$items = $result;
    $apiIdCount = [];
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



$db = getDB();
$query = "INSERT INTO `Products` ";
$columns = [];
$params = [];

foreach($result as $index => $row) {
    foreach ($row as $k => $v) {
        if($index === 0){
            $columns[] = $k;
        }
        $params[":$k$index"] = $v;
    }
}

$query .= "(" . join(",", $columns) . ") ";
$query .= "VALUES ";

foreach ($result as $index => $row) {
    $rowValues = [];
    foreach ($row as $k => $v) {
        $rowValues[] = ":$k$index";
    }
    $query .= "(" . join(",", $rowValues) . ")";
    if ($index < count($result) - 1) {
        $query .= ",";
    }
}

$query .= " ON DUPLICATE KEY update api_id = api_id";



var_export($query);
try {
    $stmt = $db-> prepare($query);
    $stmt->execute($params);
    flash("Inserted record", "success");
}catch(PDOException $e) {
    error_log("Something broke with the query" . var_export($e, true));
    flash("An error occured", "danger");
}



?>
<div class="container-fluid">
    <h1>Store Info</h1>
    <p>Remember, we typically won't be frequently calling live data from our API, this is merely a quick sample. We'll want to cache data in our DB to save on API quota.</p>
    
    <form>
        <div>
            <label>Products</label>
            <input name="keyword" />
            <input type="submit" value="Fetch Product" />
        </div>
    </form>
    <div class="row ">
        <?php if (isset($result)) : ?>
            <?php foreach ($result as $products) : ?>
                <pre>
                    <?php var_export($products);?>
                </pre>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
</div>
<?php
require(__DIR__ . "/../../partials/flash.php");