<?php
/**
 * Created by PhpStorm.
 * User: eson
 * Date: 2021-02-19
 * Time: 23:10
 */

namespace models;

/**
 * Class Hook
 * @desc web hook 自动更新版本
 * @package models
 */
class Hook
{
    static public function pull ($r, $b = 'master')
    {
        $cmd = "cd {$r} && git reset --hard origin/{$b} && git pull origin {$b} 2>&1";
        exec($cmd,$arr);
        return $arr;
    }
}