<?php
session_start();
session_destroy();          // forget the logged in player and the round
header("Location: index.php");
exit;
?>
