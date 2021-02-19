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
    public function index ()
    {
        $r = $this->params('r');
        $b = $this->params('b');
        $this->validate($r);

        return $this->json(
            ErrorCode::SUCCESS,
            Hook::pull($r, $b),
            200
        );
    }

    /**
     * @desc 验证参数
     * @param $root string 项目路径
     * @return bool|string
     */
    public function validate ($root)
    {
        $this->checkoutToken();

        if (!$root) {
            return $this->json(
                ErrorCode::ERROR_ROOT_NOT_EMPTY,
                [],
                406
            );
        }

        $pdf_dir_exists = "[ -d '{$root}' ] && echo true || echo false";
        $pdf_dir_exists_result = shell_exec($pdf_dir_exists);
        if (trim($pdf_dir_exists_result) === 'false') {
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
        $raw_post_data = file_get_contents('php://input');
        $gogs_signature = hash_hmac('sha256', $raw_post_data, $key);
        if (!empty($_SERVER['HTTP_X_GOGS_SIGNATURE']) &&
            $_SERVER['HTTP_X_GOGS_SIGNATURE'] != $gogs_signature) {
            return $this->json(ErrorCode::TOKEN_ERROR, [], 401);
        }
        return TRUE;
    }

    protected function params ($name, $default = '')
    {
        if ($default) {
            return $default;
        }

        if (!empty($_REQUEST[$name])) {
            return $_REQUEST[$name];
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
        ]);
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
            $ini_array = parse_ini_file('./.env');
            return $ini_array[$name];
        }

        return $default;
    }
}