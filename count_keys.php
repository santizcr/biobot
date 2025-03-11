<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 

$keysDirectory = 'keys/';

function countKeys($directory) {
    $files = glob(__DIR__ . '/' . $directory . '*.txt');
    if ($files === false) {
        error_log('Ошибка glob(): ' . print_r(error_get_last(), true));
        return 0;
    }
    return count($files);
}

try {
    $keyCount = countKeys($keysDirectory);

    echo json_encode([
        'status' => 'success',
        'count' => $keyCount
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