<?
session_start();
// for a user, they're either
//
// find     -- finding available cars
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
if(!array_key_exists('state', $_SESSION)) {
  $_SESSION['state'] = 'find';
  // this means there's a car
} else if($_SESSION['state'] != 'find') {
  $car = $_SESSION['car'];
}
$state = $_SESSION['state'];

$screenList = file_get_contents('waivescreen.com/api/screens');

$titleMap = [
  'find' => 'Available Cars',
  'reserve' => 'Waiting for Confirmation',
  'waiting' => 'Driver is coming',
  'riding' => 'Enjoy your trip'
];
?>
<!DOCTYPE html>
<html>
  <head>
    <title><?= $titleMap[$state] ?></title>
    <link rel="stylesheet" href="https://openlayers.org/en/v5.3.0/css/ol.css" type="text/css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  </head>
  <body>
    <h1><?= $titleMap[$state] ?></h1>
    <div id="map" class="map"></div>
<? if ($state === 'find') { 
    $filter = 'goober_state=available';
    echo '<button onclick=request()>Request Goober</button>';
  } else if ($state == 'reserve') { 
    $filter = "car=$car";
    echo '<button onclick=cancel()>Cancel</button>';
  } else if ($state == 'waiting') { 
    $filter = "car=$car";
    echo '<button onclick=cancel()>Cancel</button>';
  } else if ($state == 'riding') { 
    $filter = "car=$car";
  } 
?>
  </body>
  <script src="map.js"></script>
  <script>
var filter = "<?= $filter ?>", car=107;
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
  return fetch('proxy.php?action=request&id=' + car)
    .then(response => response.json())
}

function cancel() {
  if(confirm("Are you sure you want to cancel?!")) {
    api('cancel');
  }
}

window.onload = function(){
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
