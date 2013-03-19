<?php
  require '../vendor/autoload.php';

  $api = new \Fitbit\Api("d309d935df0b415e84721ef9c40c2379", "64cff6931e0547a8b403dad81c5ba664");
  //$api->resetSession();
  if (!$api->isAuthorized()) {
    $api->initSession();
  }


?>

<pre>
<?php /// var_dump($api->getTimeSeries("startTime", "2013-03-01", "3m")); ?>
<?= var_dump($api->getRateLimit()); ?>
</pre>
