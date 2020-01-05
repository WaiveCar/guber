<?
?>
<div id='success'></div>
<?= $car ?> is currently <?= $state ?>

<? if ($state === 'reserved') { ?>
  <p>The passenger is at <?= $location ?></p>
  <p>They requested the pickup <?= $when ?></p>
  <button onclick=accept()>Accept</button>
  <button onclick=decline()>Decline</button>
<? } else if ($state == 'confirmed') { ?>
  <p>The passenger is at <?= $location ?></p>
  <p>They requested the pickup <?= $when ?></p>
  <button onclick=unavailable()>Passenger's in</button>
  <button onclick=cancel()>Cancel</button>
<? } else if ($state == 'unavailable') { ?>
  <button onclick=available()>Make Available</button>
<? } else if ($state == 'available') { ?>
  <button onclick=unavailable()>Make Unavailable</button>
<? } ?>


<script>
var car = <?= $id ?>;
function api(what) {
  return fetch('/api/' + what + '?id=' + car)
    .then(response => response.json())
}

['available','unavailable','accept'].forEach(row => {
  self[row] = function() {
    api(row).then(function() {
      window.reload();
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
