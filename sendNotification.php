<?PHP
function sendMessage($numOfOrder)
{
  $content = array(
    "en" => "Num Of Order: $numOfOrder"
  );

  $fields = array(
    'app_id' => "0e1b1aab-51f7-473c-96fc-fe1e5ccd6b15",
    'included_segments' => array('Active Users'),
    'data' => array("foo" => "bar"),
    'contents' => $content
  );

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