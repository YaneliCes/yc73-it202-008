<?php
require_once(__DIR__ . "/../../partials/nav.php");
?>
<div class="container-fluid loginBody">
    <!-- yc73 4/1/23 -->
    <form onsubmit="return validate(this)" method="POST">
        <div class="loginCont">
            <div class="loginCenter">
                <?php render_input(["type" => "text", "id" => "email", "name" => "email", "label" => "Email/Username", "rules" => ["required" => true]]); ?>
                <?php render_input(["type" => "password", "id" => "password", "name" => "password", "label" => "Password", "rules" => ["required" => true, "minlength" => 8]]); ?>
                <?php render_button(["text" => "Login", "type" => "submit"]); ?>
            </div>
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
        const pw = form.password.value;

        if (email.length === 0) {
            flash("Email/Username must not be empty", "warning");
            is_valid = false;
        }
        if (email.includes("@")) {
            const email_pattern = /^([a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6})*$/;
            if (!email_pattern.test(email)) {
                flash("Invalid email address", "warning");
                is_valid = false;
            }
        }
        else {
            const user_pattern = /^[a-z0-9_-]{3,16}$/;
            if(!user_pattern.test(email)) {
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

        //TODO update clientside validation to check if it should
        //valid email or username
        return is_valid;
    }
</script>
<?php
/* yc73 4/1/23 */
//TODO 2: add PHP Code
if (isset($_POST["email"]) && isset($_POST["password"])) {
    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);

    //TODO 3
    $hasError = false;
    if (empty($email)) {
        flash("Email must not be empty", "danger");
        $hasError = true;
    }
    if (str_contains($email, "@")) {
        //sanitize
        //$email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $email = sanitize_email($email);
        //validate
        /*if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash("Invalid email address");
            $hasError = true;
        }*/
        if (!is_valid_email($email)) {
            flash("Invalid email address", "danger");
            $hasError = true;
        }  
    } else {
        if (!is_valid_username($email)) {
            flash("Invalid username", "danger");
            $hasError = true;
        }
    }
    
    if (empty($password)) {
        flash("Password must not be empty", "danger");
        $hasError = true;
    }
    if (!is_valid_password($password)) {
        flash("Password too short", "danger");
        $hasError = true;
    }
    if (!$hasError) {
        //flash("Welcome, $email");
        //TODO 4
        $db = getDB();
        $stmt = $db->prepare("SELECT id, email, username, password from Users where email = :email or username = :email");
        try {
            $r = $stmt->execute([":email" => $email]);
            if ($r) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $hash = $user["password"];
                    unset($user["password"]);
                    if (password_verify($password, $hash)) {
                        //flash("Weclome $email");
                        $_SESSION["user"] = $user; //sets our session data from db
                        try {
                            //lookup potential roles
                            $stmt = $db->prepare("SELECT Roles.name FROM Roles 
                        JOIN UserRoles on Roles.id = UserRoles.role_id 
                        where UserRoles.user_id = :user_id and Roles.is_active = 1 and UserRoles.is_active = 1");
                            $stmt->execute([":user_id" => $user["id"]]);
                            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC); //fetch all since we'll want multiple
                        } catch (Exception $e) {
                            error_log(var_export($e, true));
                        }
                        //save roles or empty array
                        if (isset($roles)) {
                            $_SESSION["user"]["roles"] = $roles; //at least 1 role
                        } else {
                            $_SESSION["user"]["roles"] = []; //no roles
                        }
                        flash("Welcome, " . get_username());
                        die(header("Location: home.php"));
                    } else {
                        flash("Invalid password", "danger");
                    }
                } else {
                    flash("Email not found", "danger");
                }
            }
        } catch (Exception $e) {
            flash("<pre>" . var_export($e, true) . "</pre>");
        }
    }
}
?>
<?php require_once(__DIR__ . "/../../partials/flash.php");