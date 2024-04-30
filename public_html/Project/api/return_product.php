<?php
// This is an internal API endpoint to receive data and do something with it
// this is not a standalone page
//Note: no nav.php here because this is a temporary stop, it's not a user page

/* yc73 4/25/23 */
//remove single association
require(__DIR__ . "/../../../lib/functions.php");
session_start();
if (isset($_GET["product_id"]) && is_logged_in()) {
    $db = getDB();
    
    //note for me: this part grabs user id associated with the product from UserProducts
    $query = "SELECT user_id FROM `UserProducts` WHERE product_id = :product_id";
    $stmt = $db->prepare($query);
    $stmt->execute([":product_id" => $_GET["product_id"]]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    //error_log("Query RM: " . var_export($result, true));

    if ($result) {
        $user_id = $result["user_id"];
        //note for me: this deletes using that grabbed user id
        $query = "DELETE FROM `UserProducts` WHERE user_id = :user_id AND product_id = :product_id";
        $params = [":user_id" => $user_id, ":product_id" => $_GET["product_id"]];
        //error_log("Params RM: " . var_export($params, true));
        try {
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            flash("Successfully returned product", "success");
        } catch (PDOException $e) {
            error_log("Error removing product associations: " . var_export($e, true));
            flash("Error returning product", "danger");
        }
    } else {
        flash("Product not found", "danger");
    }
}

//for now I'll redirect, but if I later use AJAX I need to send a reply instead
//redirect("order_history.php");

//got help from: https://stackoverflow.com/questions/2548566/go-back-to-previous-page 
//got help from: https://stackoverflow.com/questions/3560611/get-path-from-url#:~:text=Use%20parse_url%20to%20extract%20the%20information%20you%20want.,%22%2Fpwsdedtech%22%20%24pathWithoutSlash%20%3D%20substr%28%24path%2C%201%29%3B%20%2F%2F%20gives%20%22pwsdedtech%22
//so i can go back to the back to the last page
$path = parse_url($_SERVER["HTTP_REFERER"], PHP_URL_PATH);
redirect($path);