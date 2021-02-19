<?php
/**
 * Created by PhpStorm.
 * User: eson
 * Date: 2021-02-19
 * Time: 16:15
 */

/**
 * Class GogsHook
 * @desc web hook 自动更新脚本
 * @package GogsHook
 */
class GogsHook
{
    protected $key = null;
    protected $path = null;

    function __construct ()
    {
        // 配置密钥 SHA256 HMAC 哈希值，并设置为 X-Gogs-Signature 请求头的值。
        $this->key = $this->_env('GOGS_SIGNATURE');
        // 配置需要自动更新的项目目录
        $this->path = $this->_env('PROJECT_PATH');
    }

    public function index ()
    {
        $raw_post_data = file_get_contents('php://input');
        $gogs_signature = hash_hmac('sha256', $raw_post_data, $this->key);

        if (!empty($_SERVER['HTTP_X_GOGS_SIGNATURE']) &&
            $_SERVER['HTTP_X_GOGS_SIGNATURE'] === $gogs_signature) {
            $ret = $this->git_pull();
            return $this->json($ret);
        }

        return $this->json([], 'No push data received',0);
    }

    /**
     * @desc 获取配置
     * @param null $name
     * @param null $default
     * @return string
     */
    private function _env ($name = null, $default = null)
    {
        if ($name) {
            $ini_array = parse_ini_file('./.env');
            return $ini_array[$name];
        }

        return $default;
    }

    /**
     * @desc 执行更新
     * @return bool|string
     */
    private function git_pull ()
    {
        $path = $this->path;
        $cmd = "cd {$path} && git pull 2>&1";
        exec($cmd,$arr);
        return $arr;
    }

    /**
     * @desc 结果集返回封装
     * @param string $msg
     * @param int $code
     * @param array $data
     * @return false|string
     */
    private function json ($data = [], $msg = 'SUCCESS', $code = 1)
    {
        echo json_encode([
            'msg' => $msg,
            'code' => $code,
            'data' => $data,
        ]);
        return true;
    }
}