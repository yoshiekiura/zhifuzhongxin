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

namespace app\common\logic;


use app\api\service\ApiPayment;
use app\api\service\withdraw\Paofen;
use app\common\library\enum\CodeEnum;
use app\common\library\exception\OrderException;
use app\common\model\PayChannel;
use think\Db;
use think\Exception;
use think\Log;

class ChannelTemplate extends BaseLogic
{

    /**
     * 保存渠道模板
     */
    public function saveChannelTemplateInfo($data)
    {
        $validate = $this->validateChannelTemplate->check($data);
        if (!$validate) {
            return ['code' => CodeEnum::ERROR, 'msg' => $this->validateChannelTemplate->getError()];
        }
        Db::startTrans();
        try {
            $this->modelChannelTemplate->setInfo($data);
            $action = isset($data['id']) ? '编辑' : '新增';
            action_log($action, '支付渠道' . $data['name']);
            Db::commit();
            return ['code' => CodeEnum::SUCCESS, 'msg' => $action . '渠道成功'];
        } catch (\Exception $ex) {
            Db::rollback();
            Log::error($ex->getMessage());
            return ['code' => CodeEnum::ERROR, 'msg' => config('app_debug') ? $ex->getMessage() : '未知错误'];
        }
    }




}
