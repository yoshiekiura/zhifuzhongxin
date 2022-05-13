<?php


namespace app\usercenter\controller;


use app\common\logic\MerchantBinding;
use think\exception\Handle;

class MerchantsBinding extends Base
{


    /**
     * 申请绑定用户
     */
    public function applyBindingUser()
    {
	 if ($this->request->isPost()) {
            $params = $this->request->param();
            $logicChannel = new Channel();
            $ret =$logicChannel->bind($params);
            if ($ret['code'] == 0){
                $this->error($ret['msg']);
            }
            $this->success($ret['msg']);
        }
        $this->assign('channel_id', $this->request->param('channel_id'));
        return $this->fetch();

    }
     /**
     * 商户绑定用户列表
     */
    public function bindingUserList()
    {

    }
     /**
     * 渠道绑定用户列表
     */
    public function channelBindingUserList()
    {
    }
 
    /**
     * 渠道通过申请绑定
     */
    public function passBingdingUser()
    {
    }

     /**
     * 渠道拒绝申请绑定
     */
    public function denyBingdingUser()
    {
    }


     /**
     * 取消绑定用户
     */
    public function cancelBindingUser()
    {
    }

     /**
     * 禁用绑定用户
     */
    public function disableBindingUser()
    {
    }
 /*
      * 启用绑定用户
     */
    public function enableBindingUser()
    {
    } 
}
