<?php


namespace app\usercenter\controller;


class Paycenteruser extends Base
{

    /**
     * 用户列表
     */
    public function index()
    {
        if (!$this->isAgent()){
            $this->error('无权限访问！');
        }
        !empty($this->request->get('username')) && $map['username']
            = ['LIKE','%'.$this->request->get('username').'%'];
        $map['pid'] = $this->user['id'];
        $lists = $this->logicPayusercenter->getUserList($map);
        $this->assign('list', $lists);
        return $this->fetch('pay_center_user/index');
    }

    /**
     * 添加用户
     */
    public function addUser(){
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $params['pid'] = $this->user['id'];
            $ret =$this->logicPayusercenter->saveUser($params);
            if ($ret['code'] == 0){
                $this->error($ret['msg']);
            }
            $this->success($ret['msg']);
        }
        return $this->fetch('pay_center_user/add_user');
    }
}