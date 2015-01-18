<?php
date_default_timezone_set('America/Denver');
if (!isset($_POST['songs'])) exit;
$result = $_POST['songs'];

function getCode ($q, $max = 1) {
  // Call set_include_path() as needed to point to your client library.
  require_once __DIR__.'/../vendor/autoload.php';

  /*
   * Set $DEVELOPER_KEY to the "API key" value from the "Access" tab of the
   * Google Developers Console <https://console.developers.google.com/>
   * Please ensure that you have enabled the YouTube Data API for your project.
   */
  $DEVELOPER_KEY = 'AIzaSyAy1BJn74Ex96iKMH4EOgxkQPMbrQsftrM';

  $client = new Google_Client();
  $client->setDeveloperKey($DEVELOPER_KEY);

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
    $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
      htmlspecialchars($e->getMessage()));
  } catch (Google_Exception $e) {
    $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
      htmlspecialchars($e->getMessage()));
  }
}

$tracks = [];
foreach ($result as $track) {
  array_push($tracks, getCode($track));
}

$insResult = "S1, " . date("Y-m-d", time())."T".date("H:i", time()) . ", " . $_POST['playlist'] . ", " . $_POST['playlistnice'] . ", " . (count($result)+1) /*. ", " . json_encode($result)*/;
$file = '../log.txt';
$current = file_get_contents($file);
$current = $insResult . "\n" . $current;
file_put_contents($file, $current);

echo json_encode(["success"=>"2","codes"=>$tracks]);
