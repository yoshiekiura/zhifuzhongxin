<?php


namespace app\admin\controller;


use app\common\library\enum\CodeEnum;
use think\Exception;
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


    public function usdtDetails(Request $request)
    {
        $uid = $request->param('uid');

        $this->logicPayCenterUsdtBill->getBillList();
    }

    /**
     * 用户资金
     * @return mixed
     */
    public function centerBalance()
    {
        $this->assign('balance', $this->logicCenterBalance->getBalaceStat());
        return $this->fetch();
    }

    /**
     * 获取用户资金列表
     * @param Request $request
     */
    public function getCenterBalanceList(Request $request)
    {
        $where = [];

        //组合搜索
        !empty($this->request->param('uid')) && $where['a.uid']
            = ['eq', $this->request->param('uid')];

        !empty($this->request->param('username')) && $where['a.username']
            = ['like', '%' . $this->request->param('username') . '%'];

        $sort = 'id desc';
        //排序
        if (!empty($this->request->param('enable'))) {
            if ($this->request->param('enable') == '1') {
                $sort = 'usdt_enable asc';
            } elseif ($this->request->param('enable') == '2') {
                $sort = 'usdt_enable desc';
            } elseif ($this->request->param('enable') == '3') {
                $sort = 'pl_enable asc';
            } elseif ($this->request->param('enable') == '4') {
                $sort = 'pl_enable desc';
            }
        }
        $field = 'a.*, u.username';
        $data = $this->logicCenterBalance->getBalanceList($where, $field, $sort, input('limit'));

        $this->result(!$data->isEmpty() ?
            [
                'code' => CodeEnum::SUCCESS,
                'msg' => '',
                'count' => $data->count(),
                'data' => $data->items()
            ] : [
                'code' => CodeEnum::ERROR,
                'msg' => '暂无数据',
                'count' => $data->count(),
                'data' => $data->items()
            ]
        );
    }

    /**
     * 后台修改余额
     * @param Request $request
     * @return mixed
     */
    public function changeUsdtBalance(Request $request)
    {
        if ($this->request->isPost()) {
            if (session('__token__') != $this->request->param('__token__')) {
                $this->result(CodeEnum::ERROR, '请刷新页面重试');
            }
            $uid = $this->request->param('uid/d');
            $type = $this->request->param('type');
            $add_subtract = $this->request->param('change_type');
            $amount = $this->request->param('change_amount');
            $remarks = htmlspecialchars($this->request->param('info/s'));
            try {
                $ret = $this->logicPayusercenter->usdtChangeV2($uid, $type, $add_subtract, 3, $amount, $remarks, 1);
            }catch (Exception $ex){
                $this->result(0, $ex->getMessage());
            }

            session('__token__', null);
            $code = $ret ? CodeEnum::SUCCESS : CodeEnum::ERROR;
            $msg = $ret ? "操作成功" : "操作失败";
            $this->result($code, $msg);
        }

        return $this->fetch();
    }

    /**
     * 用户自己流水列表
     * @param Request $request
     * @return mixed
     */
    public function usdtBalanceDetails(Request $request)
    {
        $uid = $request->param('id');
        if ($request->isAjax()){
            $where = [];
            //组合搜索
            $where['a.uid'] = ['eq', $this->request->param('uid')];

            $remarks = $this->request->param('remarks');
            $remarks && $where['a.remarks'] = ['like', "%" . $remarks . "%"];

            $type = $request->param('type');
            $type && $where['a.type'] =  $type;
            //时间搜索  时间戳搜素
            $where['a.create_time'] = $this->parseRequestDate3();

            $isPlarOp = $this->request->param('is_plat_op', -1);
            ($isPlarOp != -1) && $where['a.is_plat_op'] = $isPlarOp;
            $field = 'a.*,u.username';
            $data = $this->logicCenterUsdtBalanceChange->getBalanceChangeList($where, $field);

            $this->result(!$data->isEmpty() ?
                [
                    'code' => CodeEnum::SUCCESS,
                    'msg' => '',
                    'count' => $data->count(),
                    'data' => $data->items()
                ] : [
                    'code' => CodeEnum::ERROR,
                    'msg' => '暂无数据',
                    'count' => $data->count(),
                    'data' => $data->items()
                ]
            );
        }
        $where['create_time'] = $this->parseRequestDate3();
        $where['uid'] = $uid;
        $data = $this->logicCenterUsdtBalanceChange->getBalanceChangeInfo($where);
        $this->assign('uid', $uid);
        $this->assign('data', $data);
        return $this->fetch();
    }

    /**
     * 账变统计
     */
    public function searchBalanceCal()
    {
        $where = [];
        //组合搜索
        $where['uid'] = ['eq', $this->request->param('uid')];
        $remarks = $this->request->param('remarks');
        $remarks && $where['remarks'] = ['like', "%" . $remarks . "%"];
        //时间搜索  时间戳搜素
        $where['create_time'] = $this->parseRequestDate3();

        $isPlarOp = $this->request->param('is_plat_op', -1);
        ($isPlarOp != -1) && $where['is_plat_op'] = $isPlarOp;

        $data = $this->logicCenterUsdtBalanceChange->getBalanceChangeInfo($where);

        exit(json_encode($data));

    }
}