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
//        'host'        => 'sirius.c2hsrkenxay5.ap-northeast-1.rds.amazonaws.com', //本番用RDS
//        'adapter' => 'Postgresql',
        //---ローカルDB接続情報▼---//
        'username'    => 'pman',
        'password'    => 'NBs73j(Dhqe#',
//        'dbname'      => 'sunpexist_universal_db',
        'dbname'      => 'sp_universal_db',
        //---ローカルDB接続情報▲---//
/*
        //---本番DB接続情報▼---//
        'username'    => 'sp_universal_db',
        'password'    => 'xom4xhn8lqufopui',
        'dbname'      => 'sp_universal_db',
        //---本番DB接続情報▲---//
*/
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
