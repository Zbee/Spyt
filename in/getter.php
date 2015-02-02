<?php
date_default_timezone_set('America/Denver');
if (!isset($_POST)) {
  echo json_encode(["success"=>"0"]);
  exit;
}
$songs = json_decode($_POST['songs']);

function getCode ($q, $max = 1) {
  // Call set_include_path() as needed to point to your client library.
  require_once __DIR__.'/../vendor/autoload.php';
  require "../_secret_keys.php";

  $client = new Google_Client();
  $client->setDeveloperKey($GOOGLE_KEY);

  // Define an object that will be used to make all API requests.
  $youtube = new Google_Service_YouTube($client);

  try {
    // Call the search.list method to retrieve results matching the specified
    // query term.
    $searchResponse = $youtube->search->listSearch('id,snippet', array(
      'q' => $q . ' lyrics -literal -parody -vevo -official',
      'maxResults' => $max,
    ));

    $videos = '';
    $channels = '';
    $playlists = '';

    // Add each result to the appropriate list, and then display the lists of
    // matching videos, channels, and playlists.
    foreach ($searchResponse['items'] as $searchResult) {
      if ($searchResult['id']['kind'] === 'youtube#video') {
        $videos .= $searchResult['id']['videoId'];
      }
    }

    return $videos;
  } catch (Google_ServiceException $e) {
    file_put_contents("../log.txt", sprintf("Err," . date("Y-m-d\TH:i", time()) . ", " . "A service error occurred: %s",
      htmlspecialchars(str_replace("\n", "", $e->getMessage()))) . "\n" . file_get_contents("../log.txt"));
  } catch (Google_Exception $e) {
    file_put_contents("../log.txt", sprintf("Err," . date("Y-m-d\TH:i", time()) . ", " . "A client error occurred: %s",
      htmlspecialchars(str_replace("\n", "", $e->getMessage()))) . "\n" . file_get_contents("../log.txt"));
  }
}

$tracks = [];
foreach ($songs as $track) {
  array_push($tracks, getCode($track));
}

$insResult = date("Y-m-d\TH:i", time()) . ", " . $_POST['playlist'] . ", " . $_POST['playlistnice'] . ", " . count($songs);
$file = '../log.txt';
$current = file_get_contents($file);
$current = $insResult . "\n" . $current;
file_put_contents($file, $current);

echo json_encode(["success"=>"2","codes"=>$tracks]);
