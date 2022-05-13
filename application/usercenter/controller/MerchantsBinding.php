<?php


namespace app\usercenter\controller;


use app\common\logic\User;
use think\exception\Handle;

class MerchantsBinding extends Base
{
    /**
     * 添加渠道账号
     */
    public function addChannelAccount()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $logicChannel = new Channel();
            $ret =$logicChannel->saveChannelAccount($params);
            if ($ret['code'] == 0){
                $this->error($ret['msg']);
            }
            $this->success($ret['msg']);
        }
        $this->assign('channel_id', $this->request->param('channel_id'));
        return $this->fetch();
    }


    /**
     * 申请绑定用户
     */
    public function applyBindingUser()
    {
    }
     /**
     * 商户绑定用户列表
     */
    public function bindingUserList()
    {
	    $map['pay_center_uid'] = $this->user['id'];
        $map['status'] = 1;
        !empty($this->request->get('name')) && $map['name']
            = ['like', '%'.$this->request->get('name').'%'];
        $logicChannel = new Channel();

        $channelLists = $logicChannel->getChannelsList($map,true, 'id desc');
        $this->assign('list',$channelLists );
        return $this->fetch();

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
