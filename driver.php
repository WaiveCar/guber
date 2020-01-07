<?
$gooberable = [
  107, // work49
  41,
  80
];
if(isset($_GET['id'])) {
  $id = $_GET['id'];
  $mycarList = json_decode(file_get_contents("http://waivescreen.com/api/screens?id=" . $id), true);
  $all = $all_list[0];
  $state = $all['goober_state'];
} else {
  $all_list = json_decode(file_get_contents("http://waivescreen.com/api/screens"), true);
  $mycarList = array_filter($all_list, function($row) use($gooberable) {
    return in_array($row['id'], $gooberable);
  });
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
button { font-size: 24px }
h1 { margin: 0.5rem}
.car { border: 1px solid black;padding: 0.5rem }
    .unavailable { color: red }
    .available { color: green }
    .waiting { opacity: 0.7; background: #aaa}
    </style>
  </head>
<body>
Hi goober!
<?  foreach($mycarList as $car) { 
  $state = $car['goober_state'];
  ?>
    <div class="car <?=$state?>">
    <h1><?= $car['car'] ?> is <?= $car['goober_state'] ?></h1>

  <? if ($state === 'reserved') { ?>
    <button onclick=accept(<?= $car['id'] ?>)Accept</button>
    <button onclick=decline(<?= $car['id'] ?>)>Decline</button>
  <? } else if ($state == 'confirmed') { ?>
    <button onclick=driving(<?= $car['id'] ?>)>Passenger's in</button>
    <button onclick=cancel(<?= $car['id'] ?>)>Cancel</button>
  <? } else if ($state == 'unavailable') { ?>
    <button onclick=available(<?= $car['id'] ?>)>Make Available</button>
  <? } else if ($state == 'available') { ?>
    <button onclick=unavailable(<?= $car['id'] ?>)>Make Unavailable</button>
  <? } else if ($state == 'driving') { ?>
    <button onclick=finish(<?= $car['id'] ?>)>Passenger's dropped off</button>
  <? } ?>
  </div>
<? } ?>

<script src="socket.io.js"></script>
<script>
function api(what,car) {
  document.body.classList.add('waiting');
  return fetch('api/' + what + '?id=' + car)
    .then(response => response.json())
}

['available','finish','driving','unavailable','accept'].forEach(row => {
  self[row] = function(car) {
    api(row,car).then(() => {location.reload();});
  }
});

['decline', 'cancel'].forEach(row => {
  self[row] = function(car) {
    if(confirm("Are you sure you want to " + row + "?!")) {
      api(row,car).then(() => {location.reload()});
    }
  }
});

</script>
