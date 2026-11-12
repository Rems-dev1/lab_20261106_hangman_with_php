<?php
// Saves the theme of the logged in player in data/users.json.
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();
include "functions.php";

require_post();
require_login();

$theme = $_POST["theme"] ?? "";
if ($theme !== "light" && $theme !== "dark" && $theme !== "ocean") {
    send_json(400, ["error" => "Please choose light, dark or ocean."]);
}

$file = __DIR__ . "/data/users.json";
$users = read_json($file);
$name = $_SESSION["username"];
if (!isset($users[$name])) {
    send_json(400, ["error" => "Player not found."]);
}
$users[$name]["theme"] = $theme;
write_json($file, $users);

send_json(200, ["ok" => true, "theme" => $theme]);
?>
