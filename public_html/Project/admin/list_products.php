<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

/* yc73 4/14/23 */
if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}



$query = "SELECT id, api_id, name, price, measurement, typeName, image, contextualImageUrl, imageAlt, url, categoryPath, stock FROM `Products` ORDER BY created DESC LIMIT 50";
$db = getDB();
$stmt = $db->prepare($query);
$results=[];
try {
    $stmt->execute();
    $r = $stmt-> fetchAll();
    if($r) {
        $results = $r;
    }
}
catch(PDOException $e) {
    error_log("Error fetching products " . var_export($e, true));
    flash("Unhandled erorr occured", "danger");
}

$table = ["data" => $results, /*"title" => "Latest Products",*/ "ignored_columns" => ["id"], "edit_url" => get_url("admin/edit_product.php")];
?>



<div class="list-products-title">
    <h3>List Products</h3>
</div>

<div class="list-products-container">
    <div class="container-fluid list-products">
        <?php render_table($table); ?>
    </div>
</div>







<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>