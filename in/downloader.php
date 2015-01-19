<?php
date_default_timezone_set('America/Denver');
$song = json_decode($_POST['song']);

$name = crc32($_POST["playlistnice"]);

require_once __DIR__.'/../vendor/autoload.php';
use Youtubedl\Youtubedl;

$youtubedl=new Youtubedl();
$youtubedl->getFilesystemOption()
  ->setOutput('"/tmp/'.$name.'/%(title)s.%(ext)s"');
$youtubedl->download($song);

echo json_encode(
  [
    "success"=>"2",
    "d"=>"/tmp/".$name
  ]
);
