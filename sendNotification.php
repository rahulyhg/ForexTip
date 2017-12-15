<?PHP
function sendMessage($numOfOrder)
{
  $getTip = json_decode(file_get_contents('data/6_GetTip.txt'), true);
  $msg = "Num Of Order: $numOfOrder ({$_GET['floatingProfit']})\n{$getTip['currency']} {$getTip['tradeType']} ({$getTip['floatingPips']} Pips) {$getTip['currentPrice']}";
  $content = array(
    "en" => $msg
  );

  $fields = array(
    'app_id' => "0e1b1aab-51f7-473c-96fc-fe1e5ccd6b15",
    'included_segments' => array('Active Users'),
    'data' => array("foo" => "bar"),
    'contents' => $content
  );
  $fields['chrome_web_icon'] = 'http://www.freepngimg.com/download/ok/6-2-ok-png-hd.png';

  $tmp = file_get_contents('data/8_IsCloseOrder.txt');
  if ($tmp) {
    file_put_contents('data/8_IsCloseOrder.txt', '');
    $fields['chrome_web_icon'] = 'https://irp-cdn.multiscreensite.com/1cbf2bcb/dms3rep/multi/desktop/money-logo-png-money-256x256.jpg.png';
  }

  if ($_GET['floatingProfit'] < -1000) {
    $fields['chrome_web_icon'] = 'https://images.vexels.com/media/users/3/130014/isolated/lists/3a93b4202dc70070d1d6a87f656bb267-danger-sign.png';
  }

  $fields = json_encode($fields);
  print("\nJSON sent:\n");
  print($fields);

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
    'Authorization: Basic Y2UwNzBiZDQtMTcyNy00NTNlLWFhZDAtZjFmMzViMjI5Mjg1'));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($ch, CURLOPT_HEADER, FALSE);
  curl_setopt($ch, CURLOPT_POST, TRUE);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

  $response = curl_exec($ch);
  curl_close($ch);

  return $response;
}

$numOfOrder = $_REQUEST['numOfOrder'] ? $_REQUEST['numOfOrder'] : 0;
$response = sendMessage($numOfOrder);
$return["allresponses"] = $response;
$return = json_encode($return);

print("\n\nJSON received:\n");
print($return);
print("\n");
?>