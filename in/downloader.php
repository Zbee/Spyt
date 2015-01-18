<?php
date_default_timezone_set('America/Denver');
if (!isset($_POST['songs'])) exit;
$result = $_POST['songs'];

file_put_contents("test.txt", json_encode($_POST));
