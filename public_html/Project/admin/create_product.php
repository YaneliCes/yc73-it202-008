<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

/* yc73 4/1/23 */
if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}
?>

<?php
    /* yc73 4/12/23 */
    if (isset($_POST["action"])) {
        $action = $_POST["action"];
        $keyword =  strtoupper(se($_POST, "keyword", "", false));
        $apiID =  strtoupper(se($_POST, "api_id", "", false));
        $quote = [];
        if ($action === "fetch") { 
            if ($keyword) {
                $result = fetch_quote($keyword);
                //error_log("Data from API" . var_export($result, true));
                if ($result) {
                    foreach ($result as $key => $item) {
                        $item["is_api"] = 1;
                        $quote[] = $item;
                    }
                }
            }
        } 

        /* yc73 4/12/23 */
        else if ($action === "create") {
            if ($apiID) {
                $productData = [];
                //error_log("post info: " . var_dump($_POST));
                foreach ($_POST as $k => $v) {
                    if (in_array($k, ["api_id", "name", "price", "measurement", "typeName", "image", "contextualImageUrl", "imageAlt", "url", "categoryPath", "stock"])) {
                        $productData[$k] = $v;
                    }
                }
                $productData["is_api"] = 1;
                $quote = [$productData];
                error_log("Cleaned up POST: " . var_export($quote, true));
            }
                //error_log("post info after: " . var_dump($_POST));
        }
        
        else {
            flash("You must provide a keyword", "warning");
        }

        /* yc73 4/12/23 */
        //insert data
/*
        $db = getDB();
        $query = "INSERT INTO `Products` ";
        $columns = [];
        $params = [];
        //per record
        foreach($quote as $index => $row) {
            
            foreach ($row as $k => $v) {
                if($index === 0){
                    $columns[] = $k;
                }
                $params[":$k$index"] = $v;
            }
        }

        //error_log("Data: " . var_dump($quote));

        $query .= "(" . join(",", $columns) . ") ";
        $query .= "VALUES ";

        foreach ($quote as $index => $row) {
            $rowValues = [];
            foreach ($row as $k => $v) {
                $rowValues[] = ":$k$index";
            }
            $query .= "(" . join(",", $rowValues) . ")";
            if ($index < count($quote) - 1) {
                $query .= ",";
            }
        }
        
        error_log("Query: " . $query);
        error_log("Params: " . var_export($params, true));

       if ($action === "fetch") {
            $query .= " ON DUPLICATE KEY update api_id = api_id";
        }

        try {
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            flash("Inserted record " . $db->lastInsertId(), "success");
        } catch (PDOException $e) {
            error_log("Something broke with the query" . var_export($e, true));
            flash("An error occurred", "danger");
        }
*/

        try {
            //optional options for debugging and duplicate handling
            $opts =
                ["debug" => true, "update_duplicate" => false, "columns_to_update" => []];
            $result = insert("Products", $quote, $opts);
            if (!$result) {
                flash("Unhandled error", "warning");
            } else {
                //flash("Created record with id " . var_export($result, true), "success");
                flash("Inserted new product record(s) successfully", "success");
            }
        } catch (InvalidArgumentException $e1) {
            error_log("Invalid arg" . var_export($e1, true));
            flash("Invalid data passed", "danger");
        } catch (PDOException $e2) {
            if ($e2->errorInfo[1] == 1062) {
                flash("An entry for this product already exists", "warning");
            } else {
                error_log("Database error" . var_export($e2, true));
                flash("Database error", "danger");
            }
        } catch (Exception $e3) {
            error_log("Invalid data records" . var_export($e3, true));
            flash("Invalid data records", "danger");
        }
        
    }


//TODO handle manual create product
?>

