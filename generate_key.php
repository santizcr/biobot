<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$keysDirectory = 'keys/';
$maxAttempts = 100; 


function generateRandomKey($length = 32) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $key = '';
    try {
        for ($i = 0; $i < $length; $i++) {
            $key .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $key;
    } catch (Exception $e) {
        error_log('Ошибка генерации ключа: ' . $e->getMessage());
        return false;
    }
}


function keyExists($key, $directory) {
    $filename = $directory . $key . '.txt';
    if (!file_exists($filename)) {
        return false;
    }

   
    $keyInfo = @json_decode(file_get_contents($filename), true); 
    if ($keyInfo === null) {
        return false; 
    }
    if (isset($keyInfo['expirationDate'])) {
        $expirationTime = strtotime($keyInfo['expirationDate']);
        if ($expirationTime < time()) {
           
            return false;
        }
    }

    return true; 
}


try {
    $duration = $_POST['duration'] ?? null;
    $keyLength = $_POST['key_length'] ?? null;

    
    if (!is_numeric($duration) || $duration <= 0) {
        throw new Exception('Некорректная длительность.');
    }
    if (!is_numeric($keyLength) || $keyLength < 1 || $keyLength > 32) {
        throw new Exception('Некорректная длина ключа (должна быть от 1 до 32).');
    }

   
    $files = glob( __DIR__ . '/' . $keysDirectory . '*.txt'); 
    if ($files === false) {
        error_log('Ошибка glob(): ' . print_r(error_get_last(), true)); 
    } else {
        foreach ($files as $filename) {
            if (!file_exists($filename)) {
                error_log('Файл не существует (после glob): ' . $filename);
                continue; 
            }
                        $keyInfo = @json_decode(file_get_contents($filename), true);
            if ($keyInfo !== null && isset($keyInfo['expirationDate'])) {
                $expirationTime = strtotime($keyInfo['expirationDate']);
                if ($expirationTime !== false && $expirationTime < time()) {
                   
                    if (is_writable($filename)) { 
                        if (unlink($filename)) {
                            error_log('Удален устаревший ключ: ' . $filename);
                        } else {
                            error_log('Не удалось удалить устаревший ключ: ' . $filename);
                        }
                    } else {
                        error_log('Нет прав на запись для файла: ' . $filename);
                    }
                }
            } else {
                error_log('Не удалось получить информацию о ключе или нет expirationDate: ' . $filename);
            }
        }
    }


  
    $attempts = 0;
    do {
        $key = generateRandomKey($keyLength);
        if ($key === false) {
            throw new Exception('Не удалось сгенерировать ключ.');
        }
        $attempts++;
        if ($attempts > $maxAttempts) {
            throw new Exception('Не удалось сгенерировать уникальный ключ после ' . $maxAttempts . ' попыток.');
        }
    } while (keyExists($key, $keysDirectory));

   
    if (!is_dir($keysDirectory)) {
        if (!mkdir($keysDirectory, 0777, true)) {
            throw new Exception('Не удалось создать директорию.');
        }
    }

   
    $keyInfo = [
        'key' => $key,
        'duration' => (int)$duration, 
        'creationDate' => date('Y-m-d H:i:s'),
        'activated' => false,
    ];

    $filename = $keysDirectory . $key . '.txt';
    if (file_put_contents($filename, json_encode($keyInfo)) === false) {
        throw new Exception('Не удалось сохранить ключ в файл.');
    }

    echo json_encode([
        'status' => 'success',
        'key' => $key,
        'expiration' => 'Не активирован' 
    ]);

} catch (Exception $e) {
    error_log('Ошибка PHP: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>