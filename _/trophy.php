<?php

use Olz\Utils\EnvUtils;
use Olz\Utils\HttpUtils;
use Olz\Utils\LogsUtils;

require_once __DIR__.'/config/init.php';

session_start_if_cookie_set();

require_once __DIR__.'/admin/olz_functions.php';

$logger = LogsUtils::fromEnv()->getLogger(basename(__FILE__));
$http_utils = HttpUtils::fromEnv();
$http_utils->setLog($logger);
$http_utils->validateGetParams([], $_GET);

$env_utils = EnvUtils::fromEnv();
$code_href = $env_utils->getCodeHref();
$new_url = "{$code_href}trophy";
http_response_code(301);
header("Location: {$new_url}");
