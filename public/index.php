<?php

error_reporting(E_ALL);

define('APP_PATH', realpath('..'));

try {

    /*
     * Read the configuration
     */
    $config = include APP_PATH.'/app/config/config.php';

    /**
     * Read auto-loader.
     */
    include APP_PATH.'/app/config/loader.php';

    /**
     * Read services.
     */
    include APP_PATH.'/app/config/services.php';

    /**
     * Read define.
     */
    include APP_PATH.'/app/config/define.php';

    /*
     * Starting the application
    */
    $app = new \Phalcon\Mvc\Micro();

    /*
     * Assign service locator to the application
     */
    $app->setDi($di);

    /**
     * Incude Application.
     */
    include __DIR__.'/../app/app.php';
    include __DIR__.'/../app/common.php';
    include __DIR__.'/../app/csv_download.php';
    include __DIR__.'/../app/account.php';
    include __DIR__.'/../app/info.php';
    include __DIR__.'/../app/inquiry.php';
    include __DIR__.'/../app/q_and_a.php';
    include __DIR__.'/../app/history.php';
    include __DIR__.'/../app/unreturn.php';
    include __DIR__.'/../app/receive.php';
    include __DIR__.'/../app/lend.php';
    include __DIR__.'/../app/print.php';
    include __DIR__.'/../app/stock.php';
    include __DIR__.'/../app/manpower_info.php';
    include __DIR__.'/../app/wearer.php';
    include __DIR__.'/../app/login.php';
    include __DIR__.'/../app/password.php';
    include __DIR__.'/../app/home.php';
    include __DIR__.'/../app/purchase_input.php';
    include __DIR__.'/../app/purchase_history.php';
    include __DIR__.'/../app/order_send.php';
    include __DIR__.'/../app/wearer_edit.php';
    include __DIR__.'/../app/wearer_edit_order.php';
    include __DIR__.'/../app/wearer_input.php';
    include __DIR__.'/../app/wearer_end.php';
    include __DIR__.'/../app/wearer_change.php';
    include __DIR__.'/../app/wearer_change_order.php';
    include __DIR__.'/../app/wearer_other.php';
    include __DIR__.'/../app/wearer_add_order.php';
    include __DIR__.'/../app/wearer_return_order.php';
    include __DIR__.'/../app/wearer_search.php';
    include __DIR__.'/../app/wearer_order.php';
    include __DIR__.'/../app/wearer_end_order.php';
    include __DIR__.'/../app/wearer_size_change.php';
    include __DIR__.'/../app/importCsv.php';
    include __DIR__.'/../app/ChromePhp.php';

    /*
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
