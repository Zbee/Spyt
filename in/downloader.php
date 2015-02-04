<?php
date_default_timezone_set('America/Denver');

$song = $_POST["song"];
$rname = $_POST["name"];

$name = crc32($_POST["playlist"]);

$url = "https://youtube.com/watch?v=" . $song;
$cmd = 'youtube-dl --audio-format mp3 -o "../tmp/'.$name.'/'.$rname.'.%(ext)s" --extract-audio --no-playlist --max-filesize 50m ' . escapeshellarg($url);
exec($cmd, $output);

echo json_encode(
  [
    "success"=>"2",
    "d"=>"../tmp/".$name,
    "c"=>$output
  ]
);
