<?php
require(__DIR__ . "/../../partials/nav.php");
?>
<h1 class="homeTitle">Products</h1>
<?php

/* yc73 4/1/23 */
if (is_logged_in(true)) {
    //flash("Welcome, " . get_user_email(");
    error_log("Session data: " . var_export($_SESSION, true));
} 
/*else {
    flash("You're not logged in");
}*/





/* yc73 4/22/23 */
//build search form
$form = [
    
    ["type" => "text", "name" => "name", "placeholder" => "Name", "label" => "Product Name", "include_margin" => false],

    ["type" => "number", "name" => "price_min", "placeholder" => "Min Price", "label" => "Min Price", "step" => "0.01", "include_margin" => false],
    ["type" => "number", "name" => "price_max", "placeholder" => "Max Price", "label" => "Max Price", "step" => "0.01", "include_margin" => false],

    ["type" => "text", "name" => "typeName", "placeholder" => "Type", "label" => "Type", "include_margin" => false],

    ["type" => "text", "name" => "categoryPath", "placeholder" => "Category", "label" => "Category", "include_margin" => false],


    ["type" => "select", "name" => "sort", "label" => "Sort", "options" => ["created" => "Created", "modified" => "Modified", "name" => "Name", "price" => "Price"], "include_margin" => false],
    ["type" => "select", "name" => "order", "label" => "Order", "options" => ["desc" => "-", "asc" => "+"], "include_margin" => false],

    ["type" => "number", "name" => "limit", "label" => "Limit", "value" => "10", "include_margin" => false],

];
error_log("Form data: " . var_export($form, true));


$total_records = get_total_count("`Products` pr LEFT JOIN `UserProducts` upr on pr.id = upr.product_id");

/* yc73 */
/* 4/12/23 */
//$query = "SELECT id, api_id, name, price, measurement, typeName, image, contextualImageUrl, imageAlt, url, categoryPath, stock, is_api FROM `Products` WHERE 1=1";
$query = "SELECT pr.id, api_id, pr.name, pr.price, measurement, typeName, image, contextualImageUrl, imageAlt, url, categoryPath, stock, pr.created, pr.modified, is_api, upr.user_id FROM `Products` pr
LEFT JOIN `UserProducts` upr ON pr.id = upr.product_id WHERE 1 = 1";
$params = [];
$session_key = $_SERVER["SCRIPT_NAME"];
$is_clear = isset($_GET["clear"]);
if ($is_clear) {
    session_delete($session_key);
    unset($_GET["clear"]);
    //die(header("Location: " . $session_key));
    redirect($session_key);
} else {
    $session_data = session_load($session_key);
}

if (count($_GET) == 0 && isset($session_data) && count($session_data) > 0) {
    if ($session_data) {
        $_GET = $session_data;
    }
}
if (count($_GET) > 0) {
    session_save($session_key, $_GET);
    $keys = array_keys($_GET);

    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $_GET[$v["name"]];
        }
    }

    //error_log("Data: " . var_dump($form));

    /* yc73 */
    /* 4/12/23 */
    //product name
    $name = se($_GET, "name", "", false);
    if (!empty($name)) {
        $query .= " AND name like :name";
        
        $params[":name"] = "%$name%";
    }
    //error_log("Data: " . var_dump($query));
    //error_log("Data: " . var_dump($params));
    
    //price
    $price_min = se($_GET, "price_min", "-1", false);
    if (!empty($price_min) && $price_min > -1) {
        $query .= " AND price >= :price_min";
        $params[":price_min"] = $price_min;
    }
    $price_max = se($_GET, "price_max", "-1", false);
    if (!empty($price_max) && $price_max > -1) {
        $query .= " AND price <= :price_max";
        $params[":price_max"] = $price_max;
    }

    //product type
    $typeName = se($_GET, "typeName", "", false);
    if (!empty($typeName)) {
        $query .= " AND typeName like :typeName";
        $params[":typeName"] = "%$typeName%";
    }

    //category
    $categoryPath = se($_GET, "categoryPath", "", false);
    if (!empty($categoryPath)) {
        $query .= " AND categoryPath like :categoryPath";
        $params[":categoryPath"] = "%$categoryPath%";
    }

    //sort and order
    $sort = se($_GET, "sort", "created", false);
    if (!in_array($sort, ["name", "price", "typeName", "categoryPath", "created", "modified"])) {
        $sort = "created";
    }
    //tell mysql I care about the data from table "b"
    if ($sort === "created" || $sort === "modified") {
        $sort = "pr." . $sort;
    }
    $order = se($_GET, "order", "desc", false);
    if (!in_array($order, ["asc", "desc"])) {
        $order = "desc";
    }
    //IMPORTANT make sure you fully validate/trust $sort and $order (sql injection possibility)
    $query .= " ORDER BY $sort $order";
    //limit
    try {
        $limit = (int)se($_GET, "limit", "10", false);
    } catch (Exception $e) {
        $limit = 10;
    }
    if ($limit < 1 || $limit > 100) {
        $limit = 10;
    }
    //IMPORTANT make sure you fully validate/trust $limit (sql injection possibility)
    $query .= " LIMIT $limit";
}


else {
    try {
        $limit = (int)se($_GET, "limit", "10", false);
    } catch (Exception $e) {
        $limit = 10;
    }
    if ($limit < 1 || $limit > 100) {
        $limit = 10;
    }
    $query .= " LIMIT $limit";
}






$db = getDB();
$stmt = $db->prepare($query);
$results = [];
try {
    $stmt->execute($params);
    $r = $stmt->fetchAll();
    if ($r) {
        $results = $r;
    }
} catch (PDOException $e) {
    error_log("Error fetching products " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}
foreach ($results as $index => $product) {
    foreach ($product as $key => $value) {
        if (is_null($value)) {
            $results[$index][$key] = "N/A";
        }
    }
}

$table = [
    "data" => $results, "title" => "Products", "ignored_columns" => ["id"],
    "view_url" => get_url("view_product_customer.php"),
    //"view_url" => get_url("admin/view_product.php"),
];
?>
<div class="container-fluid">
    <div class="list-products-title">
        <!--<h3>Products</h3>-->
    </div>
    <div class="all-products-container">
        <form method="GET">
            <div class="row mb-3" style="align-items: flex-end;">

                <?php foreach ($form as $k => $v) : ?>
                    <div class="col">
                        <?php render_input($v); ?>
                    </div>
                <?php endforeach; ?>

            </div>
            <?php render_button(["text" => "Search", "type" => "submit", "text" => "Filter"]); ?>
            <a href="?clear" class="btn btn-secondary">Clear</a>
        </form>
        <?php render_result_counts(count($results), $total_records); ?>
        <div class="row w-100 row-cols-auto row-cols-sm-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 row-cols-xxl-5 g-4">
            <?php foreach ($results as $product) : ?>
                <div class="col">
                    <?php render_product_card($product); ?>
                    
                </div>
            <?php endforeach; ?>
            <?php if (count($results) === 0) : ?>
                <div class="col">
                    No results to show
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>






<?php require_once(__DIR__ . "/../../partials/flash.php");