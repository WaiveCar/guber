<?
session_start();
if(!empty($_POST['password']) && 
  ( strtolower($_POST['password']) == 'awssecret' ||  
    strtolower($_POST['password']) == 'awsecret'
  )) {
  $_SESSION['secret'] = true;
  header("Location: index.php");
  exit;
}
?>

<!doctype html>
<html>
<head>
  <title>It's a secret</title>
  <link rel=stylesheet href=style.css>
  <link rel="shortcut icon" href=img/cl32.gif>
  <meta name=mobile-web-app-capable content=yes>
  <meta name=viewport content="width=device-width, initial-scale=1.0">
</head>
<style>
body {
  display: flex;
  align-items: center;
  justify-content: center;
text-align: center;
  background: #514aff;
}
button { margin-top: 1rem }
</style>
<body>
  <div class='box login'>
    <h1>Secret Password Please!</h1>
    <form action=login.php method=post>
      <input type=hidden name=referer value="<?=$_SERVER['HTTP_REFERER']?>">
      <input type=password name=password placeholder=Password>
      <button>OK</button>
    </form>
  </div>

</body>
</html>
