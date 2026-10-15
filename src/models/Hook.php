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
    static public function pull ($r, $b, $o = null, &$exitCode = null, $reload = false)
    {
        $path = escapeshellarg($r);
        $branch = escapeshellarg($b);
        $branchRef = escapeshellarg('refs/heads/' . $b);
        $remoteRef = escapeshellarg('refs/remotes/origin/' . $b);
        $refspec = escapeshellarg('+refs/heads/' . $b . ':refs/remotes/origin/' . $b);
        $cmd = "cd {$path} && git check-ref-format {$branchRef}"
            . " && git fetch origin {$refspec}"
            . " && git checkout -f -B {$branch} {$remoteRef}"
            . " && git reset --hard {$remoteRef}";
        if ($o !== null && $o !== '') {
            if (!self::validHost($o)) {
                $exitCode = 1;
                return ['服务器地址格式错误'];
            }
            if ($reload === true) {
                $cmd .= ' && nginx -t && nginx -s reload';
            }
            $cmd = 'ssh -o BatchMode=yes -o ConnectTimeout=10 '
                . escapeshellarg('root@' . $o) . ' ' . escapeshellarg($cmd);
        }
        exec('(' . $cmd . ') 2>&1', $arr, $exitCode);
        return $arr;
    }

    static public function validHost ($host)
    {
        return is_string($host) && (
            filter_var($host, FILTER_VALIDATE_IP) !== false ||
            (strlen($host) <= 253 && preg_match('/\A[a-zA-Z0-9](?:[a-zA-Z0-9.-]*[a-zA-Z0-9])?\z/', $host))
        );
    }
}
