<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" type="text/css" href="libs/css/main.css">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
  </head>
  <body>
    <div id="container">
      <a href='http://do.zbee.me' id='reload'>Spotify Downloader</a>
      <div id="main">
        <p>This is all of the information that is kept about you and why.</p>
        <p><u>What server you used</u><br>This is for the load balancer which keeps the load on our different servers roughly even.</p>
        <p><u>When you used this service</u><br>This is also for our load balancer so it can sort of keep track of how many users are on a server at a time and keep that load roughly even as well.<br>However, this is also indicates what times might be best for any maintenance to be performed.</p>
        <p><u>The id and name of the playlist you downloaded</u><br>This is to offer you a link to download a playlist you recently had this service download and so it can sort of be seen how many times people typically download the same playlist.<br>But also just for interest's sake for the recently downloaded box.</p>
        <p><u>How many songs you downloaded at the time</u><br>This is also for our load balancer since it guesses at the size of the songs and that helps with balancing the load on each server.<br>Also just for interest's sake for the recently downloaded box.</p>
        <p>As you can see, all data collected is either for the load balancer, or for curiosity's sake.</p>
        <a href="log.txt">Still not convinced? Have a look at our only log.</a>
      </div>
    </div>
  </body>
</html>
