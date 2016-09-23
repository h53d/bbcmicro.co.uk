<?php
require 'includes/config.php';
require 'includes/db_connect.php';
require 'includes/menu.php';

$id=0;

if ( isset($_GET["id"])) {
  $id=intval($_GET["id"]);
}
if ( isset($_GET["h"])) {
  $h=$_GET["h"];
} else { 
  $h="i";
}

$sql = "select g.title, g.publisher, g.year, n.name as genre, r.name as reltype from games g left join genres n on n.id = g.genre left join reltype r on r.id = g.reltype where g.id  = ?";
$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->bindParam(1, $id, PDO::PARAM_INT);
if ($sth->execute()) {
  $game = $sth->fetch();
} else {
  echo "Error:";
  echo "\n";
  $sth->debugDumpParams ();
  $game=array();
}

$sql = "select * from screenshots where gameid  = ?";
$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->bindParam(1, $id, PDO::PARAM_INT);
if ($sth->execute()) {
  $shot = $sth->fetchAll();
} else {
  echo "Error:";
  echo "\n";
  $sth->debugDumpParams ();
  $shot=array();
}
if ( empty($shot) ) {
  $shot[] = array( "filename" => 'default.jpg' );
}

$sql = "select * from images where gameid  = ?";
$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->bindParam(1, $id, PDO::PARAM_INT);
if ($sth->execute()) {
  $img = $sth->fetch();
} else {
  echo "Error:";
  echo "\n";
  $sth->debugDumpParams ();
  $img=array();
}

$ssd = 'gameimg/discs/' . $img["filename"];
$jsbeeb=JB_LOC;
$root=WS_ROOT;

if ( $ssd != null && file_exists($ssd)) {
  $imglink='<p><a type="button" class="btn btn-primary btn-lg center-block" href="' . $ssd . '">Download</a></p>
            <p><a type="button" class="btn btn-primary btn-lg center-block" href="' . $jsbeeb . $root . '/' . $ssd . '" >Play</a></p>';
} else {
  $imglink="<p>No disc image available</p>";
}

$sql = "select * from game_genre gg, genres g where gg.gameid  = ? and gg.genreid = g.id";
$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->bindParam(1, $id, PDO::PARAM_INT);
if ($sth->execute()) {
  $genres = $sth->fetchAll();
} else {
  echo "Error:";
  echo "\n";
  $sth->debugDumpParams ();
  $genres=array();
}

$sql = "select a.name from games_authors ga, authors a where ga.games_id  = ? and ga.authors_id = a.id";
$sth = $db->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->bindParam(1, $id, PDO::PARAM_INT);
if ($sth->execute()) {
  $authors = $sth->fetchAll();
} else {
  echo "Error:";
  echo "\n";
  $sth->debugDumpParams ();
  $authors=array();
}

$split=explode('(',$game["title"]);
$title='<h1>' . $split[0];
if (count($split) > 1 ) {
   $title = $title . '</h1><p>(' . implode('(',array_slice($split,1)) . "</p>";
}  else {
   $title = $title . '</h1>';
}

$back_url='index.php';
$back_desc='home page';
if ($h != "h" && array_key_exists('HTTP_REFERER', $_SERVER)) {
  if ( parse_url($_SERVER["HTTP_REFERER"],PHP_URL_HOST) == $_SERVER["SERVER_NAME"] ) {
    $back_url = "javascript:history.go(-1)";
    $back_desc='list';
  }
}

$s = '';
if ( count($genres) > 1) {
  $s = 's';
}

if ( ! empty($genres)) {
  $genretab='<tr><th>Secondary genre' . $s . '</th><td>';
  foreach ($genres as $genre) {
    $genretab=$genretab . $genre["name"] . "<br/>";
  }
  $genretab=$genretab . "</td></tr>";
} else {
  $genretab="";
}

$s = '';
if ( count($authors) > 1) {
  $s = 's';
}

if ( ! empty($authors)) {
  $authortab='<tr><th>Author' . $s . '</th><td>';
  foreach ($authors as $author) {
    $authortab=$authortab . $author["name"] . "<br/>";
  }
  $authortab=$authortab . "</td></tr>";
} else {
  $authortab="";
}

?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">

    <title><?php echo $game["title"]; ?></title>

    <!-- Bootstrap core CSS -->
    <link href="bs/css/bootstrap.min.css" rel="stylesheet">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="bs/css/ie10-viewport-bug-workaround.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="bs/css/jumbotron.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

 <nav class="navbar navbar-fixed-top navbar-inverse">
  <div class="container">
   <div class="navbar-header">
    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
     <span class="sr-only">Toggle navigation</span>
     <span class="icon-bar"></span>
     <span class="icon-bar"></span>
     <span class="icon-bar"></span>
    </button>
    <a href="index.php" class="navbar-brand"><?php echo $site_name?></a>
   </div>
   <?php make_menu_bar("Games")?>
  </div><!-- /.container -->
 </nav><!-- /.navbar -->


    <div class="container">
      <div class="jumbotron">
        <?php echo $title; ?>
      </div>
      <!-- Example row of columns -->
      <div class="row">
        <div class="col-md-8">
          <h2>Screen Shot</h2>
          <p><img src="gameimg/screenshots/<?php echo $shot[0]["filename"];?>" class="img-responsive"></p>
          
        </div>
        <div class="col-md-4">
          <h2>Details</h2>
          <table class="table">
            <tr><th>Title</th><td><?php echo $game["title"];?></td></tr>
            <tr><th>Year</th><td><?php echo $game["year"];?></td></tr>
            <tr><th>Publisher</th><td><?php echo $game["publisher"];?></td></tr>
            <?php echo $authortab;?>
            <tr><th>Release Type</th><td><?php echo $game["reltype"];?></td></tr>
            <tr><th>Primary genre</th><td><?php echo $game["genre"];?></td></tr>
            <?php echo $genretab;?>
          </table>
          <?php echo $imglink; ?>
          <p><a type="button" class="btn btn-primary btn-lg center-block" href="<?php echo $back_url ?>" title="Back">Back to <?php echo $back_desc ?></a></p>
       </div>
      </div>
      <hr>
     </div> <!-- /container -->
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
    <script src="bs/js/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="bs/js/ie10-viewport-bug-workaround.js"></script>
<?php include_once("includes/googleid.php") ?>
  </body>
</html>

