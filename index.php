<?php
require_once('rest.inc.php');
require_once('func.inc.php');

if (isset($_REQUEST['numOfOrder'])) {
  $tmp = $_REQUEST['numOfOrder'] ? $_REQUEST['numOfOrder'] : 0;
  file_put_contents('data/7_NumOfOrder.txt', $tmp);
  exit;
}
$numOfOrder = file_get_contents('data/7_NumOfOrder.txt');
getTop20Id();
getDataFromId();
formatDataByCurrency();
getCurrentPrice();
zipDataByCurrency();
getTip();

$zipDataByCurrency = json_decode(file_get_contents('data/5_ZipDataByCurrency.txt'), true);
$getTip = json_decode(file_get_contents('data/6_GetTip.txt'), true);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="refresh" content="30"/>
  <title>FX Signal</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
  <link rel="manifest" href="/manifest.json">
  <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async></script>
  <script>
      var OneSignal = window.OneSignal || [];
      OneSignal.push(["init", {
          appId: "0e1b1aab-51f7-473c-96fc-fe1e5ccd6b15",
          autoRegister: true,
          notifyButton: {
              enable: true /* Set to false to hide */
          }
      }]);
  </script>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <?php
      print '<table class="table table-bordered table-hover table-condensed" style="margin: auto; max-width: 650px;">';
      print '<thead>';
      print '<tr>';
      print '<th>Symbol</th>';
      print '<th>Type</th>';
      print '<th>Lot</th>';
      print '<th>Open Price</th>';
      print '<th>Curren Price</th>';
      print '<th>Floating Pips</th>';
      print '</tr>';
      print '</thead>';
      print '<tbody>';
      foreach ($zipDataByCurrency as $currentcy => $dataObj) {
        $tmp = 0;
        foreach ($dataObj as $type => $order) {
          $tmp++;
          $class = ($type == 'BUY') ? 'success' : 'danger';
          print '<tr class="' . $class . '">';
          print '<td>' . $currentcy . '</td>';
          print '<td>' . $type . '</td>';
          print '<td>' . $order['stdLotds'] . '</td>';
          print '<td>' . $order['entryRate'] . '</td>';
          print '<td>' . $order['currentPrice'] . '</td>';
          print '<td>' . $order['floatingPips'] . '</td>';
          print '</tr>';
        }
        if ($tmp) {
          print "<tr>";
          print "<td colspan='7'></td>";
          print "</tr>";
          print "<tr>";
          print "<td colspan='7'></td>";
          print "</tr>";
        }
      }
      $style = ($numOfOrder) ? 'style="background: black"' : '';
      print "<tr>";
      print "<th colspan='7' style='text-align:center; vertical-align:middle;'>Tip - " . $numOfOrder . "</th>";
      print "</tr>";
      print '<tr class="warning">';
      print '<td ' . $style . '>' . $getTip['currency'] . '</td>';
      print '<td ' . $style . '>' . $getTip['tradeType'] . '</td>';
      print '<td ' . $style . '>' . $getTip['stdLotds'] . '</td>';
      print '<td ' . $style . '>' . $getTip['entryRate'] . '</td>';
      print '<td ' . $style . '>' . $getTip['currentPrice'] . '</td>';
      print '<td ' . $style . '>' . $getTip['floatingPips'] . '</td>';
      print '</tr>';
      print "</tbody>";
      print "</table>";
      ?>
    </div>
  </div>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/scripts.js"></script>
</body>
</html>