<?php


namespace app\usercenter\logic;


use app\common\library\enum\CodeEnum;
use app\common\logic\BaseLogic;
use app\common\logic\Log;
use think\Db;

class MerchantsBinding extends BaseLogic
{
    public function bind($channel_user_id, $merchant_user_id, $uid)
    {
        $insert_data = [
            'channel_user_id' => $channel_user_id,
            'merchant_user_id' => $merchant_user_id,
            'merchant_id' => $uid
        ];
        $ret = $this->modelMerchantBinding->where($insert_data)->find();
        if ($ret){
            return ['code' => CodeEnum::ERROR, 'msg' => '不能重复绑定'];
        }
        $insert_data['addtime'] = time();
        $this->modelMerchantBinding->save($insert_data);
        return ['code' => CodeEnum::SUCCESS, 'msg' => '绑定成功'];
    }

    /**
     * 商户绑定列表
     * @param $where
     * @param $alias
     * @param $field
     * @param string $order
     * @return mixed
     */
    public function bindingUserList($where, $alias, $field, $role, $order = 'addtime desc ')
    {
        $modelMerchantBinding = $this->modelMerchantBinding;
        $join = [
            ['pay_center_user pu', 'pu.id = ' .  $role],
            ['user u', 'u.uid = a.merchant_id'],
        ];
        $modelMerchantBinding->alias($alias);
        $modelMerchantBinding->join = $join;
        return $modelMerchantBinding->getList($where, $field,  $order, 15);
    }

    /**
     * 渠道同意绑定
     */
    public function passBingdingUser($data, $uid)
    {
        Db::startTrans();
        try {
            $bind = $this->modelMerchantBinding->find($data['bindId']);
            $bind->status = 1;
            $bind->channel_account_id = $data['accountId'];
            $bind->save();
            $field = 'a.*,c.pay_center_uid';
            $account = $this->modelPayCenterChannelAccount->alias('a')
                ->join('pay_channel c' , 'c.id = a.channel_id', 'LEFT')
                ->field($field)->find($data['accountId']);
            $account->status = 1;
            $account->save();
            Db::commit();
            return ['code' => CodeEnum::SUCCESS, 'msg' => '绑定成功'];
        }catch (\Exception $e){
            Db::rollback();
            halt($e->getMessage());
            \think\Log::error('usercenter:'.$e->getMessage());
            return ['code' => CodeEnum::ERROR, 'msg' => '未知错误'];
        }

    }
}