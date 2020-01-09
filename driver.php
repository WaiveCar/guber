<?
include('common.php');
$gooberable = get_goob();
if(isset($_GET['id'])) {
  $id = $_GET['id'];
  $mycarList = json_decode(file_get_contents("http://waivescreen.com/api/screens?id=" . $id), true);
} else {
  $all_list = json_decode(file_get_contents("http://waivescreen.com/api/screens"), true);
  $mycarList = array_filter($all_list, function($row) use($gooberable) {
    return in_array($row['id'], $gooberable);
  });
}
function getGoober($id) {
  return file_get_contents("http://waivescreen.com/api/goober?id=" . $id);
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://openlayers.org/en/v5.3.0/css/ol.css" type="text/css">
    <style>
#map-link { display: block;text-align: center;margin-bottom: 2rem}
button { font-size: 24px; width: 30%;margin: 0 3%; }
h1 { margin: 0.5rem}
#map {margin-bottom: 2rem;}
.car { border: 1px solid black;padding: 0.5rem; text-align: center }
    .unavailable { color: red }
    .available { color: green }
    .waiting { opacity: 0.7; background: #aaa}
    </style>
  </head>
<body>
<?  foreach($mycarList as $car) { 
  $state = $car['goober_state'];
  ?>
    <div class="car <?=$state?>">
    <h1><?= $car['car'] ?> is <?= $car['goober_state'] ?></h1>

  <? if ($state === 'reserved') { ?>
    <div id='map'></div>
    <a id=map-link>Open in maps</a>
    <script>
    self.goob = <?= getGoober($car['goober_id']); ?>[0];
    </script>
    <button onclick=accept(<?= $car['id'] ?>)>Accept</button>
    <button onclick=decline(<?= $car['id'] ?>)>Decline</button>
  <? } else if ($state == 'confirmed') { ?>
    <div id='map'></div>
    <a id=map-link>Open in maps</a>
    <script>
    self.goob = <?= getGoober($car['goober_id']); ?>[0];
    </script>
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

<script src="map.js"></script>
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

window.onload = function(){
  if(self.goob) {

    var mymap = map({
      zoom: 14
    });
    document.getElementById('map-link').href = 'https://maps.google.com/?q=' + goob.lat + ',' + goob.lng;
    mymap.addOne(["Location", [goob.lng, goob.lat]]);
    mymap.center( [goob.lng, goob.lat]);
  }
}

</script>
