<?php
/**
 *  +----------------------------------------------------------------------
 *  | 中通支付系统 [ WE CAN DO IT JUST THINK ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2018 http://www.iredcap.cn All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed ( https://www.apache.org/licenses/LICENSE-2.0 )
 *  +----------------------------------------------------------------------
 *  | Author: Brian Waring <BrianWaring98@gmail.com>
 *  +----------------------------------------------------------------------
 */

namespace app\admin\validate;

class ChannelTemplate extends BaseAdmin
{
    // 验证规则
    protected $rule = [
        'name'  => 'require',
        'port_address'    => 'require',
        'class_name'    => 'require'
    ];

    // 验证提示
    protected $message = [
        'name.require'    => '模板名不能为空！',
        'port_address.require'     => '接口地址不能为空！',
        'class_name.require'     => '基础类名不能为空！'
    ];
}