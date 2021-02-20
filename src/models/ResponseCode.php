<?php
/**
 * Created by PhpStorm.
 * User: eson
 * Date: 2021-02-19
 * Time: 23:09
 */

namespace models;

/**
 * Class ResponseCode
 * @desc 错误码类
 * @package models
 */
class ResponseCode
{
    /**
     * 返回成功
     */
    const SUCCESS = 1;

    /**
     * 令牌类 100
     */
    const TOKEN_ERROR = 110;   // 令牌错误

    /**
     * 参数错误类 300
     */
    const ERROR_ROOT_NOT_EMPTY = 301; // 项目路径不能为空
    const ERROR_ROOT_NOT_SET = 302; // 项目路径不存在

    /**
     * 配置git运行错误类 500
     */
    const ERROR_SHELL_EXEC = 501; // 终端运行错误，可能当前运行程序用户无法操作git权限问题。

    const CODE_MAP = [
        self::SUCCESS => 'OKAY',
        self::TOKEN_ERROR => '签名错误！',
        self::ERROR_ROOT_NOT_EMPTY => '项目路径不能为空！',
        self::ERROR_ROOT_NOT_SET => '项目路径不存在！',
        self::ERROR_SHELL_EXEC => '终端运行错误，可能当前运行程序用户无法操作git权限问题。',
    ];

}