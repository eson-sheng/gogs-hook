<?php
/**
 * Created by PhpStorm.
 * User: eson
 * Date: 2021-02-19
 * Time: 23:10
 */

namespace models;

use models\ResponseCode as ErrorCode;

class Index
{
    private $config;

    public function index ()
    {
        $this->checkoutToken();
        $r = $this->params('r');
        $b = $this->params('b', 'master');
        $o = $this->params('o');
        $s = $this->params('s') === 'true';
        $this->validate($r, $o);

        $ret = Hook::pull($r, $b, $o, $exitCode, $s);
        if ($exitCode !== 0) {
            return $this->json(
                ErrorCode::ERROR_SHELL_EXEC,
                $ret,
                500
            );
        }

        return $this->json(
            ErrorCode::SUCCESS,
            $ret,
            200
        );
    }

    /**
     * @desc 验证参数
     * @param $root string 项目路径
     * @return bool|string
     */
    public function validate ($root, $host = null)
    {
        if (!is_string($root) || $root === '') {
            return $this->json(
                ErrorCode::ERROR_ROOT_NOT_EMPTY,
                [],
                406
            );
        }

        if ($host !== null) {
            if ($host === 'local' || !Hook::validHost($host)) {
                return $this->json(ErrorCode::ERROR_HOST_INVALID, [], 406);
            }
        }

        // Exact host/path pairs: no wildcard or recursive directory access.
        $targets = json_decode($this->_env('ALLOWED_TARGETS', '{}'), true);
        $target = $host === null ? 'local' : $host;
        $allowed = is_array($targets) && isset($targets[$target]) && is_array($targets[$target])
            ? $targets[$target] : [];
        if ($root[0] !== '/' || strpos($root, "\0") !== false ||
            preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $root) ||
            !in_array($root, $allowed, true)) {
            return $this->json(ErrorCode::ERROR_TARGET_FORBIDDEN, [], 403);
        }

        if ($host === null && (!is_dir($root) || realpath($root) !== $root)) {
            return $this->json(
                ErrorCode::ERROR_ROOT_NOT_SET,
                [],
                406
            );
        }

        return TRUE;
    }

    /**
     * @desc 验证签名
     * @return bool|string
     */
    protected function checkoutToken ()
    {
        // 配置密钥 SHA256 HMAC 哈希值，并设置为 X-Gogs-Signature 请求头的值。
        $key = $this->_env('GOGS_SIGNATURE');
        $signature = isset($_SERVER['HTTP_X_GOGS_SIGNATURE']) ? $_SERVER['HTTP_X_GOGS_SIGNATURE'] : '';
        if (!is_string($key) || $key === '' || !is_string($signature) ||
            !preg_match('/\A[0-9a-f]{64}\z/i', $signature)) {
            return $this->json(ErrorCode::TOKEN_ERROR, [], 401);
        }
        $raw_post_data = file_get_contents('php://input');
        if ($raw_post_data === false) {
            return $this->json(ErrorCode::TOKEN_ERROR, [], 401);
        }
        $gogs_signature = hash_hmac('sha256', $raw_post_data, $key);
        if (!hash_equals($gogs_signature, strtolower($signature))) {
            return $this->json(ErrorCode::TOKEN_ERROR, [], 401);
        }
        return TRUE;
    }

    /**
     * @desc 获取接口参数
     * @param $name
     * @param null $default
     * @return null
     */
    protected function params ($name, $default = null)
    {
        $value = isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? $_POST[$name] : null);
        if ($value !== null && !is_string($value)) {
            return $this->json(ErrorCode::ERROR_PARAM_INVALID, [], 406);
        }
        if ($value !== null && $value !== '') {
            return $value;
        }

        return $default;
    }

    /**
     * @desc 结果集返回封装
     * @param int $code
     * @param array $data
     * @param int $head_code
     * @return string
     */
    protected function json ($code = 1, $data = [], $head_code = 200)
    {
        http_response_code($head_code);
        echo json_encode([
            'msg' => ErrorCode::CODE_MAP[$code],
            'code' => $code,
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE);
        exit();
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
            if ($this->config === null) {
                $path = dirname(__DIR__, 2) . '/.env';
                $this->config = is_readable($path) ? @parse_ini_file($path, false, INI_SCANNER_RAW) : [];
                if (!is_array($this->config)) {
                    $this->config = [];
                }
            }
            if (!isset($this->config[$name]) || !is_string($this->config[$name])) {
                return $default;
            }
            $value = $this->config[$name];
            // PHP INI strips double quotes but retains single quotes used by this project.
            if (strlen($value) >= 2 && $value[0] === "'" && substr($value, -1) === "'") {
                $value = substr($value, 1, -1);
            }
            return $value;
        }

        return $default;
    }
}
