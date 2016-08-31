<?php

error_reporting(E_ALL);

define('APP_PATH', realpath('..'));

try {

    /**
     * Read the configuration
     */
    $config = include APP_PATH . "/app/config/config.php";

    /**
     * Read auto-loader
     */
    include APP_PATH . "/app/config/loader.php";

    /**
     * Read services
     */
    include APP_PATH . "/app/config/services.php";

	/**
     * Starting the application
    */
    $app = new \Phalcon\Mvc\Micro();

    /**
     * Assign service locator to the application
     */
    $app->setDi($di);

    /**
     * Incude Application
     */
    include __DIR__ . '/../app/app.php';
    include __DIR__ . '/../app/common.php';
    include __DIR__ . '/../app/csv_download.php';
    include __DIR__ . '/../app/history.php';
    include __DIR__ . '/../app/unreturn.php';
    include __DIR__ . '/../app/receive.php';
    include __DIR__ . '/../app/lend.php';
    include __DIR__ . '/../app/login.php';
    include __DIR__ . '/../app/home.php';
    include __DIR__ . '/../app/wearer_input.php';
    include __DIR__ . '/../app/ChromePhp.php';

    /**
     * Handle the request
     */
    $app->handle();

    //確認用
    // /**
     // * Handle the request
     // */
    // $app = new \Phalcon\Mvc\Application($di);
    // echo $app->handle()->getContent();


} catch (\Exception $e) {
    echo $e->getMessage();
}
