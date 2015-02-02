<?php
date_default_timezone_set('America/Denver');
$song = json_decode($_POST['song']);

$name = crc32($_POST["playlistnice"]);

$url = "https://youtube.com/watch?v=" . $song;
$cmd = 'youtube-dl --audio-format mp3 -o "../tmp/'.$name.'/%(title)s.%(ext)s" --extract-audio ' . escapeshellarg($url);
exec($cmd, $output);

echo json_encode(
  [
    "success"=>"2",
    "d"=>"../tmp/".$name,
    "c"=>$output
  ]
);
