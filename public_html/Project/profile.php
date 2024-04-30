<?php
require_once(__DIR__ . "/../../partials/nav.php");
/* yc73 4/25/23 */
//is_logged_in(true);
$user_id = -1;
try {
    $user_id = (int)se($_GET, "id", -1, false);
} catch (Exception $e) {
    //we know it's a data format issue
}
if ($user_id < 1) {
    $user_id = get_user_id(); //get our id if we're logged in
}
$is_me = $user_id == get_user_id();
$is_edit = isset($_GET["edit"]);
?>
<?php
if ($is_me && $is_edit && isset($_POST["save"])) {
    $email = se($_POST, "email", null, false);
    $username = se($_POST, "username", null, false);
    $hasError = false;
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
    if (!$hasError) {
        $params = [":email" => $email, ":username" => $username, ":id" => get_user_id()];
        $db = getDB();
        $stmt = $db->prepare("UPDATE Users set email = :email, username = :username where id = :id");
        try {
            $stmt->execute($params);
            flash("Profile saved", "success");
        } catch (PDOException $e) {
            users_check_duplicate($e->errorInfo);
        }
        //select fresh data from table
        $stmt = $db->prepare("SELECT id, email, username from Users where id = :id LIMIT 1");
        try {
            $stmt->execute([":id" => get_user_id()]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                //$_SESSION["user"] = $user;
                $_SESSION["user"]["email"] = $user["email"];
                $_SESSION["user"]["username"] = $user["username"];
            } else {
                flash("User doesn't exist", "danger");
            }
        } catch (Exception $e) {
            flash("An unexpected error occurred, please try again", "danger");
            //echo "<pre>" . var_export($e->errorInfo, true) . "</pre>";
        }
    }


    //check/update password
    $current_password = se($_POST, "currentPassword", null, false);
    $new_password = se($_POST, "newPassword", null, false);
    $confirm_password = se($_POST, "confirmPassword", null, false);
    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        $hasError = false;
        if (!is_valid_password($new_password)) {
            flash("Password too short", "danger");
            $hasError = true;
        }
        if (!$hasError) {
            if ($new_password === $confirm_password) {
                //TODO validate current
                $stmt = $db->prepare("SELECT password from Users where id = :id");
                try {
                    $stmt->execute([":id" => get_user_id()]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (isset($result["password"])) {
                        if (password_verify($current_password, $result["password"])) {
                            $query = "UPDATE Users set password = :password where id = :id";
                            $stmt = $db->prepare($query);
                            $stmt->execute([
                                ":id" => get_user_id(),
                                ":password" => password_hash($new_password, PASSWORD_BCRYPT)
                            ]);

                            flash("Password reset", "success");
                        } else {
                            flash("Current password is invalid", "warning");
                        }
                    }
                } catch (PDOException $e) {
                    echo "<pre>" . var_export($e->errorInfo, true) . "</pre>";
                }
            } else {
                flash("New passwords don't match", "warning");
            }
        }
    }
}
?>

<?php
/* yc73 4/25/23 */
$user = [];
if ($user_id > 0) {
    $db = getDB();
    $query = "SELECT email, username, created FROM Users where id = :user_id";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":user_id" => $user_id]);
        $r = $stmt->fetch();
        if ($r) {
            $user = $r;
        } else {
            flash("Couldn't find user profile", "warning");
        }
    } catch (PDOException $e) {
        error_log("Error fetching user: " . var_export($e, true));
        flash("Error fetching user", "danger");
    }
}
?>

<!-- yc73 4/26/23 -->
<div class="container-fluid profileBody">
        <?php if ($is_me && $is_edit) : ?>
            <div class ="profile-edit">
                <a class="btn btn-secondary btn-sm prof-view-btn" href="?">View</a>
                <form method="POST" onsubmit="return validate(this);">
                    <div class="profileCont">
                        <?php render_input(["type" => "email", "id" => "email", "name" => "email", "label" => "Email", "value" => se($user, "email", "", false), "rules" => ["required" => true]]); ?>
                        <?php render_input(["type" => "text", "id" => "username", "name" => "username", "label" => "Username", "value" => se($user, "email", "", false), "rules" => ["required" => true, "maxlength" => 30]]); ?>
                        <!-- DO NOT PRELOAD PASSWORD -->
                        <div class="lead pwLabel">Password Reset</div>
                        <?php render_input(["type" => "password", "id" => "cp", "name" => "currentPassword", "label" => "Current Password", "rules" => ["minlength" => 8]]); ?>
                        <?php render_input(["type" => "password", "id" => "np", "name" => "newPassword", "label" => "New Password", "rules" => ["minlength" => 8]]); ?>
                        <?php render_input(["type" => "password", "id" => "conp", "name" => "confirmPassword", "label" => "Confirm Password", "rules" => ["minlength" => 8]]); ?>
                        <?php render_input(["type" => "hidden", "name" => "save"]);/*lazy value to check if form submitted, not ideal*/ ?>
                        <?php render_button(["text" => "Update Profile", "type" => "submit"]); ?>
                </form>
            </div>
        <?php else : ?>
            <div class ="profile-view">
                <a href="<?php echo get_url("home.php"); ?>" class="btn btn-secondary btn-sm prof-edit-btn" style="margin-right: 1%;">Back</a>
                <?php if ($is_me) : ?>
                    <a class="btn btn-primary btn-sm prof-edit-btn" href="?edit">Edit</a>
                <?php endif; ?>
                <div class="card">
                    <div class="card-body">
                        <div class="h4">Username: <?php se($user, "username"); ?></div>
                        <div class="text-body">Joined: <?php se($user, "created"); ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    
</div>

<script>
    function validate(form) {
        let isValid = true;
        let pw = form.newPassword.value;
        let con = form.confirmPassword.value;
        
        //TODO add other client side validation....
        let email = form.email.value;
        let user = form.username.value; 

        if (email.length === 0) {
            flash("Email must not be empty", "warning");
            isValid = false;
        }
        else {
            const email_pattern = /^([a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6})*$/;
            if (!email_pattern.test(email)) {
                flash("Invalid email address", "warning");
                isValid = false;
            }
        }
        if (user.length === 0) {
            flash("Username must not be empty", "warning");
            isValid = false;
        } 
        else {
            const user_pattern = /^[a-z0-9_-]{3,16}$/;
            if(!user_pattern.test(user)) {
                flash("Invalid username", "warning");
                isValid = false;
            }
        }

        //example of using flash via javascript
        //find the flash container, create a new element, appendChild
        if (pw.length !== 0 || con.length !== 0) {
            const pw_pattern = /^.{8,}/;
            if(!pw_pattern.test(pw)){
                flash("Password too short", "warning");
                isValid = false;
            }
        }
        if (pw.length > 0 && pw !== con) {
            flash("Password and Confrim password must match", "warning");
            isValid = false;
        }

        return isValid;
    }
</script>
<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>