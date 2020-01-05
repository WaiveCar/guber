<?
$id = $_GET['id'];
$all_list = json_decode(file_get_contents("http://waivescreen.com/api/screens?id=" . $id), true);
$all = $all_list[0];
$state = $all['goober_state'];
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
    .unavailable { color: red }
    .available { color: green }
    .waiting { opacity: 0.7; background: #aaa}
    </style>
  </head>
<body class="<?=$state?>">
Hi goober!
<h1><?= $all['car'] ?> is currently <?= $all['goober_state'] ?></h1>

<? if ($state === 'reserved') { ?>
  <button onclick=accept()>Accept</button>
  <button onclick=decline()>Decline</button>
<? } else if ($state == 'confirmed') { ?>
  <button onclick=unavailable()>Passenger's in</button>
  <button onclick=cancel()>Cancel</button>
<? } else if ($state == 'unavailable') { ?>
  <button onclick=available()>Make Available</button>
<? } else if ($state == 'available') { ?>
  <button onclick=unavailable()>Make Unavailable</button>
<? } ?>


<script>
var car = <?= $all['id'] ?>;
function api(what) {
  document.body.classList.add('waiting');
  return fetch('http://waivescreen.com/api/' + what + '?id=' + car)
    .then(response => response.json())
}

['available','unavailable','accept'].forEach(row => {
  self[row] = function() {
    api(row).then(function() {
      location.reload();
    });
  }
});

function decline() {
  if(confirm("Are you sure you want to decline?!")) {
    api('decline');
  }
}
function cancel() {
  if(confirm("Are you sure you want to cancel?!")) {
    api('cancel');
  }
}
</script>
