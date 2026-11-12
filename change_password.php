<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();
include "functions.php";

$message = "";
$changed = false;
$file = __DIR__ . "/data/users.json";
$users = read_json($file);

// Check 1: the player must be logged in (and must be in users.json).
$loggedIn = isset($_SESSION["username"]) && isset($users[$_SESSION["username"]]);

if ($loggedIn && $_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_SESSION["username"];
    $oldPassword = $_POST["old_password"] ?? "";
    $newPassword = $_POST["new_password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if ($oldPassword !== $users[$username]["password"]) {
        // Check 2: the old password must match.
        $message = "The old password is not correct. Nothing was changed.";
    } elseif ($newPassword !== $confirmPassword) {
        // Check 3: the two new passwords must be the same.
        $message = "The two new passwords are not the same. Nothing was changed.";
    } elseif ($newPassword === "") {
        // Check 4: the new password must not be empty.
        $message = "The new password cannot be empty. Nothing was changed.";
    } else {
        // All checks passed: save the new password.
        $users[$username]["password"] = $newPassword;
        write_json($file, $users);
        $message = "Your password was changed.";
        $changed = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Change password</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="<?php echo theme_class(); ?>">
  <?php include "nav.php"; ?>
  <h1>Change password</h1>
  <div class="box">
    <?php if (!$loggedIn) { ?>
      <p class="error">You must be logged in to change your password.</p>
      <p><a href="login.php">Go to the log in page</a></p>
    <?php } else { ?>
      <?php if ($message !== "") { ?>
        <p class="<?php echo $changed ? 'ok' : 'error'; ?>"><?php echo htmlspecialchars($message); ?></p>
      <?php } ?>
      <form method="post" action="change_password.php">
        <p><label for="old_password">Old password:</label>
           <input type="password" id="old_password" name="old_password"></p>
        <p><label for="new_password">New password:</label>
           <input type="password" id="new_password" name="new_password"></p>
        <p><label for="confirm_password">New password again:</label>
           <input type="password" id="confirm_password" name="confirm_password"></p>
        <p><button type="submit">Change password</button></p>
      </form>
    <?php } ?>
  </div>
</body>
</html>
