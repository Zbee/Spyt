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
  if ((count($tracks[$thisID])+1)*2.5e6 > $mfs || count($tracks[$thisID]) < 2) {
    $body .= '<a title="' . sizeFormat((count($tracks[$thisID])+1)*2.5e6) . '" data-id="' . $thisID . '" id="' . $thisID . '" class="no">' . $playlist->name . '</a><br>';
  } else {
    $body .= '<a title="' . sizeFormat((count($tracks[$thisID])+1)*2.5e6) . '" data-id="' . $thisID . '" id="' . $thisID . '">' . $playlist->name . '</a><br>';
    $body .= '
      <script>
      $("#' . $thisID . '").click(function() {
        $("#info").html("<div class=\'bar\'><span></span></div><br>Right now we\'re looking for all of the song\'s MP3s.<br>(This should only take a minute or two)");
        $.ajax({
          type: "POST",
          url: "getter.php",
          data: {playlist: "' . $thisID . '", playlistnice: "' . $playlist->name . '", songs: JSON.stringify(' . json_encode($tracks[$thisID]) . ')},
          dataType: "json",
          context: document.body,
          async: true,
          complete: function(res, stato) {
            if (res.responseJSON.success == "2") {
              $("#info").html("<div class=\'bar\'><span></span></div><br>We finished finding all of the songs\' MP3s!<br>Now we\'re downloading them all ...<br>(This could take quite some time, leave this tab open)");
              var arr = res.responseJSON.codes;
              var len = arr.length;
              var yes = 1;
              $.each(arr, function(index, value) {
                $.ajax({
                  type: "POST",
                  url: "downloader.php",
                  data: {playlist: "' . $thisID . '", playlistnice: "' . $playlist->name . '", song: JSON.stringify(value)},
                  dataType: "json",
                  context: document.body,
                  async: true,
                  complete: function(res, stato) {
                    if (res.responseJSON.success == "2" && yes != len) {
                      $("#info").html("<div class=\'bar\'><span></span></div><br>We finished finding all of the songs\' MP3s!<br>Now we\'re downloading them all ...<br>(This could take quite some time, leave this tab open)");
                      yes += 1;
                    } else if (res.responseJSON.success == "2" && yes == len) {
                      $("#info").html("All of your songs have finished downloading to our servers.<br><a target=\'_blank\' href=\'download.php?d=" + res.responseJSON.d + "\'>Download playlist</a>");
                    }
                  }
                });
              });
            } else if (res.responseJSON.success == "1") {
              $("#info").html("aw");
            } else {
              $("#info").html("AHHHH!");
            }
            //$("#info").html(JSON.stringify(res));
          }
        });
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
    <div class="attrib" data-content="Download Server - <?=sizeFormat(folderSize("../tmp"))?>/20 GB">&pi; </div>
    <div id="container">
      <a href='http://do.zbee.me' id='reload'>Spotify Downloader</a>
      <div id="main">
        <?=$info?>
        <?=$body?>
      </div>
    </div>
  </body>
</html>
