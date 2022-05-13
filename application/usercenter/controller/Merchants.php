<?php


namespace app\usercenter\controller;


use app\common\logic\User;
use think\exception\Handle;

class Merchants extends Base
{
    public function list_merchant()
    {
        $map['pay_center_uid'] = $this->user['id'];
        $map['status'] = 1;
        !empty($this->request->get('name')) && $map['name']
            = ['like', '%'.$this->request->get('name').'%'];
        $logicUser = new User();

        $userList = $logicUser->getChannelsList($map,true, 'id desc');
        $this->assign('list',$userList );
        return $this->fetch('list_merchant');

    /**
     * 添加商户
     *
     */
    public function add_merchant()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $logicUser = new User();
            $params['pay_center_uid'] = $this->user['id'];
            $ret =$logicChannel->saveUser($params);
            if ($ret['code'] == 0){
                $this->error($ret['msg']);
            }
            $this->success($ret['msg']);
        }
        return $this->fetch('add_merchant');
    }

    /**
     * 编辑
     */
    public function edit_merchant()
    {
        $row = $this->modelUser->where(['pay_center_uid' => $this->user['id'], 'id' => $this->request->param('id')])->find();
        if (!$row){
            $this->error('数据错误');
        }
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $logicUser = new User();
            $ret =$logicUser->editUser($params);
            if ($ret['code'] == 0){
                $this->error($ret['msg']);
            }
            $this->success($ret['msg']);
        }
        return $this->fetch('edit_merchant');
    }

    /**
     * 删除 把状态改成0 后面看在
     */
    public function del()
    {
        $where = ['pay_center_uid' => $this->user['id'], 'id' => $this->request->param('id'), 'status' => 1];
        $row = $this->modelUser->where($where)->find($where);
        if (!$row){
            $this->error('数据错误');
        }
        $row->status = 0;
        $row->save();
        $this->success('删除成功');
    }
}
