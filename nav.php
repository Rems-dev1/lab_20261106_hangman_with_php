<?php
// The menu shown at the top of every page.
// The page that includes this file must call session_start() first.
?>
<div id="nav">
  <a href="index.php">Game</a> |
  <a href="leaderboard.php">Leaderboard</a> |
  <?php if (isset($_SESSION["username"])) { ?>
    Logged in as <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong> |
    <a href="change_password.php">Change password</a> |
    <a href="logout.php">Log out</a>
  <?php } else { ?>
    <a href="login.php">Log in</a>
  <?php } ?>
</div>
