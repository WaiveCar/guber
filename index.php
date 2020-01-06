<?
session_start();
// for a user, they're either
//
// available-- finding available cars
// reserve  -- reserved a given car
// waiting  -- waiting for a confirmed car
// riding   -- inside a car
//
// find -> reserve
//
//        user/driver cancel, driver confirmed
// reserve -> [ find, waiting ]
//
// waiting -> [ find, riding ]
//
// riding -> [ find ]
//
function getgoober($id) {
  $list = json_decode(file_get_contents('http://waivescreen.com/api/screens?goober_id=' . $id), true);
  if(count($list) > 0) {
    return $list[0];
  }
}
function getcar($id) {
  $list = json_decode(file_get_contents('http://waivescreen.com/api/screens?id=' . $id), true);
  return $list[0];
}

$state = 'available';
if(array_key_exists('id', $_SESSION)) {
  $id = $_SESSION['id'];
  $car = getgoober($id);
  if($car) {
    $state = $car['goober_state'];
  } else {
    unset($_SESSION['id']);
  }
} 


$titleMap = [
  'available' => 'Available Cars',
  'reserved' => 'Waiting for Confirmation',
  'confirmed' => 'Driver is coming',
  'driving' => 'Enjoy your trip'
];
?>
<!DOCTYPE html>
<html>
  <head>
    <title><?= $titleMap[$state] ?></title>
    <link rel="stylesheet" href="https://openlayers.org/en/v5.3.0/css/ol.css" type="text/css">
    <link rel="stylesheet" href="style.css" type="text/css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
<style>
#map {
  height: 70vw
}
</style>
  </head>
  <body class=mode-find>
  <div id="header">

    <h1><?= $titleMap[$state] ?></h1>
</div>
    <div id="map" class="map"></div>
<div id="bottom">
 <div class="find">

<? if ($state === 'available') { 
    $filter = 'goober_state=available';
    echo '<button class="full" onclick=request()>Request Goober</button>';
  } else if ($state == 'reserved') { 
    $filter = "car=$car";
    echo '<button onclick=cancel()>Cancel</button>';
  } else if ($state == 'confirmed') { 
    $filter = "car=$car";
    echo '<button onclick=cancel()>Cancel</button>';
  } else if ($state == 'driving') { 
    $filter = "car=$car";
  } 
?>
</div>
</div>
  </body>
  <script src="map.js"></script>
  <script src="socket.io.js"></script>
  <script>
var 
  _carMap = {},
  _socket = false,
  filter = "<?= $filter ?>", car=<? if($car) { echo $car; } else { echo 'false'; } ?>;
//function toMap(what) {

function getLocations() {
  fetch('http://waivescreen.com/api/screens?' + filter)
    .then(response => response.json())
    .then(all => {
      console.log(all);
    });
}
setInterval(function(){
});

function api(what) {
  return fetch('http://waivescreen.com/api/' + what + '?id=' + car)
    .then(response => response.json())
}

function request() {
  if(!car) { car = 107;} 
  return fetch('proxy.php?action=request&id=' + car)
    .then(response => response.json())
}

function cancel() {
  if(confirm("Are you sure you want to cancel?!")) {
    api('cancel');
  }
}

window.onload = function(){
  _socket = io(':3000');
  _socket.on('update', function(data) {
    data = JSON.parse(data);
    console.log(data);
    if(data.type == 'car') {
      if(_carMap[data.car]) {
        //console.log("move>>", _carMap[data.car].index);
        _map.move(_carMap[data.car].index, data.lat, data.lng);
      } else {
        _carMap[data.car] = _map.addOne(["Point", [data.lng, data.lat], data.car]);
        _map.fit();
      }
    }
  });

  var sincity = [-115.1542192, 36.1316824];

  self._map = map({
    select: true,
    center: sincity,
    zoom: 14
  });

  /*
  self.mypoints = _map.load([
    ["Point", [-118.33,34.024]],
    ["Location", [-118.35,34.034]]
  ]);
   */

  _map.on('select', function(a) { 
    console.log(a.target.getFeatures().item(0).getId());
    self.a = a;
    console.log(a) 
  })
}
  </script>
</html>

<!--<? var_dump([$car, $state]);exit;?>-->
