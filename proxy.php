<?
// this is so we can do session variables and have a pass thru to the waivescreen api
session_start();
$action = $_GET['action'];
if($action === 'request') {
  $car = $_GET['id'];
  $lat = $_GET['lat'];
  $lng = $_GET['lng'];
  $user = session_id();
  $goober_id = file_get_contents("http://waivescreen.com/api/request?id=$car&user_id=$user&lat=$lat&lng=$lng");
  if($goober_id) {
    $_SESSION['id'] = $goober_id;
    $_SESSION['car'] = $car;
    echo $goober_id;
  } else {
    echo 'false';
  }
  session_commit();
}
