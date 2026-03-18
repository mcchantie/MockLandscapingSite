<?php
    http_response_code(200);
    echo json_encode(['ok' => true, 'response' => $_ENV['APP_ENV']]);
    exit;
