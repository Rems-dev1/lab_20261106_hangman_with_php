<?php
session_start();
include "functions.php";

// Is somebody logged in? If yes, get their theme and picture.
$loggedIn = isset($_SESSION["username"]);
$picture = "";
if ($loggedIn) {
    $users = read_json(__DIR__ . "/data/users.json");
    $picture = $users[$_SESSION["username"]]["picture"] ?? "";
}
$theme = str_replace("theme-", "", theme_class());
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hangman for Players</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="<?php echo theme_class(); ?>">
  <?php include "nav.php"; ?>
  <h1>Hangman for Players</h1>

<?php if (!$loggedIn) { ?>
  <div class="box">
    <p>Please <a href="login.php">log in</a> to play. You can look at the
       <a href="leaderboard.php">leaderboard</a> without logging in.</p>
  </div>
<?php } else { ?>
  <!-- Game area (only for a logged in player) -->
  <div id="gameArea">
    <div class="box">
      <img id="avatar" class="avatar" alt="Profile picture"
           <?php if ($picture !== "") { ?>src="<?php echo htmlspecialchars($picture); ?>"<?php } else { ?>hidden<?php } ?>>
      <strong id="playerName"><?php echo htmlspecialchars($_SESSION["username"]); ?></strong>
      <p>
        <label for="theme">Theme:</label>
        <select id="theme">
          <option value="light" <?php if ($theme === "light") echo "selected"; ?>>Light</option>
          <option value="dark" <?php if ($theme === "dark") echo "selected"; ?>>Dark</option>
          <option value="ocean" <?php if ($theme === "ocean") echo "selected"; ?>>Ocean</option>
        </select>
      </p>
      <p>
        <label for="pictureInput">Profile picture:</label>
        <input type="file" id="pictureInput" accept="image/*">
      </p>
    </div>

    <div class="box">
      <label for="level">Word list:</label>
      <select id="level">
        <option value="easy">Easy</option>
        <option value="normal">Normal</option>
      </select>
      <button id="startButton">Start</button>
      <button id="endButton" disabled>End</button>
      <button id="hintButton" disabled>Hint (-1 point)</button>

      <p><img id="hangmanImage" src="images/hangman_stage_1.png" alt="Hangman picture" width="150"></p>
      <h2 id="wordDisplay"></h2>
      <p>Wrong guesses: <span id="wrongCount">0</span> / 6</p>
      <p>Points this round: <span id="roundPoints">0</span></p>
      <p id="hintText"></p>
      <div id="letters"></div>
    </div>
  </div>
<?php } ?>

  <p id="message"></p>

<?php if ($loggedIn) { ?>
  <script src="game.js"></script>
<?php } ?>
</body>
</html>
