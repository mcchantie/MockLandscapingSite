<?php
$cfg = '/home/aeratepl/secure/config_uat.php';

if (is_file($cfg) && is_readable($cfg)) {
    require_once $cfg;  // sets $_SERVER/$_ENV/putenv
} else {
    error_log("bootstrap/env_uat.php: missing config at $cfg");
}
