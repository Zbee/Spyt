<?php
date_default_timezone_set('America/Denver');
if (!isset($_POST["song"])) {
  echo json_encode(["success"=>"0"]);
  exit;
}
$song = $_POST["song"];

function getCode ($q, $max = 1) {
  // Call set_include_path() as needed to point to your client library.
  require_once __DIR__."/../vendor/autoload.php";
  require "../_secret_keys.php";

  $client = new Google_Client();
  $client->setDeveloperKey($GOOGLE_KEY);

  // Define an object that will be used to make all API requests.
  $youtube = new Google_Service_YouTube($client);

  try {
    // Call the search.list method to retrieve results matching the specified
    // query term.
    $searchResponse = $youtube->search->listSearch("id,snippet", array(
      "q" => $q . " -acoustic -live -literal -parody -vevo -official",
      "maxResults" => $max,
      "type" => "video",
      "videoDimension" => "2d",
      "videoDuration" => "medium"
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

$code = getCode($song);

echo json_encode(["success"=>"2","code"=>$code]);
