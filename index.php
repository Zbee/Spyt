<?php
ob_start();

/*
Max folder size (in bytes)
*/
$mfs = 18e9;

/*
Your API keys
*/
require "_secret_keys.php";

/*
Including libraries
*/
require_once __DIR__.'/vendor/autoload.php';

/*
Required Functions
*/
function folderSize($dir){
  $count_size = 0;
  $count = 0;
  $dir_array = scandir($dir);
    foreach($dir_array as $key=>$filename){
      if($filename!=".." && $filename!="."){
         if(is_dir($dir."/".$filename)){
            $new_foldersize = foldersize($dir."/".$filename);
            $count_size = $count_size+ $new_foldersize;
          }else if(is_file($dir."/".$filename)){
            $count_size = $count_size + filesize($dir."/".$filename);
            $count++;
          }
     }
   }
  return $count_size;
}

function sizeFormat($bytes){
  $kb = 1024;
  $mb = $kb * 1024;
  $gb = $mb * 1024;
  $tb = $gb * 1024;

  if (($bytes >= 0) && ($bytes < $kb)) {
    return $bytes . ' B';
  } elseif (($bytes >= $kb) && ($bytes < $mb)) {
    return ceil($bytes / $kb) . ' KB';
  } elseif (($bytes >= $mb) && ($bytes < $gb)) {
    return ceil($bytes / $mb) . ' MB';
  } elseif (($bytes >= $gb) && ($bytes < $tb)) {
    return ceil($bytes / $gb) . ' GB';
  } elseif ($bytes >= $tb) {
    return ceil($bytes / $tb) . ' TB';
  } else {
    return $bytes . ' B';
  }
}

/*
Number of items in log
*/
$file = "log.txt";
$lines = 0;
$handle = fopen($file, "r");
while(!feof($handle)){
  $line = fgets($handle);
  $lines += 1;
}
fclose($handle);

/*
Echoing out recent logs
*/
if ($lines < 3) {
  $recent = "No recent downloads.";
} else {
  $recentr = file_get_contents("log.txt");
  $recentr = explode("\n", $recentr);
  $recent = "<u>Recent downloads</u><br>";
  $nr = 0;
  foreach ($recentr as $r) {
    if ($nr < 11 && $nr < $lines) {
      $d = explode(", ", $r);
      $recent .=  "'" . $d[1] . "' (" . $d[2] . " - " . sizeFormat($d[2]*9e6) . ") [" . $d[0] . "]<br>";
      $nr += 1;
    }
  }
}

/*
Disallowing use of system if too close to quota
*/
$fs = folderSize("tmp");
if ($fs >= $mfs) {
  $info = "<p>We're terribly sorry, use of the system is suspended for a little while.<br>The server is full and we have to wait until some downloads have finished.</p>";
}

/*
Setting up the Spotify API
*/
$session = new SpotifyWebAPI\Session($SPOTIFY_CLIENT_ID, $SPOTIFY_CLIENT_SECRET, $SPOTIFY_REDIRECT_URL);
$api = new SpotifyWebAPI\SpotifyWebAPI();
?>

<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" type="text/css" href="libs/css/main.css">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
  </head>
  <body>
    <div id="ad"><a href="https://s.zbee.me/bfq" title="AD"><img src="http://i.imgur.com/E2njcd2.png" width="100%"></a></div>
    <div id="recent"><?=$recent?></div>
    <div class="attrib" data-content="Download Server - <?=sizeFormat($fs)?>/20 GB">&pi; </div>
    <div id="container">
      <a href='http://do.zbee.me' id='reload'>Spotify Downloader</a>
      <div id="main">
        <p>This service will allow you to download an entire Spotify playlist to save you data and money when you're not at home.</p>
        <p>In order to use this service, you'll need to log into Spotify. We need permission to view your playlists (public and private).</p>
        <a href="<?=$session->getAuthorizeUrl(array('scope' => array('playlist-read-private')))?>">Log in</a>
      </div>
    </div>
  </body>
</html>
