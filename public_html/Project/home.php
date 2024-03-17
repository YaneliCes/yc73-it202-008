<?php
require(__DIR__ . "/../../partials/nav.php");
?>
<h1>Home</h1>
<?php
if (is_logged_in(true)) {
    //flash("Welcome, " . get_user_email(");
    error_log("Session data: " . var_export($_SESSION, true));
} 
/*else {
    flash("You're not logged in");
}*/
?>
<?php require_once(__DIR__ . "/../../partials/flash.php");