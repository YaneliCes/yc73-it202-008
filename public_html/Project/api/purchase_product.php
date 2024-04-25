<?php
// This is an internal API endpoint to receive data and do something with it
// this is not a standalone page
//Note: no nav.php here because this is a temporary stop, it's not a user page
require(__DIR__ . "/../../../lib/functions.php");
session_start();
if (isset($_GET["product_id"]) && is_logged_in()) {
    //TODO implement purchase logic (for now it's all free)
    $db = getDB();
    $query = "INSERT INTO `UserProducts` (user_id, product_id, product_name, price) VALUES (:user_id, :product_id, (SELECT name FROM Products WHERE id = :product_id), (SELECT price FROM Products WHERE id = :product_id))";
    //error_log("Data: " . var_dump($query));
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":user_id" => get_user_id(), ":product_id" => $_GET["product_id"]]);
        flash("Congrats your purchase was successful", "success");
    } catch (PDOException $e) {
        if ($e->errorInfo[1] === 1062) {
            flash("This product isn't available", "danger");
        } else {
            flash("Unhandled error occurred", "danger");
        }
        error_log("Error purchasing product(s): " . var_export($e, true));
    }
}

//for now I'll redirect, but if I later use AJAX I need to send a reply instead
redirect("home.php");