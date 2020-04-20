<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $secret = isset($_POST['secretKey']) ? $_POST['secretKey'] : (isset($_GET['secretKey']) ? $_GET['secretKey'] : false);

    if (!$secret || $secret !== 'noonecanknowthiskeybecauseitssecret48152020') {
        $result = 'You have no access to this page!';
    } else {
        if (function_exists('opcache_reset')) {
            clearstatcache( true );
            opcache_reset();
            $result = 'OPCache was successfully cleared';
        } else {
            $result = 'Zend function "opcache_reset" doesnt exists.';
        }
    }
} catch (\Exception $exception) {
    $result = 'Error while clearing a cache: ' . $exception->getMessage();
}

http_response_code(200);
echo json_encode($result);
exit();


