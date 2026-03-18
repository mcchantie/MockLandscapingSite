<?php
(function(array $vars){ foreach($vars as $k=>$v){ $_SERVER[$k]=$v; $_ENV[$k]=$v; putenv("$k=$v"); } })([
    'SMTP_HOST' => 'az1-cl9-its1.a2hosting.com',
    'SMTP_PORT' => '587',
    'TX_TOP_DRESSING_USERNAME' => 'aeratepl',
    'TX_TOP_DRESSING_PASSWORD' => '0nt0p702',
    'SMTP_DEBUG' => '2',
    'SMTP_FROM' => '["no-reply@txtopdressing.com","No-Reply"]',
    'SMTP_CC_ADDRESSES' => '["crysmcduf@gmail.com"]'
]);

SMTP_HOST=az1-cl9-its1.a2hosting.com
SMTPAuth=true
SMTP_PORT=587
SMTP_DEBUG=2
SMTP_FROM='["no-reply@txtopdressing.com","No-Reply"]'
SMTP_CC_ADDRESSES='["crysmcduf@gmail.com"]'
IS_HTML=true