<?php
require(__DIR__ . "/../../partials/nav.php");
?>
<h1 class="homeTitle">Home</h1>
<?php

/* yc73 4/1/23 */
if (is_logged_in(true)) {
    //flash("Welcome, " . get_user_email(");
    error_log("Session data: " . var_export($_SESSION, true));
} 
/*else {
    flash("You're not logged in");
}*/
?>
<?php require_once(__DIR__ . "/../../partials/flash.php");