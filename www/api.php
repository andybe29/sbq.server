<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Content-Type');

    if (ob_get_length()) ob_clean();

    $data = file_get_contents('php://input');

    $post = $data ? json_decode($data, true) : [];
/*
    error_log('', 3, 'post.log');
    error_log(json_encode($post, JSON_UNESCAPED_UNICODE) . PHP_EOL, 3, 'post.log');
*/
    require '../config.php';

    if (API::_checkIncomeData($post)) {
        $api = new API($sql);

        $response = $api->{$post['method']}(isset($post['params']) ? $post['params'] : []);
    } else {
        $response = API::$response;
    }

    $response['id'] = isset($post['id']) ? $post['id'] : null;

    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
