<?php
date_default_timezone_set('America/Denver');

$song = $_POST["song"];
$rname = $_POST["name"];

$name = crc32($_POST["playlistnice"]);

$url = "https://youtube.com/watch?v=" . $song;
$cmd = 'youtube-dl --audio-format mp3 -o "../tmp/'.$name.'/'.$rname.'.%(ext)s" --extract-audio --no-playlist ' . escapeshellarg($url);
exec($cmd, $output);

echo json_encode(
  [
    "success"=>"2",
    "d"=>"../tmp/".$name,
    "c"=>$output
  ]
);
