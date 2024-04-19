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

    if (isset($_POST["name"])) {
        foreach ($_POST as $k => $v) {
            /*error_log("POST" . var_export($_POST, true));*/
            if (!in_array($k, [/*"api_id",*/ "name", "price", "measurement", "typeName", "image", "contextualImageUrl", "imageAlt", "url", "categoryPath", "stock"])) {
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
        $query = "SELECT /*api_id,*/ name, price, measurement, typeName, image, contextualImageUrl, imageAlt, url, categoryPath, stock FROM `Products` WHERE id = :id";
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
    
        <form onsubmit="return validate(this)" method="POST">
            <?php foreach ($form as $k => $v) {
                render_input($v);
            } ?>
            <?php /* render_input(["type" => "hidden", "name" => "action", "value" => "update"]); */ ?> 
            <?php render_button(["text" => "Search", "type" => "submit", "text" => "Update"]); ?>
        </form>
</div>

<script>
    function validate(form) {
        //TODO 1: implement JavaScript validation
        //ensure it returns false for an error and true for success
        let is_valid = true;
        const name = form.name.value; 
        const price = form.price.value;
        const measurement = form.measurement.value;
        const typeName = form.typeName.value; 
        const img = form.image.value;
        const contextualImageUrl = form.contextualImageUrl.value; 
        const alt = form.imageAlt.value;
        const url = form.url.value; 
        const category = form.categoryPath.value;
        const stock = form.stock.value; 


        if (name.length === 0) {
            flash("Product name must not be empty (JS)", "warning");
            is_valid = false;
        } 
        else {
            const name_pattern = /^[^\d]{1,32}$/;
            if(!name_pattern.test(name)) {
                flash("Invalid product name: no numbers, only up to 32 characters (JS)", "warning");
                is_valid = false;
            }
        }

        if (price.length === 0) {
            flash("Price must not be empty (JS)", "warning");
            is_valid = false;
        }
        else {
            const price_pattern = /^(?:\d{1,7}|\d{1,5}\.\d{1,2})$/;
            if(!price_pattern.test(price)){
                flash("Invalid price format: only up to 7 digits (JS) (ex: XXXXXXX or XXXXX.XX)", "warning");
                is_valid = false;
            }
        }
        
        if (measurement.length === 0) {
            flash("Measurement must not be empty (JS)", "warning");
            is_valid = false;
        }
        else {
            const measurement_pattern = /^[^\\"'@#$%^*()?<>~`|+\-,_=;:;\[\]{}]{1,75}$/;
            if (!measurement_pattern.test(measurement)) {
                flash("Invalid measurement: no symbols besides / and only up to 75 characters (JS)", "warning");
                is_valid = false;
            }
        }
        
        if (typeName.length === 0) {
            flash("Product type must not be empty (JS)", "warning");
            is_valid = false;
        } 
        else {
            const typeName_pattern = /^.{1,100}$/;
            if(!typeName_pattern.test(typeName)) {
                flash("Invalid product type: only up to 100 characters (JS)", "warning");
                is_valid = false;
            }
        }

        if (img.length === 0) {
            flash("Image url must not be empty (JS)", "warning");
            is_valid = false;
        } 
        else {
            const img_pattern = /(https?:\/\/)?(www\.)[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/;
            if(!img_pattern.test(img)) {
                flash("Invalid image url (JS)", "warning");
                is_valid = false;
            }
        }

        if (contextualImageUrl.length === 0) {
            flash("Contextual image url must not be empty (JS)", "warning");
            is_valid = false;
        } 
        else {
            const contextualImageUrl_pattern = /(https?:\/\/)?(www\.)[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/;
            if(!contextualImageUrl_pattern.test(contextualImageUrl)) {
                flash("Invalid contextual image url (JS)", "warning");
                is_valid = false;
            }
        }
        
        if (alt.length === 0) {
            flash("Image alt text must not be empty (JS)", "warning");
            is_valid = false;
        }
        
        if (url.length === 0) {
            flash("Product url must not be empty (JS)", "warning");
            is_valid = false;
        } 
        else {
            const url_pattern = /(https?:\/\/)?(www\.)[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/;
            if(!url_pattern.test(url)) {
                flash("Invalid product url (JS)", "warning");
                is_valid = false;
            }
        }
        
        if (category.length === 0) {
            flash("Category must not be empty (JS)", "warning");
            is_valid = false;
        }
        else {
            const category_pattern = /^[\w\s&]{1,100}$/;
            if(!category_pattern.test(category)) {
                flash("Invalid category path: no symbols besides _ and &, only up to 100 characters (JS)", "warning");
                is_valid = false;
            }
        }

        if (stock.length === 0) {
            flash("Stock must not be empty (JS)", "warning");
            is_valid = false;
        } 
        else {
            const stock_pattern = /^\d+$/;
            if(!stock_pattern.test(stock)) {
                flash("Invalid stock: only numbers (JS)", "warning");
                is_valid = false;
            }
        }

        return is_valid;
    }
</script>


<?php
/* yc73 4/15/23 */
//TODO 2: add PHP Code
if (isset($_POST["name"]) && isset($_POST["price"]) && isset($_POST["measurement"]) && isset($_POST["typeName"]) && isset($_POST["image"]) && isset($_POST["contextualImageUrl"]) && isset($_POST["imageAlt"]) && isset($_POST["url"]) && isset($_POST["categoryPath"]) && isset($_POST["stock"])) {
    $name = se($_POST, "name", "", false);
    $price = se($_POST, "price", "", false);
    $measurement = se($_POST, "measurement", "", false);
    $typeName = se($_POST, "typeName", "", false);
    $image = se($_POST, "image", "", false);
    $contextualImageUrl = se($_POST, "contextualImageUrl", "", false);
    $imageAlt = se($_POST, "imageAlt", "", false);
    $url = se($_POST, "url", "", false);
    $categoryPath = se($_POST, "categoryPath", "", false);
    $stock = se($_POST, "stock", "", false);

    //TODO 3
    $hasError = false;

    if (empty($name)) {
        flash("Product name must not be empty", "danger");
        $hasError = true;
    }
    if (!is_valid_productName($name)) {
        flash("Invalid product name: no numbers, only up to 32 characters", "danger");
        $hasError = true;
    }

    if (empty($price)) {
        flash("Price must not be empty", "danger");
        $hasError = true;
    }
    if (!is_valid_price($price)) {
        flash("Invalid price format: only up to 7 digits (ex: XXXXXXX or XXXXX.XX)", "danger");
        $hasError = true;
    }

    if (empty($measurement)) {
        flash("Measurement must not be empty", "danger");
        $hasError = true;
    }
    if (!is_valid_measurement($measurement)) {
        flash("Invalid measurement: no symbols besides / and only up to 75 characters", "danger");
        $hasError = true;
    }

    if (empty($typeName)) {
        flash("Product type must not be empty", "danger");
        $hasError = true;
    }
    if (!is_valid_productType($typeName)) {
        flash("Invalid product type: only up to 100 characters", "danger");
        $hasError = true;
    }
        
    if (empty($image)) {
        flash("Image url must not be empty", "danger");
        $hasError = true;
    }
    if (!is_valid_url($image)) {
        flash("Invalid image url", "danger");
        $hasError = true;
    }

    if (empty($contextualImageUrl)) {
        flash("Contextual image url must not be empty", "danger");
        $hasError = true;
    } 
    if (!is_valid_url($contextualImageUrl)) {
        flash("Invalid contextual image url", "danger");
        $hasError = true;
    }

    if (empty($imageAlt)) {
        flash("Image alt text must not be empty", "danger");
        $hasError = true;
    }

    if (empty($url)) {
        flash("Product url must not be empty", "danger");
        $hasError = true;
    }
    if (!is_valid_url($url)) {
        flash("Invalid product url", "danger");
        $hasError = true;
    }
    
    if (empty($categoryPath)) {
        flash("Category must not be empty", "danger");
        $hasError = true;
    }
    if (!is_valid_category($categoryPath)) {
        flash("Invalid category path: no symbols besides _ and &, only up to 100 characters", "danger");
        $hasError = true;
    }

    if (empty($stock)) {
        flash("Stock must not be empty", "danger");
        $hasError = true;
    }
    if (!is_valid_stock($stock)) {
        flash("Invalid stock: only numbers", "danger");
        $hasError = true;
    }
}
?>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>