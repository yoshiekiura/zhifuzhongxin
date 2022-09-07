<?php


namespace app\common\logic;


use app\common\library\enum\CodeEnum;

class BindChannel extends BaseLogic
{


    /**
     * 保存绑定渠道信息
     */
    public function saveBind($data)
    {
        $validate = $this->validateBindChannel->scene('saveBind')->check($data);
        if ( false === $validate){
            return ['code' => CodeEnum::ERROR, 'msg' => $this->validateBindChannel->getError()];
        }

        $user = $this->modelUser->where('uid', $data['uid'])->find();
        $channel  = $this->modelPayChannel->where('id', $data['channel_id'])->find();

        $saveData = array(
            'merchant_user_id' => $user['pay_center_uid'],
            'channel_user_id' => $channel['pay_center_uid'],
            'user_id' => $user['uid'],
            'channel_id' => $channel['id'],
            'status' => 0,
            'channel_status' => 1
        );
        $ret =  $this->modelBindChannel->allowField(true)->save($saveData);
        if ($ret){
            return ['code' => CodeEnum::SUCCESS, 'msg' => '申请已发送，等待审核'];
        }
        return ['code' => CodeEnum::ERROR, 'msg' => '申请失败'];
    }

    /**
     * 保存账号信息
     */
    public function saveAccount($data)
    {
        $validate = $this->validatePayCenterUserAccount->scene($data['scene'])->check($data);
        if (!$validate) {
            return [ 'code' => CodeEnum::ERROR, 'msg' => $this->validatePayCenterUserAccount->getError()];
        }
        $ret = $this->modelPayCenterUserAccount->setInfo($data);
        if ($ret){
            return ['code' =>CodeEnum::SUCCESS, 'msg' => '添加成功'];
        }else{
            return ['code' =>CodeEnum::ERROR, 'msg' => '添加失败'];
        }
    }

    public function channelBindHandle($data)
    {
       $ret = $this->modelBindChannel->setInfo($data);
       if ($ret){
           return ['code' => CodeEnum::SUCCESS, 'msg' => '操作成功'];
       }else{
           return ['code' => CodeEnum::ERROR, 'msg' => '操作失败！'];
       }
    }
}