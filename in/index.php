<?php
error_reporting(-1);
ini_set('display_errors',-1);
ob_start();

putenv("SPOTIFY_CLIENT_ID=9ec000a9a4e04a0caf0fb87de826ef0f");
putenv("SPOTIFY_CLIENT_SECRET=70dc9593647246cb8101f232edcb374c");
putenv("SPOTIFY_REDIRECT_URI=http://do.zbee.me/in");

$body = "";
$info = "<p id='info'>Choose one of your playlists below to begin downloading it.</p>";

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
Number of items in log
*/
$file="../log.txt";
$linecount = 0;
$handle = fopen($file, "r");
while(!feof($handle)){
  $line = fgets($handle);
  if (strpos($line, 'S1, ') !== false) {
    $linecount++;
  }
}
fclose($handle);
$transfers = $linecount;

/*
Echoing out recent logs
*/
$recentr = file_get_contents("../log.txt");
$recentr = explode("\n", $recentr);
$recent = "<u>Recent downloads</u><br>";
$nr = 0;
foreach ($recentr as $r) {
  if ($nr < 11 && $nr < $linecount) {
    $d = explode(", ", $r);
    if (strpos($r, 'S1, ') !== false) {
      $recent .=  $d[3] . " (" . $d[4] . " songs - " . sizeFormat($d[4]*9e6) . ")<br>";
      $nr += 1;
    }
  }
}
if ($linecount < 3) {
  $recent = "No recent downloads.";
}

/*
Disallowing use of system if too close to quota
*/
$fs = folderSize("/tmp/");
$no = false;
$ffs = 18500000000 - $fs;

if ($fs >= 18500000000) {
  $info = "<p>We're terribly sorry, use of the system is suspended for the rest of the day.<br>Our server is full, and we have to wait until playlist archives expire.</p>";
  $no = true;
}
if ($transfers >= 600) {
  $info = "<p>We're terribly sorry, use of the system is suspended for the rest of the month.<br>We've hit the cap on how much data we can transfer.</p>";
  $no = true;
}

/*
Setting up the Spotify API
*/
if (!$no) {
  $session = new SpotifyWebAPI\Session(getenv('SPOTIFY_CLIENT_ID'), getenv('SPOTIFY_CLIENT_SECRET'), getenv('SPOTIFY_REDIRECT_URI'));
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
    if (((count($tracks[$thisID])+1)*9e6) > $ffs && count($tracks[$thisID]) < 2) {
      $body .= '<a title="' . sizeFormat((count($tracks[$thisID])+1)*9e6) . '" data-id="' . $thisID . '" id="' . $thisID . '" class="no">' . $playlist->name . '</a><br>';
    } else {
      $body .= '<a title="' . sizeFormat((count($tracks[$thisID])+1)*9e6) . '" data-id="' . $thisID . '" id="' . $thisID . '">' . $playlist->name . '</a><br>';
      $body .= '
        <script>
        $("#' . $thisID . '").click(function() {
          $("#info").html("<div class=\'bar\'><span></span></div><br>Right now we\'re looking for all of the song\'s MP3s.<br>(This should only take a minute or two)");
          $.ajax({
            type: "POST",
            url: "getter.php",
            data: {playlist: "' . $thisID . '", playlistnice: "' . $playlist->name . '", songs:' . json_encode($tracks[$thisID]) . '},
            dataType: "json",
            context: document.body,
            async: true,
            complete: function(res, stato) {
              if (res.responseJSON.success == "2") {
                $("#info").html("<div class=\'bar\'><span></span></div><br>We finished finding all of the song\'s MP3s!<br>Now we\'re downloading them all ...<br>(This could take quite some time, leave this tab open)");
                $.ajax({
                  type: "POST",
                  url: "downloader.php",
                  data: {playlist: "' . $thisID . '", playlistnice: "' . $playlist->name . '", songs: JSON.stringify(res.responseJSON.codes)},
                  dataType: "json",
                  context: document.body,
                  async: true,
                  complete: function(res, stato) {
                    if (res.responseJSON.success == "2") {
                      $("#info").html("<div class=\'bar\'><span></span></div><br>We finished finding all of the song\'s MP3s!<br>Now we\'re downloading them all ...<br>(This could take quite some time, leave this tab open)");
                    } else if (res.responseJSON.success == "1") {
                      $("#info").html("aw");
                    } else {
                      $("#info").html("AHHHH!");
                    }
                    //$("#info").html(JSON.stringify(res));
                  }
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
}

/*try {
  $a = new PharData('c.tar');
  $a->addFile('error_log');
} catch (Exception $e) {
  echo "Err: " . $e;
}*/

?>

<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" type="text/css" href="../libs/css/main.css">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
  </head>
  <body>
    <div id="ad"><a href="https://s.zbee.me/bfq" title="AD"><img src="http://i.imgur.com/E2njcd2.png" width="100%"></a></div>
    <div id="recent"><?=$recent?></div>
    <div class="attrib" data-content="Server 1 - US - <?=sizeFormat($fs)?>/20 GB - <?=$transfers?>/500 transfers">&pi; </div>
    <div id="container">
      <a href='http://do.zbee.me' id='reload'>Spotify Downloader</a>
      <div id="main">
        <?=$info?>
        <?=$body?>
      </div>
    </div>
  </body>
</html>
