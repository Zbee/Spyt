<?php
$folder = $_POST["folder"];
$len = $_POST["length"];
$echoed = false;

if (is_file($folder.".tar")) {
  echo json_encode(["success" => "4"]);
  $echoed = true;
}

if (!is_dir($folder)) {
  if (!$echoed ) { echo json_encode(["success" => "0"]); $echoed = true; }
} else {
  $items = scandir($folder);

  foreach ($items as $item) {
    if (strpos($item,'.m4a') !== false) {
      if (!$echoed ) { echo json_encode(["success" => "1"]); $echoed = true;}
      break;
    }
  }

  if (!$echoed && ceil(count($items)) >= floor($len * .9)) { echo json_encode(["success" => "2"]); $echoed = true; }
}

if (!$echoed ) { echo json_encode(["success" => "3"]); }
