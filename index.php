<?php
ob_start();

putenv("SPOTIFY_CLIENT_ID=9ec000a9a4e04a0caf0fb87de826ef0f");
putenv("SPOTIFY_CLIENT_SECRET=70dc9593647246cb8101f232edcb374c");

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
$transferso = 0;
$transferst = 0;
$lines = 0;
$handle = fopen($file, "r");
while(!feof($handle)){
  $line = fgets($handle);
  if (strpos($line, 'S1, ') !== false) {
    $transferso += 1;
  } else {
    $transferst += 1;
  }
  $lines += 1;
}
fclose($handle);

/*
Echoing out recent logs
*/
if ($lines == 1) {
  $recent = "No recent downloads.";
} else {
  $recentr = file_get_contents("log.txt");
  $recentr = explode("\n", $recentr);
  $recent = "<u>Recent downloads</u><br>";
  $nr = 0;
  foreach ($recentr as $r) {
    if ($nr < 11 && $nr < $lines) {
      $d = explode(", ", $r);
      $recent .=  "'" . $d[3] . "' (" . $d[4] . " songs - " . sizeFormat($d[4]*9e6) . ") [" . $d[0] . "]<br>";
      $nr += 1;
    }
  }
}

/*
Disallowing use of system if too close to quota
*/
$fso = folderSize("/tmp/");
$fst = folderSize("/tmp/");

if ($fso >= 18500000000 && $fst >= 18500000000) {
  $info = "<p>We're terribly sorry, use of the system is suspended for the rest of the day.<br>Our server is full, and we have to wait until playlist archives expire.</p>";
  $no = true;
}
if ($transferso >= 600 && $transferst >= 600) {
  $info = "<p>We're terribly sorry, use of the system is suspended for the rest of the month.<br>We've hit the cap on how much data we can transfer.</p>";
  $no = true;
}

/*
Balancing server load
*/
if ($fso <= $fst) {
  putenv("SPOTIFY_REDIRECT_URI=http://do.zbee.me/in");
} else {
  putenv("SPOTIFY_REDIRECT_URI=http://do.zbee.me/in2");
}

/*
Setting up the Spotify API
*/
$session = new SpotifyWebAPI\Session(getenv('SPOTIFY_CLIENT_ID'), getenv('SPOTIFY_CLIENT_SECRET'), getenv('SPOTIFY_REDIRECT_URI'));
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
    <div class="attrib" data-content="Server 0 - CA, USA | Server 1 - CA, US - <?=sizeFormat($fso)?>/20 GB - <?=$transferso?>/600 transfers | Server 2 - US - <?=sizeFormat($fst)?>/20 GB - <?=$transferst?>/600 transfers">&pi; </div>
    <div id="container">
      <a href='http://do.zbee.me' id='reload'>Spotify Downloader</a>
      <div id="main">
        <p>This service will allow you to download an entire Spotify playlist to save you data and money when you're not at home.</p>
        <p>In order to use this service, you'll need to log into Spotify. We need permission to view your playlists (public and private).</p>
        <a href="<?=$session->getAuthorizeUrl(array('scope' => array('playlist-read-private')))?>">Log in</a>
        <br>
        <a style="background:#404040" href="keepinfo.php">What information is kept about you</a>
      </div>
    </div>
  </body>
</html>
