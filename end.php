<?php
// The player ends the round early. It is saved as "ended", not a win.
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();
include "functions.php";

require_get();
require_login();

if (!isset($_SESSION["round"]) || $_SESSION["round"]["over"]) {
    send_json(400, ["error" => "There is no round running. Press Start."]);
}

$_SESSION["round"]["over"] = true;
$_SESSION["round"]["result"] = "ended";

$round = $_SESSION["round"];
save_result($_SESSION["username"], $round["level"], round_points($round), $round["result"]);

send_json(200, make_state($round));
?>
