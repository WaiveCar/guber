<?
session_start();
if(!isset($_SESSION['secret'])) {
  header("Location: login.php");
  exit;
}
include('common.php');
$gooberable = get_goob();
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
function getgooberCar($id) {
  $list = json_decode(file_get_contents('http://waivescreen.com/api/screens?goober_id=' . $id), true);
  if(count($list) > 0) {
    return $list[0];
  }
}
function getcar($id) {
  $list = json_decode(file_get_contents('http://waivescreen.com/api/screens?id=' . $id), true);
  return $list[0];
}
function getgooberInfo($id) {
  return file_get_contents("http://waivescreen.com/api/goober?id=" . $id);
}

$state = 'available';
$goober = 0;
if(array_key_exists('id', $_SESSION)) {
  $id = $_SESSION['id'];
  $goober = $id;
  $car = getgooberCar($id);
  if($car && !empty($car)) {
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
    <link rel="stylesheet" href="style.css?1" type="text/css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
<style>
#map {
  height: 90vh
}
.find > div { display: none }
</style>
  </head>
  <body class=mode-find>
  <div id="map" class="map"></div>
  <div id="bottom">
    <div class="find">

   <div id=start>
    <h2>To start, tap on the car that's closest to you</h2>
   </div> 
   <div id=available>
    <h2>To request a ride, tap the button below</h2>
    <button class="full" onclick=request()>Request</button>
   </div>
   <div id=reserved>
    <h2>Your vehicle is reserved. Waiting on driver to confirm...</h2>
    <button onclick=cancel()>Cancel</button>
   </div>
   <div id=confirmed>
    <h2>Confirmed! Your driver is coming. They'll call you when they're near</h2>
    <input id=number type=text placeholder="Best number to reach you"><button onclick='savenumber(this)' class='small'>ok</button>
    <div style=text-align:center;margin-top:1rem>
    <button class=muted onclick=cancel()>Cancel ride</button>
    </div>
   </div>
   <div id=driving>
    <h2>Have a pleasant trip!</h2>
   </div>
  </div>
</div>
<? if ($state === 'available') { 
$filter = 'goober_state=available';
  } else if ($state == 'reserved') { 
    $filter = "car=$car";
  } else if ($state == 'confirmed') { 
    $filter = "car=$car";
  } else if ($state == 'driving') { 
    $filter = "car=$car";
  } 
?>
  </body>
<script
  src="https://code.jquery.com/jquery-3.4.1.min.js"
  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
  crossorigin="anonymous"></script>

  <script src="map.js"></script>
  <script src="socket.io.js"></script>
  <script>
var 
  _carMap = {},
  _blueDot = false,
  _socket = false,
  _state = "<?= $state ?>",
  _goober_id = <?= $goober ?>,
  _goober_info = false,
  _me = {id:"<?=session_id(); ?>"},
  filter = "<?= $filter ?>", 
  _car = <? if($car) { echo $car['id']; } else { echo 'false'; } ?>;

<? if ($goober) { ?>
_goober_info = <?= getgooberInfo($goober); ?>[0];
<? } ?> 

function getLocations() {
  fetch('api/screens?' + filter)
    .then(response => response.json())
    .then(all => {
      all.forEach(row => {
        addCar(row);
      }); 
    });
}

function updateLocation(pos) {
  var crd = pos.coords;
  _me.lat = crd.latitude;
  _me.lng = crd.longitude;
  if(!_blueDot) {
    let loc = [crd.longitude, crd.latitude];
    _blueDot = _map.addOne(['Location', loc]);
    _map.center(loc);
  } else {
    _map.move(_blueDot.index, crd.latitude, crd.longitude);
  }
}

function locationError(what) {
  console.log(what);
}

function mekv() {
  return "&lat=" + _me.lat + "&lng=" + _me.lng;
}

function api(what) {
  return fetch('api/' + what + '?id=' + _car + mekv())
    .then(response => response.json())
}

function request() {
  return fetch('proxy.php?action=request&id=' + _car + mekv())
    .then(response => response.json())
}

function cancel() {
  if(confirm("Are you sure you want to cancel?!")) {
    api('cancel').then(function() {
      _state = 'available';
      _car = false;
      gen();
    });
  }
}

function removeCar(data) {
  console.log("removing car");
  if(_carMap[data.id]) {
    _map.remove(_carMap[data.id].index);
    delete _carMap[data.id];
  }
}

function addCar(data) {
  if(!_carMap[data.id]) {
    let m  = _map.addOne(["Point", [data.lng, data.lat], data.id]);
    console.log(m);
    _carMap[data.id] = m;
  } else {
    moveCar(data);
  }
}
function moveCar(data) {
  if(_carMap[data.id]) {
    _map.move(_carMap[data.id].index, data.lat, data.lng);
  } 
}
function savenumber(el) {
  fetch('api/goobup?number=' + $("#number").val() + '&id=' + _goober_id).then(function() {
    el.style.display = 'none';
  });
}

function gen() {
  let el = '';
  if(_state === "available" && !_car) {
    el = 'start';
  } else {
    el = _state;
  }
  $("#" + el).show().siblings().hide();
  if(el == 'confirmed') {
    if(_goober_info.phone) {
      $("#number").val(_goober_info.phone);
    }
  }
  document.getElementById('bottom').className = el;
}
    
window.onload = function(){
  _socket = io();
  _socket.on('update', function(data) {
    data = JSON.parse(data);
      console.log(data);
    if(data.type == 'car') {
      moveCar(data);
    }
    if(data.type == 'update') {
      if(data.state == 'reserved') {
        if(data.user_id == _me.id) {
          _state = 'reserved';
          gen();
        }
      }
      if(data.state == 'driving') {
        if(data.user_id == _me.id) {
          _state = 'driving';
          gen();
        }
      }
      if(data.state == 'finished') {
        if(data.user_id == _me.id) {
          _state = 'start';
          _car = false;
          gen();
        }
      }
      if(data.state == 'confirmed') {
        _state = 'confirmed';
        gen();
      }

      if(data.state == 'available') {
        addCar(data);
      } else if(data.state == 'unavailable') {
        removeCar(data);
      }
    } else {
      console.log(data);
    }
  });

  self._map = map({
    select: true,
    zoom: 14
  });

  gen();
  getLocations();

  navigator.geolocation.watchPosition(
    updateLocation, locationError, {
      enableHighAccuracy: true,
      timeout: 5000,
      maximumAge: 0
    });

  _map.on('select', function(a) { 
    _car = a.target.getFeatures().item(0).getId();
    gen();
  })
}
  </script>
</html>

