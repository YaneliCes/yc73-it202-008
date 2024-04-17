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
        $db = getDB();
        $query = "INSERT INTO `ProductSample` ";
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
        <form method=POST>

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