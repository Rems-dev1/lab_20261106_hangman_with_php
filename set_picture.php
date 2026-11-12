<?php
// Saves the profile picture (as text, like in Week 06) in data/users.json.
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();
include "functions.php";

require_post();
require_login();

$picture = $_POST["picture"] ?? "";
if (!is_string($picture) || strpos($picture, "data:image/") !== 0) {
    send_json(400, ["error" => "Please send a picture file."]);
}
if (strlen($picture) > 100000) {
    send_json(400, ["error" => "That picture is too big. The limit is 100000 characters."]);
}

$file = __DIR__ . "/data/users.json";
$users = read_json($file);
$name = $_SESSION["username"];
if (!isset($users[$name])) {
    send_json(400, ["error" => "Player not found."]);
}
$users[$name]["picture"] = $picture;
write_json($file, $users);

send_json(200, ["ok" => true]);
?>
