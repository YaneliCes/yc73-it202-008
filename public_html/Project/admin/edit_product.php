<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

/* yc73 4/14/23 */
if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}
?>



<?php
    $id = se($_GET, "id", -1, false);

    //TODO handle product fetch
    /*if (isset($_POST["keyword"]))*/ 
    /* $action = $_POST["action"]; */

    if (isset($_POST["api_id"])) {
        foreach ($_POST as $k => $v) {
            /*error_log("POST" . var_export($_POST, true));*/
            if (!in_array($k, ["api_id", "name", "price", "measurement", "typeName", "image", "contextualImageUrl", "imageAlt", "url", "categoryPath", "stock"])) {
                unset($_POST[$k]);
            }
            $quote = $_POST;
            error_log("Cleaned up POST: " . var_export($quote, true));
        }
        //insert data
        $db = getDB();
        $query = "UPDATE `Products` SET ";
        $params = [];

        //per record
        foreach ($quote as $k => $v) {
            if ($params) {
                $query .= ",";
            }
            //be sure $k is trusted as this is a source of sql injection
            $query .= "$k=:$k";
            $params[":$k"] = $v;
        }

        $query .= " WHERE id = :id";
        $params[":id"] = $id;
        error_log("Query: " . $query);
        error_log("Params: " . var_export($params, true));

        try {
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            flash("Updated record ", "success");
        } catch (PDOException $e) {
            error_log("Something broke with the query" . var_export($e, true));
            flash("An error occurred", "danger");
        }
    }


    $product = [];
    if ($id > -1) {
        //fetch
        $db = getDB();
        $product = [];
        $query = "SELECT api_id, name, price, measurement, typeName, image, contextualImageUrl, imageAlt, url, categoryPath, stock FROM `Products` WHERE id = :id";
        try {
            $stmt = $db->prepare($query);
            $stmt->execute([":id" => $id]);
            $r = $stmt->fetch();
            if ($r) {
                $product = $r;
            }
        }
        catch (PDOException $e) {
            error_log("Error fetching record: " . var_export($e, true));
            flash("Error fetching record", "danger");
        }
    }
    else {
        flash("Invalid id passed", "danger");
        die(header("Location:" . get_url("admin/list_products.php")));
    }

    if($product) {
        $form = [
            //["type" => "text", "name" => "api_id", "placeholder" => "Product API ID", "label" => "Product API ID", "rules" => ["required" => "required"]],
            ["type" => "text", "name" => "name", "placeholder" => "Product Name", "label" => "Product Name", "rules" => ["required" => "required"]],
            ["type" => "number", "name" => "price", "placeholder" => "Product Price", "label" => "Product Price", "rules" => ["required" => "required"]],
            ["type" => "text", "name" => "measurement", "placeholder" => "Product Measurement", "label" => "Product Measurement", "rules" => ["required" => "required"]],
            ["type" => "text", "name" => "typeName", "placeholder" => "Product Type (name)", "label" => "Product Type (name)", "rules" => ["required" => "required"]],
            ["type" => "text", "name" => "image", "placeholder" => "Product Image", "label" => "Product Image", "rules" => ["required" => "required"]],
            ["type" => "text", "name" => "contextualImageUrl", "placeholder" => "Product Contextual Image", "label" => "Product Contextual Image", "rules" => ["required" => "required"]],
            ["type" => "text", "name" => "imageAlt", "placeholder" => "Product Image Alt", "label" => "Product Image Alt", "rules" => ["required" => "required"]],
            ["type" => "text", "name" => "url", "placeholder" => "Product Url", "label" => "Product Url", "rules" => ["required" => "required"]],
            ["type" => "text", "name" => "categoryPath", "placeholder" => "Product Category", "label" => "Product Category", "rules" => ["required" => "required"]],
            ["type" => "number", "name" => "stock", "placeholder" => "Product Stock (number)", "label" => "Product Stock (number)", "rules" => ["required" => "required"]],
        ];

        $keys = array_keys(($product));
        error_log("keys " . var_export($keys, true));
        foreach ($form as $k => $v) {
            if (in_array($v["name"], $keys)) {
                error_log("IN ARRAY");
                $form[$k]["value"] = $product[$v["name"]];
                error_log("Value: " . var_export($v, true));
            }
        }
        /*error_log("Form full data " . var_export($form, true));*/
    }

//TODO handle manual create product
?>

<div class="container editProd-form shadow"> <!-- card d-flex justify-content-center -->
    <div>
        <a href="<?php echo get_url("admin/list_products.php"); ?>" class="editProd-back btn btn-secondary">Back</a>
    </div>
    <h3 class="editProd_title">Edit Store Item</h3>
    
        <form method="POST">
            <?php foreach ($form as $k => $v) {
                render_input($v);
            } ?>
            <?php /* render_input(["type" => "hidden", "name" => "action", "value" => "update"]); */ ?> 
            <?php render_button(["text" => "Search", "type" => "submit", "text" => "Update"]); ?>
        </form>
</div>



<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>