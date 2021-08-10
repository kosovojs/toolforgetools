<?php

$myRoot = $_SERVER['DOCUMENT_ROOT'].'../';

$file = $myRoot.'/replica.my.cnf';
$db_config = parse_ini_file($file);

return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],
        'db' => [
            'host' => "tools.db.svc.eqiad.wmflabs",
            'user' => $db_config['user'],
            'pass' => $db_config['password'],
            'dbname' => 's53143__ccm_p',
        ],
        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
    ],
];
