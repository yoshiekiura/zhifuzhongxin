<?php


namespace app\admin\controller;


use app\common\library\enum\CodeEnum;
use think\Request;

class Paycenteruser extends BaseAdmin
{

    public function index()
    {
        if ($this->request->isAjax()){
            $where = [];
            //组合搜索
            !empty($this->request->param('username')) && $where['username']
                = ['like', '%'.$this->request->param('username').'%'];
            !empty($this->request->param('user_type')) && $where['user_type']
                = $this->request->param('user_type');
           $data = $this->logicPayusercenter->getUserList($where,true, 'create_time desc',false);
           foreach ($data as &$item){
               $item['pid_username'] =  $this->modelPaycenteruser->where('id', '=', $item['pid'])->value('username');
           }
            $this->result($data || !empty($data) ?
                [
                    'code' => CodeEnum::SUCCESS,
                    'msg'=> '',
                    'count'=>$data->total(),
                    'data'=>$data->items()
                ] : [
                    'code' => CodeEnum::ERROR,
                    'msg'=> '暂无数据',
                    'count'=>10,
                    'data'=>$data
                ]);
        }
        return $this->fetch();
    }

    /**
     * 添加用户
     */
    public function addUser()
    {
        if ($this->request->isPost()){
            $params = $this->request->param();
            $params['admin_id'] = is_admin_login();
            $ret =  $this->logicPayusercenter->saveUser($params);
            if ($ret['code'] == 0 ){
                $this->error($ret['msg']);
            }
            $this->success($ret['msg']);
        }
        return $this->fetch('add_user');
    }

    /**
     * 编辑用户
     */
    public function editUser()
    {
        $id = $this->request->get('id');
        $row = $this->modelPayCenterUser->get($id);
        if ($this->request->isPost()){
            $params = $this->request->param();
            if (isset($params['password']) && !empty($params['password'])){
                $params['password'] = pwdMd52($params['password']);
            }else{
                unset($params['password']);
            }
            $ret = $this->logicPayusercenter->saveUser($params);
            if ($ret['code'] == 0 ){
                $this->error($ret['msg']);
            }
            $this->success($ret['msg']);
        }
        $this->assign('row', $row);
        return $this->fetch('edit_user');
    }

    /**
     * 删除用户
     */
    public function delUser()
    {
        $id = $this->request->get('id');
        $row = $this->modelPayCenterUser->get($id);
        action_log('删除', '删除支付中心用户' . $row['id']);
        $row->status = 0;
        $row->save();
        $this->success('操作成功');
    }

    public function changeUsdtBalance(Request $request)
    {

        if ($this->request->isPost()) {
            if (session('__token__') != $this->request->param('__token__')) {
                $this->result(CodeEnum::ERROR, '请刷新页面重试');
            }
            $uid = $this->request->param('uid/d');
            $setDec = $this->request->param('change_type');
            $amount = $this->request->param('amount');
            $field = $this->request->param('change_money_type');
            $remarks = htmlspecialchars($this->request->param('remarks/s'));


            //判断 如果操作的是增加 并且 是余额  这里要给渠道增加 余额 并且计算费率

            $channel_id = $this->request->param('channel_id');
            $account_id = $this->request->param('account_id');
            if ($is_open_channel_fund && $field == 'enable' && $setDec == '0' && $channel_id && $account_id) {
                // 计算费率
                //获取用户分成
                $profit = $this->logicUser->getUserProfitInfo(['uid' => $uid, 'cnl_id' => $account_id]);

                $account = $this->logicPay->getAccountInfo(['id' => $account_id]);

                if (empty($profit)) $profit = $account;

                //渠道分成
                $channel_amount = bcmul($amount, $account['rate'], 3);
                //用户分成
                $amount = bcmul($amount, $profit['urate'], 3);
                //渠道
                $this->logicPayChannelChange->creatPayChannelChange($channel_id, $channel_amount, $remarks, false, 1);
            }


            $ret = $this->logicBalanceChange->creatBalanceChange($uid, $amount, $remarks, $field, $setDec, 1);

            /**  2020-2-20 update  **/
            //如果操作的是增加冻结金额
            if ($field == 'disable') {
                //增加对应余额
                if (!$setDec) {
                    $result = $this->logicBalanceChange->creatBalanceChange($uid, $amount, $remarks, 'enable', !$setDec, 1);
                    if (!$result) {
                        return false;
                    }
                }

            }
            session('__token__', null);
            $code = $ret ? CodeEnum::SUCCESS : CodeEnum::ERROR;
            $msg = $ret ? "操作成功" : "操作失败";
            $this->result($code, $msg);
        }

        return $this->fetch();
    }
}