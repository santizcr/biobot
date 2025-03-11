<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$keysDirectory = 'keys/';


function getKeyInfo($filename) {
    $data = @file_get_contents($filename); 
    if ($data === false) {
        return false; 
    }
    $keyInfo = @json_decode($data, true); 
    if ($keyInfo === null) {
        return false; 
    }
    return $keyInfo;
}

try {
    $key = $_GET['key'] ?? null;

    if (empty($key)) {
        throw new Exception('Ключ отсутствует.');
    }

    $filename = $keysDirectory . $key . '.txt';
    if (!file_exists($filename)) {
        throw new Exception('Ключа не существует.'); 
    }

    
    $keyInfo = getKeyInfo($filename);
    if ($keyInfo === false) {
        
        throw new Exception('Не удалось прочитать информацию о ключе. Файл ключа возможно поврежден.');

    }

    
    if (isset($keyInfo['activated']) && $keyInfo['activated'] === true) {
       
        if (isset($keyInfo['expirationDate'])) {
            $expirationTime = strtotime($keyInfo['expirationDate']);

            if ($expirationTime === false) {
                throw new Exception('Неверный формат даты в файле.');
            }

            if ($expirationTime < time()) {
                // Срок действия истек
                 echo json_encode([
                    'Status' => 'Error',
                    'MessageString' => 'Срок действия ключа истек.',
                    'Expiration' => date('Y-m-d H:i:s', $expirationTime),
                    'Activated' => true
                ]);
                exit;

            }

           
            echo json_encode([
                'Status' => 'Success',
                'MessageString' => 'Access granted (already activated)',
                'Username' => 'PremiumUser',
                'Expiration' => date('Y-m-d H:i:s', $expirationTime),
                'Activated' => true
            ]);
            exit; 

        } else {
            throw new Exception('Отсутствует время действия ключа.');
        }

    } else {
        
        if (isset($keyInfo['duration'])) {
            

            $duration = (int)$keyInfo['duration'];
            
            $expirationTime = time() + ($duration * 60);
            $expirationDate = date('Y-m-d H:i:s', $expirationTime);


            echo json_encode([
                'Status' => 'Pending Activation',
                'MessageString' => 'Key not yet activated',
                'Username' => 'PremiumUser',
                'Expiration' => $expirationDate, 
                'Activated' => false,
                'Duration' => $duration
            ]);
            exit;

        } else {
            throw new Exception('Отсутствует длительность ключа.');
        }
    }

} catch (Exception $e) {
    error_log('Ошибка PHP: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'Status' => 'Error',
        'MessageString' => $e->getMessage()
    ]);
}
?>