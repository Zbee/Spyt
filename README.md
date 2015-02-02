# Spyt
Downloads complete Spotify playlists in MP3 format from Youtube, adds them to a .tar file, and returns that file.

## Issues
- Currently only runs on linux systems since the /tmp/ directory is used to store files.

## Secret files
In the system you will notice a secret file being referenced (`require "_secret_keys.php"`). These files contain sensitive information and so they cannot be included in the repo.

However, here are the setup of the files so you're not up a creek without a paddle if you decide to recreate or use this system.

### _secret_keys.php
```
<?php
/*
Spotify secret keys
https://developer.spotify.com
*/
$SPOTIFY_CLIENT_ID = "";
$SPOTIFY_CLIENT_SECRET = "";
$SPOTIFY_REDIRECT_URL = "";

/*
Google secret key
https://developers.google.com/youtube/registering_an_application
*/
$GOOGLE_KEY = "";
?>
```
