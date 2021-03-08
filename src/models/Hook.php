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
    /**
     * @param $r string 服务器中项目部署的路径
     * @param $b string 分支名称
     * @return mixed
     */
    static public function pull ($r, $b)
    {
        $cmd = "cd {$r} && git checkout {$b} && git reset --hard {$b} && git pull 2>&1";
        exec($cmd,$arr);
        return $arr;
    }
}
