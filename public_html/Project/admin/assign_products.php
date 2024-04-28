<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

/* yc73 4/1/23 */
if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    //die(header("Location: $BASE_PATH" . "/home.php"));
    redirect("home.php");
}

//build search form
$form = [
    ["type" => "text", "name" => "username", "placeholder" => "Username", "label" => "Username", "include_margin" => false],
    ["type" => "text", "name" => "name", "placeholder" => "Name", "label" => "Product Name", "include_margin" => false],

    ["type" => "number", "name" => "price_min", "placeholder" => "Min Price", "label" => "Min Price", "step" => "0.01", "include_margin" => false],
    ["type" => "number", "name" => "price_max", "placeholder" => "Max Price", "label" => "Max Price", "step" => "0.01", "include_margin" => false],

    ["type" => "text", "name" => "typeName", "placeholder" => "Type", "label" => "Type", "include_margin" => false],

    ["type" => "text", "name" => "categoryPath", "placeholder" => "Category", "label" => "Category", "include_margin" => false],

    ["type" => "select", "name" => "sort", "label" => "Sort", "options" => ["created" => "Created", "modified" => "Modified", "name" => "Name", "price" => "Price"], "include_margin" => false],
    ["type" => "select", "name" => "order", "label" => "Order", "options" => ["desc" => "-", "asc" => "+"], "include_margin" => false],

    ["type" => "number", "name" => "limit", "label" => "Limit", "value" => "25", "include_margin" => false],
];
//error_log("Form data: " . var_export($form, true));


/* yc73 4/26/23 */
//attempt to apply
if (isset($_POST["users"]) && isset($_POST["products"])) {
    $user_ids = $_POST["users"]; //se() doesn't like arrays so we'll just do this
    $product_ids = $_POST["products"]; //se() doesn't like arrays so we'll just do this
    if (empty($user_ids) || empty($product_ids)) {
        flash("Both users and products need to be selected", "warning");
    } else {
        //for sake of simplicity, this will be a tad inefficient
        $db = getDB();
        foreach ($user_ids as $user_id) {
            foreach ($product_ids as $product_id) {
                try {
                    $check_query = "SELECT is_active FROM UserProducts WHERE user_id = :user_id AND product_id = :product_id";
                    $check_stmt = $db->prepare($check_query);
                    $check_stmt->execute([":user_id" => $user_id, ":product_id" => $product_id]);
                    $is_active = $check_stmt->fetchColumn();

                    if ($is_active == 1) {
                        $delete_query = "DELETE FROM UserProducts WHERE user_id = :user_id AND product_id = :product_id";
                        $delete_stmt = $db->prepare($delete_query);
                        $delete_stmt->execute([":user_id" => $user_id, ":product_id" => $product_id]);
                    }

                    else {
                        $query = "INSERT INTO UserProducts (user_id, product_id, product_name, price, is_active) VALUES (:user_id, :product_id, (SELECT name FROM Products WHERE id = :product_id), (SELECT price FROM Products WHERE id = :product_id), 1) 
                        ON DUPLICATE KEY UPDATE is_active = !is_active";
                        $stmt = $db->prepare($query);
                        $stmt->execute([":user_id" => $user_id, ":product_id" => $product_id]);  
                    }
                    flash("Updated product", "success");
                    
                } catch (PDOException $e) {
                    flash("Error updating product", "danger");
                    error_log("Error applying: " . var_export($e->errorInfo, true));
                }
            }
        }
    }
}
else if (isset($_POST["users"]) || isset($_POST["products"])) {
    flash("Both users and products need to be selected", "warning");
}





/* yc73 4/26/23 */
//get products
$get_products = [];
$db = getDB();
$query = "SELECT id, api_id, name, price, measurement, typeName, image, contextualImageUrl, imageAlt, url, categoryPath, stock, is_api 
FROM `Products` WHERE 1=1";

$params = [];
$session_key = $_SERVER["SCRIPT_NAME"];
$is_clear = isset($_GET["clear"]);
if ($is_clear) {
    session_delete($session_key);
    unset($_GET["clear"]);
    redirect($session_key);
} else {
    $session_data = session_load($session_key);
}

