<?php
require_once(__DIR__ . "/../../partials/nav.php");
reset_session();
?>
<div class="container-fluid regBody">
    <!-- yc73 4/1/23 -->
    <form onsubmit="return validate(this)" method="POST">
        <div class="regCont">
            <?php render_input(["type"=>"email", "id"=>"email", "name"=>"email", "label"=>"Email", "rules"=>["required"=>true]]);?>
            <?php render_input(["type"=>"text", "id"=>"username", "name"=>"username", "label"=>"Username", "rules"=>["required"=>true, "maxlength"=>30]]);?>
            <?php render_input(["type"=>"password", "id"=>"password", "name"=>"password", "label"=>"Password", "rules"=>["required"=>true, "minlength"=>8]]);?>
            <?php render_input(["type"=>"password", "id"=>"confirm", "name"=>"confirm", "label"=>"Confirm Password", "rules"=>["required"=>true,"minlength"=>8]]);?>
            <?php render_button(["text"=>"Register", "type"=>"submit"]);?>
        </div>
    </form>
</div>
<script>
    /* yc73 4/1/23 */
    function validate(form) {
        //TODO 1: implement JavaScript validation
        //ensure it returns false for an error and true for success
        let is_valid = true;
        const email = form.email.value;
        const user = form.username.value; 
        const pw = form.password.value;
        const pw_confirm = form.confirm.value;

        if (email.length === 0) {
            flash("Email must not be empty", "warning");
            is_valid = false;
        }
        else {
            const email_pattern = /^([a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6})*$/;
            if (!email_pattern.test(email)) {
                flash("Invalid email address", "warning");
                is_valid = false;
            }
        }
        if (user.length === 0) {
            flash("Username must not be empty", "warning");
            is_valid = false;
        } 
        else {
            const user_pattern = /^[a-z0-9_-]{3,16}$/;
            if(!user_pattern.test(user)) {
                flash("Invalid username", "warning");
                is_valid = false;
            }
        }
        if (pw.length === 0) {
            flash("Password must not be empty", "warning");
            is_valid = false;
        }
        else {
            const pw_pattern = /^.{8,}/;
            if(!pw_pattern.test(pw)){
                flash("Password too short", "warning");
                is_valid = false;
            }
        }
        if (pw_confirm.length === 0) {
            flash("Confirm password must not be empty", "warning");
            is_valid = false;
        }
        if (pw.length > 0 && pw !== pw_confirm) {
            flash ("Passwords must match", "warning");
            is_valid = false;
        }

        return is_valid;
    }
</script>
<?php
/* yc73 4/1/23 */
//TODO 2: add PHP Code
if (isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["confirm"]) && isset($_POST["username"])) {
    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);
    $confirm = se($_POST, "confirm", "", false);
    $username = se($_POST, "username", "", false);

    //TODO 3
    $hasError = false;
    if (empty($email)) {
        flash("Email must not be empty", "danger");
        $hasError = true;
    }

    //sanitize
    $email = sanitize_email($email);
    //validate
    if (!is_valid_email($email)) {
        flash("Invalid email address", "danger");
        $hasError = true;
    }

    if (!is_valid_username($username)) {
        flash("Username must only contain 3-16 characters a-z, 0-9, _, or -", "danger");
        $hasError = true;
    }
    if (empty($password)) {
        flash("password must not be empty", "danger");
        $hasError = true;
    }
    if (empty($confirm)) {
        flash("Confirm password must not be empty", "danger");
        $hasError = true;
    }
    if (!is_valid_password($password)) {
        flash("Password too short", "danger");
        $hasError = true;
    }
    if (strlen($password) > 0 && $password !== $confirm) {
        flash("Passwords must match", "danger");
        $hasError = true;
    }
    if (!$hasError) {
        //TODO 4
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO Users (email, password, username) VALUES(:email, :password, :username)");
        try {
            $stmt->execute([":email" => $email, ":password" => $hash, ":username" => $username]);
            flash("Successfully registered!", "success");
        } catch (PDOException $e) {
            users_check_duplicate($e->errorInfo);
        }
    }
}
?>
<?php
require(__DIR__ . "/../../partials/flash.php");
?>