<?php
$folder = $_POST["folder"];
$len = $_POST["length"];

$items = scandir($folder);


foreach ($items as $item) {
  if (strpos($item,'.m4a') !== false && count($items) >= ($len * .9)) {
    echo json_encode(["success" => "0"]);
    break;
  }
}

echo json_encode(["success" => "1"]);
