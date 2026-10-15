<?php
/**
 * Created by PhpStorm.
 * User: eson
 * Date: 2021-02-19
 * Time: 23:12
 */

/*自动加载*/
spl_autoload_register(function ($class) {
    require __DIR__ . '/' . str_replace('\\', DIRECTORY_SEPARATOR, ltrim($class, '\\')) . '.php';
});
