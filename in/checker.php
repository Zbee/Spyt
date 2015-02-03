<?php
$folder = $_POST["folder"];

$items = scandir($folder);

foreach ($items as $item) {
  if (strpos($item,'.m4a') !== false) {
    echo json_encode(["success" => "0"]);
    break;
  }
}

echo json_encode(["success" => "1"]);
