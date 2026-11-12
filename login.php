<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();
include "functions.php";

$message = "";

// Run this part only when the form was sent (POST).
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $users = read_json(__DIR__ . "/data/users.json");
    $name = find_user($users, $username);   // capital letters do not matter

    if ($name !== null && $users[$name]["password"] === $password) {
        $_SESSION["username"] = $name;      // remember who is logged in
        header("Location: index.php");
        exit;
    } else {
        $message = "Wrong username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Log in</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="<?php echo theme_class(); ?>">
  <?php include "nav.php"; ?>
  <h1>Log in</h1>
  <div class="box">
    <?php if ($message !== "") { ?>
      <p class="error"><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>
    <form method="post" action="login.php">
      <p><label for="username">Username:</label>
         <input type="text" id="username" name="username"></p>
      <p><label for="password">Password:</label>
         <input type="password" id="password" name="password"></p>
      <p><button type="submit">Log in</button></p>
    </form>
    <p>The test players are in data/users.json.</p>
  </div>
</body>
</html>
