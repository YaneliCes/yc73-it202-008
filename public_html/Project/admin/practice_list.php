<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}

//build search form
$form = [
    ["type" => "text", "name" => "name", "placeholder" => "Name", "label" => "Product Name", "include_margin" => false],

    ["type" => "number", "name" => "price", "placeholder" => "Price", "label" => "Price", "include_margin" => false],

    ["type" => "text", "name" => "typeName", "placeholder" => "Type", "label" => "Type", "include_margin" => false],

    ["type" => "text", "name" => "categoryPath", "placeholder" => "Category", "label" => "Category", "include_margin" => false],


    ["type" => "select", "name" => "sort", "label" => "Sort", "options" => ["price" => "Price", "name" => "Name", "category" => "Category", "created" => "Created", "modified" => "Modified"], "include_margin" => false],
    ["type" => "select", "name" => "order", "label" => "Order", "options" => ["asc" => "+", "desc" => "-"], "include_margin" => false],

    ["type" => "number", "name" => "limit", "label" => "Limit", "value" => "10", "include_margin" => false],


];
error_log("Form data: " . var_export($form, true));


$query = "SELECT id, api_id, name, price, measurement, typeName, image, contextualImageUrl, imageAlt, url, categoryPath, stock, is_api FROM `Products` WHERE 1=1";
$params = [];
$session_key = $_SERVER["SCRIPT_NAME"];
$is_clear = isset($_GET["clear"]);
if ($is_clear) {
    session_delete($session_key);
    unset($_GET["clear"]);
    die(header("Location: " . $session_key));
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

    //product name
    $name = se($_GET, "name", "", false);
    if (!empty($name)) {
        $query .= " AND name like :name";
        $params[":name"] = "%$name%";
    }

    //price
    $price_min = se($_GET, "price", "-1", false);
    if (!empty($price_min) && $price_min > -1) {
        $query .= " AND price >= :price";
        $params[":price"] = $price_min;
    }
    $price_max = se($_GET, "price", "-1", false);
    if (!empty($price_max) && $price_max > -1) {
        $query .= " AND price <= :price";
        $params[":price"] = $price_max;
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





$query = "SELECT id, api_id, name, price, measurement, typeName, image, contextualImageUrl, imageAlt, url, categoryPath, stock FROM `Products` ORDER BY created DESC LIMIT 50";
$db = getDB();
$stmt = $db->prepare($query);
$results = [];
error_log("Data result: " . var_dump($results));
try {
    $stmt->execute($params);
    $r = $stmt->fetchAll();
    if ($r) {
        $results = $r;
    }
} catch (PDOException $e) {
    error_log("Error fetching stocks " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}

$table = [
    "data" => $results, /*"title" => "Latest Stocks",*/ "ignored_columns" => ["id"],
    "edit_url" => get_url("admin/edit_product.php"),
    "delete_url" => get_url("admin/delete_product.php")
];
?>
<div class="container-fluid">
    <div class="list-products-title">
        <h3>List Products</h3>
    </div>
    <div class="list-products-container">
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

        <div class="container-fluid list-products">
            <?php render_table($table); ?>
        </div>
    </div>
    
</div>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>