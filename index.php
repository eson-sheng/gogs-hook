<?php
/**
 * Created by PhpStorm.
 * User: eson
 * Date: 2021-02-19
 * Time: 16:48
 */

require_once __DIR__ . '/src/autoload.php';

use models\Index;

$obj = new Index();
$obj->index();