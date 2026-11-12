<?php
// Anyone can open this page, even if they are not logged in.
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();
include "functions.php";

// Put the player with more total points first.
function compare_points($a, $b) {
    return $b["points"] <=> $a["points"];
}

// Add up the points of each player for one level. Give back the top 3.
function get_top_three($results, $level) {
    $totals = [];
    foreach ($results as $result) {
        if ($result["level"] !== $level) {
            continue;
        }
        $name = $result["username"];
        if (!isset($totals[$name])) {
            $totals[$name] = ["username" => $name, "points" => 0];
        }
        $totals[$name]["points"] = $totals[$name]["points"] + $result["points"];
    }
    $totals = array_values($totals);
    usort($totals, "compare_points");
    return array_slice($totals, 0, 3);
}

$results = read_json(__DIR__ . "/data/scores.json");
$users = read_json(__DIR__ . "/data/users.json");
$levels = ["easy" => "Easy", "normal" => "Normal"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Leaderboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="<?php echo theme_class(); ?>">
  <?php include "nav.php"; ?>
  <h1>Leaderboards (top 3 by total points)</h1>

  <?php foreach ($levels as $level => $title) { ?>
    <div class="box">
      <h2><?php echo $title; ?></h2>
      <?php $top = get_top_three($results, $level); ?>
      <?php if (count($top) === 0) { ?>
        <p>No results yet.</p>
      <?php } else { ?>
        <table>
          <tr><th>Rank</th><th>Player</th><th>Total points</th></tr>
          <?php foreach ($top as $index => $row) { ?>
            <tr>
              <td><?php echo $index + 1; ?></td>
              <td>
                <?php
                  $name = $row["username"];
                  if (isset($users[$name]["picture"]) && $users[$name]["picture"] !== "") {
                      echo '<img class="avatar" alt="Profile picture" src="'
                           . htmlspecialchars($users[$name]["picture"]) . '"> ';
                  }
                  echo htmlspecialchars($name);
                ?>
              </td>
              <td><?php echo $row["points"]; ?></td>
            </tr>
          <?php } ?>
        </table>
      <?php } ?>
    </div>
  <?php } ?>
</body>
</html>
