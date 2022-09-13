<?php


namespace app\usercenter\controller;


use think\Request;

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

    /**
     * 个人中心
     */
    public function info()
    {
        if ($this->request->isPost()){
            $params = $this->request->param();
            $params['id'] = $this->user['id'];
            $this->modelPayCenterUser->allowField(['is_info_public'])->isUpdate(true)->save($params);
            $this->success('操作成功');
        }
        return $this->fetch('pay_center_user/info');
    }

    /**
     * 商户用户头像图片
     */
    /*public function uploadAvatar()
    {
        if ($this->request->isPost()) {
            $this->result($this->logicFile->fileUpload('file', 'avatar'));
        }
    }*/

    /**
     * 获取充值地址
     */
    public function getTopUpAddress(Request $request)
    {
        $data = array_merge(['uid' => $this->user['id']], $request->param());
        $this->result($this->logicPayusercenter->getTopUpAddress($data));
    }

    /**
     * 充值列表
     * @param Request $request
     * @return mixed
     */
    public function topupList(Request $request)
    {
        $usdt_sum =  $request->param('usdt_sum');
        $usdt_sum && $where['a.usdt_sum'] = $usdt_sum;
        $where['a.uid'] = $this->user['id'];
        $lists = $this->logicUsdtTopupOrders->getTopupList($where);
        $this->assign('lists', $lists);
        return $this->fetch('pay_center_user/topup_list');
    }

    /**
     * 申请提现
     * @param Request $request
     */
    public function applyWithdraw (Request $request){
        $data = array_merge(['uid' => $this->user['id']], $request->param());
        $this->result($this->logicPayusercenter->applyWithdraw($data));
    }

    public function withdrawList(Request $request)
    {
        $usdt_sum =  $request->param('usdt_sum');
        $usdt_sum && $where['a.usdt_sum'] = $usdt_sum;
        $where['a.uid'] = $this->user['id'];
        $lists = $this->logicWithdrawUsdtOrders->getWithdrawList($where);

        $this->assign('lists', $lists);
        return $this->fetch('pay_center_user/withdraw_list');
    }
}