if (count($_POST) == 0 && isset($session_data) && count($session_data) > 0) {
    if ($session_data) {
        $_POST = $session_data;
    }
}
if (count($_POST) > 0) {
    session_save($session_key, $_POST);
    $keys = array_keys($_POST);

    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $_POST[$v["name"]];
        }
    }
    
    //product name
    $name = se($_POST, "name", "", false);
    if (!empty($name)) {
        $query .= " AND name like :name";
        $params[":name"] = "%$name%";
    }
  
    //price
    $price_min = se($_POST, "price_min", "-1", false);
    if (!empty($price_min) && $price_min > -1) {
        $query .= " AND price >= :price_min";
        $params[":price_min"] = $price_min;
    }
    $price_max = se($_POST, "price_max", "-1", false);
    if (!empty($price_max) && $price_max > -1) {
        $query .= " AND price <= :price_max";
        $params[":price_max"] = $price_max;
    }

    //product type
    $typeName = se($_POST, "typeName", "", false);
    if (!empty($typeName)) {
        $query .= " AND typeName like :typeName";
        $params[":typeName"] = "%$typeName%";
    }

    //category
    $categoryPath = se($_POST, "categoryPath", "", false);
    if (!empty($categoryPath)) {
        $query .= " AND categoryPath like :categoryPath";
        $params[":categoryPath"] = "%$categoryPath%";
    }

    //sort and order
    $sort = se($_POST, "sort", "created", false);
    if (!in_array($sort, ["name", "price", "typeName", "categoryPath", "created", "modified"])) {
        $sort = "created";
    }

    $order = se($_POST, "order", "desc", false);
    if (!in_array($order, ["asc", "desc"])) {
        $order = "desc";
    }
    //IMPORTANT make sure you fully validate/trust $sort and $order (sql injection possibility)
    $query .= " ORDER BY $sort $order";
    //limit
    try {
        $limit = (int)se($_POST, "limit", "25", false);
    } catch (Exception $e) {
        $limit = 25;
    }
    if ($limit < 1 || $limit > 100) {
        $limit = 25;
    }
    //IMPORTANT make sure you fully validate/trust $limit (sql injection possibility)
    $query .= " LIMIT $limit";
}

else {
    try {
        $limit = (int)se($_POST, "limit", "25", false);
    } catch (Exception $e) {
        $limit = 25;
    }
    if ($limit < 1 || $limit > 100) {
        $limit = 25;
    }
    $query .= " LIMIT $limit";
}
//error_log("Product Query: " . var_export($query));


$stmt = $db->prepare($query);
try {
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
        $get_products = $results;
    }
} catch (PDOException $e) {
    flash(var_export($e->errorInfo, true), "danger");
}





/* yc73 4/26/23 */
//search for user by username
$users = [];
$db = getDB();
$query = "SELECT u.id, username, (SELECT GROUP_CONCAT(name, ' ', ' (', pr.price ,')', ' (' , IF(upr.is_active = 1,'active','inactive') , ')' SEPARATOR ', ') 
FROM UserProducts upr JOIN Products pr on upr.product_id = pr.id 
WHERE upr.user_id = u.id) as product FROM Users u";
$params = [];
$session_key = $_SERVER["SCRIPT_NAME"];
$is_clear = isset($_GET["clear"]);
if ($is_clear) {
    session_delete($session_key);
    unset($_GET["clear"]);
    redirect($session_key);
} else {
    $session_data = session_load($session_key);
}

if (count($_POST) == 0 && isset($session_data) && count($session_data) > 0) {
    if ($session_data) {
        $_POST = $session_data;
    }
}
if (count($_POST) > 0) {
    session_save($session_key, $_POST);
    $keys = array_keys($_POST);

    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $_POST[$v["name"]];
        }
    }
    //username
    $username = se($_POST, "username", "", false);
    if (!empty($username)) {
        $query .= " WHERE u.username like :username";
        $params[":username"] = "%$username%";
    }
}
//error_log("Username Query: " . var_export($query));

$stmt = $db->prepare($query);
try {
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
        $users = $results;
    }
} catch (PDOException $e) {
    flash(var_export($e->errorInfo, true), "danger");
}





?>
<div class="container-fluid arBody">
    <div class="ad-products-header">
        <h1 class="ad-title">Assign Products</h1>
    </div>
    <div class="all-products-container">
        <form method="POST">
            <div class="row mb-3" style="align-items: flex-end;">
                <?php foreach ($form as $k => $v) : ?>
                    <div class="col">
                        <?php render_input($v); ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php render_button(["text" => "Search", "type" => "submit"]); ?>
            <a href="?clear" class="btn btn-secondary">Clear</a>
        </form>

        <form method="POST">
            <?php if (isset($username) && !empty($username)) : ?>
                <input type="hidden" name="username" value="<?php se($username, false); ?>" />
            <?php endif; ?>
            <table class="table">
                <thead class="tableHeader"> 
                    <th>Users</th>
                    <th>Products to Assign</th>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="assignProd-usercontent">
                                <table class="table">
                                    <?php foreach ($users as $user) : ?>
                                        <tr>
                                            <td>
                                                <?php render_input(["type" => "checkbox", "id" => "user_".se($user, 'id', "", false), "name" => "users[]", "label" => se($user, "username", "", false), "value" => se($user, 'id', "", false)]) ?>
                                            </td>
                                            <td><?php se($user, "product", "No Products"); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        </td>
                        <td class="assignTable">
                            <div class="assignProd-content">
                                <table class="table">
                                    <?php foreach ($get_products as $prod) : ?>
                                        <tr>
                                            <td><?php render_input(["type" => "checkbox", "id" => "product_".se($prod, 'id', "", false), "name" => "products[]", "label" => se($prod, "name", "", false), "value" => se($prod, 'id', "", false)]) ?></td>
                                            <td><?php se($prod, "price", "No Info"); ?></td>
                                            <td><?php se($prod, "typeName", "No Info"); ?></td>
                                            <td><?php se($prod, "categoryPath", "No Info"); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="arCont-bottom">
                <?php render_button(["text" => "Toggle Products", "type" => "submit", "color" => "secondary"]); ?>
            </div>
        </form>
    </div>
</div>
<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>