<?
// this is so we can do session variables and have a pass thru to the waivescreen api
session_start()
$action = $_GET['action'];
if($action === 'request') {
  $id = $_GET['id'];
  $goober_id = file_get_contents("http://waivescreen.com/api/request?id=$id");
  var_dump($goober_id);
}
