<?php
  header('Content-Type: application/json; charset=UTF-8');

  $apiKey = '31U7R56193ZBQuu-T4Xtpw73-9vr5S464-C5V7Kdga-65NHDdkzg57Sj67'; // ЗАМЕНИТЕ НА ВАШ API КЛЮЧ

  function curlRequest($url, $data = [], $method = 'GET') {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    if ($method === 'POST') {
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    $response = curl_exec($ch);
    if(curl_errno($ch)){
        $error = curl_error($ch);
        curl_close($ch);
        return ['success' => false, 'error' => "Curl error: $error"];
    }
    curl_close($ch);
    $result = json_decode($response, true);
    if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
      return ['success' => false, 'error' => 'JSON decoding error: ' . json_last_error_msg()];
    }
    return $result ? $result : ['success' => false, 'error' => 'Invalid JSON response'];
  }


  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'cancel') {
      $numberId = isset($_POST['numberId']) ? $_POST['numberId'] : null;
      if($numberId === null){
          echo json_encode(['success' => false, 'error' => 'numberId is missing']);
          exit;
      }
      $result = curlRequest('https://onlinesim.io/api/cancelNumber.php', ['apikey' => $apiKey, 'id' => $numberId], 'POST');
      echo json_encode($result);
      exit;
    }

    $country = isset($_POST['country']) ? $_POST['country'] : null;
    $service = isset($_POST['service']) ? $_POST['service'] : null;

    if($country === null || $service === null){
        echo json_encode(['success' => false, 'error' => 'country or service is missing']);
        exit;
    }

    $result = curlRequest('https://onlinesim.io/api/getNum.php', ['apikey' => $apiKey, 'country' => $country, 'service' => $service], 'POST');
    echo json_encode($result);
  }
?>

