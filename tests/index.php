<?php
  require '../vendor/autoload.php';

  $api = new \Fitbit\Api("clientid", "clientsecret");
  //$api->resetSession();
  if (!$api->isAuthorized()) {
    $api->initSession();
  }


?>

<pre>
<?php /// var_dump($api->getTimeSeries("startTime", "2013-03-01", "3m")); ?>
<?= var_dump($api->getProfile()); ?>
</pre>