<div class="container cr-form shadow"> <!-- card d-flex justify-content-center -->
    <h3 class="crProd_title">Add Store Item</h3>
    <ul class="nav nav-pills crProductTabs shadow-sm" id="myTab">
        <li class="nav-item crProdTab1">
            <a class="nav-link active crProd-navlink" data-bs-toggle="pill" type="button" role="tab"
            aria-selected="true" href="#" onclick="switchTab('create')">Fetch</a>
        </li>
        <li class="nav-item crProdTab2">
            <a class="nav-link crProd-navlink" data-bs-toggle="pill" type="button" role="tab"
            aria-selected="false" href="#" onclick="switchTab('fetch')">Create</a>
        </li>
    </ul>
    <div id="fetch" class="tab-target cr-tab-content cr-tab-fetch">
        <form method=POST>                                
                                                           
            <?php render_input(["type" => "search", "name" => "keyword", "placeholder" => "Product", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "hidden", "name" => "action", "value" => "fetch"]); ?>
            <?php render_button(["text" => "Search", "type" => "submit"]); ?>
        </form>
    </div>
    <div id="create" class="tab-target cr-tab-content cr-tab-create" style="display: none;">
        <form onsubmit="return validate(this)" method=POST>

            <?php render_input(["type" => "text", "name" => "api_id", "placeholder" => "Product API ID", "label" => "Product API ID", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "name", "placeholder" => "Product Name", "label" => "Product Name", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "number", "name" => "price", "placeholder" => "Product Price", "label" => "Product Price", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "measurement", "placeholder" => "Product Measurement", "label" => "Product Measurement", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "typeName", "placeholder" => "Product Type (name)", "label" => "Product Type (name)", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "image", "placeholder" => "Product Image", "label" => "Product Image", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "contextualImageUrl", "placeholder" => "Product Contextual Image", "label" => "Product Contextual Image", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "imageAlt", "placeholder" => "Product Image Alt", "label" => "Product Image Alt", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "url", "placeholder" => "Product Url", "label" => "Product Url", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "categoryPath", "placeholder" => "Product Category", "label" => "Product Category", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "number", "name" => "stock", "placeholder" => "Product Stock (number)", "label" => "Product Stock (number)", "rules" => ["required" => "required"]]); ?>


            <?php render_input(["type" => "hidden", "name" => "action", "value" => "create"]); ?>
            <?php render_button(["text" => "Search", "type" => "submit", "text" => "Create"]); ?>
        </form>
    </div>
</div>

<script>
    function validate(form) {
        //TODO 1: implement JavaScript validation
        //ensure it returns false for an error and true for success
        let is_valid = true;
        const api_id = form.api_id.value;
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

        if (api_id.length === 0) {
            flash("API ID must not be empty (JS)", "warning");
            is_valid = false;
        }
        else {
            const apiID_pattern = /^[a-zA-Z0-9]{6,20}$/;
            if (!apiID_pattern.test(api_id)) {
                flash("Invalid API ID: cannot contain symbols and must be 6-20 characters (JS)", "warning");
                is_valid = false;
            }
        }

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
            flash("Category must not be empty", "warning");
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
if (isset($_POST["api_id"]) && isset($_POST["name"]) && isset($_POST["price"]) && isset($_POST["measurement"]) && isset($_POST["typeName"]) && isset($_POST["image"]) && isset($_POST["contextualImageUrl"]) && isset($_POST["imageAlt"]) && isset($_POST["url"]) && isset($_POST["categoryPath"]) && isset($_POST["stock"])) {
    $api_id = se($_POST, "api_id", "", false);
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

    if (empty($api_id)) {
        flash("API ID must not be empty", "danger");
        $hasError = true;
    }
    if (!is_valid_apiID($api_id)) {
        flash("Api ID cannot contain symbols and must be 6-20 characters", "danger");
        $hasError = true;
    }

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


<script>
    function switchTab(tab) {
        let target = document.getElementById(tab);
        if (target) {
            let eles = document.getElementsByClassName("tab-target");
            for(let ele of eles) {
                ele.style.display = (ele.id === tab) ? "none" : "block";
            }
        }
    }

    var triggerTabList = [].slice.call(document.querySelectorAll('#myTab button'))
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl)

        triggerEl.addEventListener('click', function (event) {
            event.preventDefault()
            tabTrigger.show()
        })
    })
</script>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>