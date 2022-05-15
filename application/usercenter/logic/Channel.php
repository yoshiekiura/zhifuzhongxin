<?php


namespace app\usercenter\logic;


use app\common\library\enum\CodeEnum;
use app\common\logic\BaseLogic;
use think\Log;

class Channel extends BaseLogic
{

    /**
     * 添加渠道
     */
    public function saveChannel($data){
        $validateChannel = new \app\usercenter\validate\Channel();
        $validate = $validateChannel->scene($data['scene'])->check($data);
        if ($validate == false){
            return ['code' => CodeEnum::ERROR, 'msg' =>$validateChannel->getError() ];
        }
        $ret = $this->modelPayChannel->allowField(true)->isUpdate($data['scene'] == 'add' ? false : true)->save($data);
        $msg  = $data['scene'] == 'add' ? '添加' : '修改';
        if (!$ret){
            return ['code' => CodeEnum::SUCCESS, 'msg' =>$msg . '失败'];
        }
        return ['code' => CodeEnum::SUCCESS, 'msg' =>$msg . '成功'];
    }

    /**
     * 获取渠道列表
     */
    public function getChannelsList($map, $alias ='a', $field = true, $order='id desc', $paginate = 15)
    {
        $modelPayChannel = $this->modelPayChannel;
        $modelPayChannel->alias($alias);
        $modelPayChannel->join= [
            ['cm_channel_template t', 't.id = a.template_id', 'left']
        ];
        return $modelPayChannel->getList($map, $field, $order, $paginate);
    }

    /**
     * 添加账号
     */
    public function saveChannelAccount($data)
    {
        $validateChannel = new \app\usercenter\validate\ChannelAccount();
        $validate = $validateChannel->scene($data['scene'])->check($data);
        if ($validate == false){
            return ['code' => CodeEnum::ERROR, 'msg' =>$validateChannel->getError() ];
        }
        $ret = $this->modelPayCenterChannelAccount->allowField(true)->isUpdate($data['scene'] == 'add' ? false : true)->save($data);
        $msg  = $data['scene'] == 'add' ? '添加' : '修改';
        if (!$ret){
            return ['code' => CodeEnum::SUCCESS, 'msg' =>$msg . '失败'];
        }
        return ['code' => CodeEnum::SUCCESS, 'msg' =>$msg . '成功'];
    }

    /**
     * 配置编码
     */
    public function setChannelCode($data){
        $modelPayCenterChannelCode = $this->modelPayCenterChannelCode;
        try {
            $modelPayCenterChannelCode->startTrans();
            //先删除配置的编码
            $modelPayCenterChannelCode->where([ 'channel_id' => $data['channel_id']])->delete();
            $insert_data = [];
            foreach ($data['d'] as $key => $v){
                if (!empty($v)){
                    $data1['channel_id'] = $data['channel_id'];
                    $data1['code_id'] = explode('_', $key)[0];
                    $data1['value'] = $v;
                    $insert_data[] = $data1;
                }
            }
            if (count($insert_data) > 0){
                $modelPayCenterChannelCode->insertAll($insert_data);
            }
            $modelPayCenterChannelCode->commit();
            return ['code' => CodeEnum::SUCCESS, 'msg' => '操作成功'];
        }catch (\Exception $e){
            $modelPayCenterChannelCode->rollback();
            Log::error($e->getMessage());
            return ['code' => CodeEnum::ERROR, 'msg' => '操作失败'];
        }
    }

    /**
     *  匹配已经设置的编码
     */
    public function matchingCode($channel_id)
    {
        $modelPayCenterChannelCode = $this->modelPayCenterChannelCode;
        $pay_center_channel_code = collection($modelPayCenterChannelCode->where([ 'channel_id' =>$channel_id])->select());
        $pay_code = $this->modelPayCode->where([ 'status' =>1])->select();
        if (!$pay_center_channel_code->isEmpty()){
            $matchingCode = array_column($pay_center_channel_code->toArray(), 'value', 'code_id');
            foreach ($pay_code as $key => &$code){
                if (in_array($code['id'],  array_keys($matchingCode))){
                    $code['value'] = $matchingCode[$code['id']];
                }
            }
        }
        return $pay_code;
    }

}