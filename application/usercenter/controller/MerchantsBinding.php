<?php


namespace app\usercenter\controller;


use think\Db;

class MerchantsBinding extends Base
{
    /**
     * 申请绑定用户
     */
    public function applyBindingUser()
    {
        $uid = $this->request->param('uid');
        $map = [
            'uid' => $uid,
            'pay_center_uid' => $this->user['id']
        ];
        $row = $this->modelUser->where($map)->find();
        if (!$row) {
            $this->error('数据错误');
        }
        if ($this->request->isPost()) {
            $channel_user_id = $this->request->param('userid', '');
            if (empty($channel_user_id)) {
                $this->error('绑定用户不能为空');
            }
            $logicChannel = new \app\usercenter\logic\MerchantsBinding();
            $ret = $logicChannel->bind($channel_user_id, $this->user['id'], $uid);
            if ($ret['code'] == 0) {
                $this->error($ret['msg']);
            }
            $this->success($ret['msg']);
        }
        $map = [
            'status' => 1,
            'user_type' => 1,
            'id' => ['<>', $this->user['id']]
        ];
        $users = $this->modelPayCenterUser->where($map)->select();
        $this->assign('uid', $uid);
        $this->assign('users', $users);
        return $this->fetch();

    }

    /**
     * 商户绑定用户列表
     */
    public function bindingUserList()
    {
        $map['a.merchant_user_id'] = $this->user['id'];
        !empty($this->request->get('user_username')) && $map['u.username']
            = ['like', '%' . $this->request->get('user_username') . '%'];
        $field = 'a.*, pu.username as pay_center_username, u.username as user_username';
        $list = $this->logicMerchantsBinding->bindingUserList($map, 'a', $field, 'a.channel_user_id', 'a.addtime desc');
        /*暂时获取一个渠道*/
        foreach ($list as $key=>$item){
            $item['channel_name'] = $this->modelPayChannel->where(['pay_center_uid' => $item['channel_user_id']])->value('name');
        }
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 渠道绑定用户列表
     */
    public function channelBindingUserList()
    {
        $map['a.channel_user_id'] = $this->user['id'];
        !empty($this->request->get('user_username')) && $map['u.username']
            = ['like', '%' . $this->request->get('user_username') . '%'];
        $logicMerchantsBinding = new \app\usercenter\logic\MerchantsBinding();
        $field = 'a.*, pu.username as pay_center_username, u.username as user_username';
        $list = $logicMerchantsBinding->bindingUserList($map, 'a', $field, 'a.merchant_user_id', 'a.addtime desc');
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 渠道通过申请绑定
     */
    public function passBingdingUser()
    {
        $params = $this->request->param();
        if ( !isset($params['bindId']) or empty($params['bindId']) or !isset($params['accountId']) or empty($params['accountId'])){
            $this->error('数据错误');
        }
        $logicMerchantsBinding = new \app\usercenter\logic\MerchantsBinding();
        $ret  = $logicMerchantsBinding->passBingdingUser($params, $this->user['id']);
        if ($ret['code'] != 1){
            $this->error($ret['msg']);
        }
        $this->success($ret['msg']);
    }

    /**
     * 渠道拒绝申请绑定
     */
    public function denyBingdingUser()
    {
        $id = $this->request->param('id', '');
        $map = [
            'id' => $id,
            'channel_user_id' => $this->user['id'],
            'status' =>0
        ];
         $row = $this->modelMerchantBinding->where($map)->find();
         if (!$row){
             $this->error('数据错误');
         }
        $row->status = 2;
        $row->save();
        $this->success('操作成功');
    }

    /**
     * 取消绑定用户
     */
    public function cancelBindingUser()
    {
        $id = $this->request->param('id');
        if (empty($id)){
            $this->error('数据错误');
        }
        $logicMerchantsBinding = new \app\usercenter\logic\MerchantsBinding();
        $ret  = $logicMerchantsBinding->cancelBindingUser($id, $this->user['id']);
        if ($ret['code'] != 1){
            $this->error($ret['msg']);
        }
        $this->success($ret['msg']);
    }

    /**
     * 禁用启用
     * @throws \think\exception\DbException
     */
    public function enableHandel()
    {
        $en_able = $this->request->param('en_able', '');
        $id = $this->request->param('id', 0);
        if ((empty($en_able) && $en_able !=0) or !in_array($en_able, [0,1])){
            $this->error('数据错误！');
        }
        $row = $this->modelMerchantBinding->get($id);
        if (!$row or $row['merchant_user_id'] != $this->user['id']){
            $this->error('数据错误！');
        }
        $row->en_able = $en_able;
        $row->save();
        $this->success('操作成功');
    }

    /**
     * 禁用绑定用户
     */
    public function disableBindingUser()
    {
    }

    /**
         * 启用绑定用户
        */
    public function enableBindingUser()
    {
    }

    /**
     * 添加渠道账号
     */
    public function addAccount(){
        $bind_id = $this->request->param('bind_id');
        $row = $this->modelMerchantBinding->get($bind_id);
        if (!$row or $row['merchant_user_id'] != $this->user['id'] ){
             $this->error('数据错误');
        }

        $channel = $this->modelPayChannel->where('pay_center_uid', $row['channel_user_id'])->find();

        if (!$channel) {
            $this->error('渠道用户暂未添加渠道');
        }

         $row['channel_id'] = $channel['id'];

         $row['channel_name'] = $channel['name'];
        $this->request->isPost() && $this->result($this->logicMerchantsBinding->saveAccount($this->request->post()));

        $this->assign('row', $row);
        return $this->fetch();
    }
}
