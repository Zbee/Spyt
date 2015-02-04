<?php
ob_start();

$body = "";
$info = "<p id='info'>Choose one of your playlists below to begin downloading it.</p>";

/*
Max file size (in bytes)
*/
$mfs = 5e9;

/*
Your API keys
*/
require "../_secret_keys.php";

/*
Including libraries
*/
require_once __DIR__.'/../vendor/autoload.php';

/*
Required Functions
*/
function scrub ($name) {
  $name = preg_replace_callback('/[\x{80}-\x{10FFFF}]/u', function($match) {
    list($utf8) = $match;
    $entity = mb_convert_encoding($utf8, 'HTML-ENTITIES', 'UTF-8');
    return $entity;
  }, $name);
  $name = str_replace("EP", "", $name);
  $name = str_replace(";explicit", "", $name);
  $name = preg_replace("/\([^)]{25,}\)/","", $name);
  $name = preg_replace("/\[[^]]{25,}\]/","", $name);
  $name = str_replace("'", "", $name);
  $name = trim($name);
  return $name;
}

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

function dirsize($a) {
  $b=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($a));
  $c = 0;
  foreach($b as $d){
    $c+=$d->getSize();
  }
  return $c;
}

$fs = dirsize("../tmp");

/*
Setting up the Spotify API
*/
$session = new SpotifyWebAPI\Session($SPOTIFY_CLIENT_ID, $SPOTIFY_CLIENT_SECRET, $SPOTIFY_REDIRECT_URL);
$api = new SpotifyWebAPI\SpotifyWebAPI();

if (!isset($_GET['code'])) {
  $_GET['code'] = null;
}
try {
  $session->requestToken($_GET['code']);
  $api->setAccessToken($session->getAccessToken());
} catch (Exception $e) {
  header('Location: ' . $session->getAuthorizeUrl(array('scope' => array('playlist-read-private'))));
}

$id = $api->me()->id;

$playlists = $api->getUserPlaylists($id, array('limit' => 10));

/*
Sorting contents of Spotify playlists
*/
foreach ($playlists->items as $playlist) {
  $thisID = explode("/", $playlist->external_urls->spotify)[6];
  $tracks[$thisID] = array();
  $n = ceil($playlist->tracks->total/100)+2;
  for ($x = 0; $x < $n; $x++) {
    $t = $x==0 ? 0 : $x*100;
    $apic = $api->getUserPlaylistTracks($id, $thisID, array("offset"=>$t,"limit"=>100));
    foreach ($apic as $a) {
      if (is_array($a)) {
        foreach ($a as $track) {
          if (!in_array((scrub($track->track->name) . " - " . $track->track->artists[0]->name), $tracks[$thisID])) {
            array_push($tracks[$thisID], (scrub($track->track->name) . " - " . $track->track->artists[0]->name));
          }
        }
      }
    }
  }
  if ((count($tracks[$thisID])+1)*3.2e6 > $mfs || count($tracks[$thisID]) < 2) {
    $body .= '<a title="' . sizeFormat((count($tracks[$thisID])+1)*3.2e6) . '" data-id="' . $thisID . '" id="' . $thisID . '" class="no">' . $playlist->name . '</a><br>';
  } else {
    $body .= '<a title="' . sizeFormat((count($tracks[$thisID])+1)*3.2e6) . '" data-id="' . $thisID . '" id="' . $thisID . '">' . $playlist->name . '</a><br>';
    $body .= '
      <script>
      $("#' . $thisID . '").click(function() {
        var len = ' . count($tracks[$thisID]) . ';
        arr = ' . json_encode($tracks[$thisID]) . ';
        for (var i = 0; i < len; i++) {
          $.ajax({
            type: "POST",
            url: "getter.php",
            data: {playlist: "' . $thisID . '", playlistnice: "' . $playlist->name . '", song: arr[i]},
            dataType: "json",
            context: document.body,
            async: false, //Must not be async, otherwise `arr[i]` is undefined
            complete: function(res, stato) {
              res.responseJSON.song = arr[i];
              console.log(res.responseJSON);
              if (res.responseJSON.success == "2") {
                $("#info").html("The songs are now downloading.");
                $.ajax({
                  type: "POST",
                  url: "downloader.php",
                  data: {playlist: "' . $thisID . '", playlistnice: "' . $playlist->name . '", song: res.responseJSON.code, name: arr[i]},
                  dataType: "json",
                  context: document.body,
                  async: true
                });
              } else if (res.responseJSON.success == "1") {
                $("#info").html("We failed to find the song on Youtube.");
              } else {
                $("#info").html("AHHHH! I DON\'T KNOW WHAT HAPPENED!<Br>" + JSON.stringify(res));
              }
            }
          });
          if (i+1 == len) {
            $("#info").html("All songs are now downloading.");
            window.setInterval(function(){
              $.ajax({
                type: "POST",
                url: "checker.php",
                data: {folder: "../tmp/' . crc32($thisID) . '", length: len},
                dataType: "json",
                context: document.body,
                async: false,
                complete: function(res, stato) {
                  if (res.responseJSON.success == "2") {
                    $("#info").html("All songs have been downloaded.<br><a target=\'_blank\' href=\'download.php?d=../tmp/' . crc32($thisID) . '&n=' . scrub($playlist->name) . '\'>Download playlist</a>");
                  } else if (res.responseJSON.success == "1") {

                  } else if (res.responseJSON.success == "4" || $("#info").text().indexOf("Enoy!") > -1) {
                    $("#info").html("Your download has begun.<br>Enjoy!");
                  } else {
                    $("#info").html("AHHHHH! I DUNNO WHAT HAPPENED!<Br>" + JSON.stringify(res));
                  }
                }
              });
            }, 15000);
          }
        }
      });
      </script>
    ';
  }
}
?>

<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" type="text/css" href="../libs/css/main.css">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
  </head>
  <body>
    <div class="attrib" data-content="<?=sizeFormat($fs)?>/<?=sizeformat($mfs)?>">&pi; </div>
    <div id="container">
      <a href="../" id="reload">Spyt</a>
      <div id="main">
        <?=$info?>
        <?=$body?>
      </div>
    </div>
  </body>
</html>
