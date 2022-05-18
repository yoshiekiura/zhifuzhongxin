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
        !empty($this->request->get('username')) && $map['username']
            = ['like', '%' . $this->request->get('username') . '%'];
        $logicUser = new User();

        $userList = $logicUser->getUserList($map, true, 'create_time desc');
//        $banding = 0;
//        foreach ($userList as &$item){
//
//        }
        $this->assign('list', $userList);
        return $this->fetch('list_merchant');
    }
    /**
     * 添加商户
     *
     */
    public function add_merchant()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $params['pay_center_uid'] = $this->user['id'];
            //我不改以前的addUser方法数据写死
            $params['password'] = getRandChar(6);
            $params['account'] = 'usercenter666@qq.com';
            $params['auth_login_ips'] = '127.0.0.1';
            $params['status'] = 1;

            $ret = $this->logicUser->addUser($params);

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
        $row = $this->modelUser->where(['pay_center_uid' => $this->user['id'], 'uid' => $this->request->param('uid')])->find();
        if (!$row){
            $this->error('数据错误');
        }
        if ($this->request->isPost()) {
            $username = $this->request->param('username');
            if (empty($username)){
                $this->error('用户名不能为空');
            }
            $data['username'] = $username;
            $data['uid'] = $this->request->param('uid');
            $ret = $this->modelUser->allowField(true)->isUpdate(true)->save($data);
            if (!$ret){
                $this->error('更新失败');
            }
            $this->success('更新成功');
        }
        $this->assign('row', $row);
        return $this->fetch('edit_merchant');
    }

    /**
     * 删除 把状态改成0 后面看在
     */
    public function del_merchant()
    {
        $where = ['pay_center_uid' => $this->user['id'], 'uid' => $this->request->param('uid'), 'status' => 1];
        $row = $this->modelUser->where($where)->find($where);
        if (!$row){
            $this->error('数据错误');
        }
        $row->status = 0;
        $row->save();
        $this->success('删除成功');
    }
}
