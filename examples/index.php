<?php
require_once __DIR__.'/../vendor/autoload.php';

use Youtubedl\Youtubedl;

$youtubedl=new Youtubedl();
$youtubedl->getFilesystemOption()->setOutput('"/tmp/cake/%(title)s.%(ext)s"');
$youtubedl->download('AAjOCXsc_Vs');

var_dump(scandir("/tmp/"));
