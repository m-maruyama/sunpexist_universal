<?php

defined('APP_PATH') || define('APP_PATH', realpath('.'));

return new \Phalcon\Config(array(
    // 'database' => array(
        // 'host'        => 'sirius.c2hsrkenxay5.ap-northeast-1.rds.amazonaws.com',
        // 'username'    => 'sunpexist_db_test',
        // 'password'    => 'k3q2rgdxd2v6zwbx',
        // 'dbname'      => 'sunpexist_db_test',
        // // 'charset'     => 'utf8',
    // ),
    'database' => array(
        'host'        => '10.0.2.2', //ローカル
//        'host'        => 'testdb.cedx29aizopt.ap-northeast-1.rds.amazonaws.com', //開発用RDS
        'username'    => 'pman',
        'password'    => 'NBs73j(Dhqe#',
        'dbname'      => 'sunpexist_universal_db',
        // 'charset'     => 'utf8',
    ),
    'application' => array(
        'controllersDir' => APP_PATH . '/app/controllers/',
        // 'formsDir' => APP_PATH . '/app/forms/',
        'modelsDir'      => APP_PATH . '/app/models/',
        'migrationsDir'  => APP_PATH . '/app/migrations/',
        // 'viewsDir'       => APP_PATH . '/app/views/',
        // 'pluginsDir'     => APP_PATH . '/app/plugins/',
        'libraryDir'     => APP_PATH . '/app/library/',
        // 'cacheDir'       => APP_PATH . '/app/cache/',
        'logDir'       => APP_PATH . '/app/log/',
        'baseUri'        => '/universal/',
    )
));
