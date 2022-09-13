<?php


namespace app\common\logic;


use app\common\library\enum\CodeEnum;
use app\common\library\QrcodeLib;
use think\App;
use think\Db;
use think\Exception;
use think\Request;

class Payusercenter extends BaseLogic
{
    /**
     * 获取用户列表
     * @param array $where
     * @return false|\PDOStatement|string|\think\Collection|\think\Paginator|void
     */
    public function getUserList($where = [], $field = true, $order = 'id desc', $paginate = 10)
    {
        $this->modelPayCenterUser->alias('a');
        $this->modelPayCenterUser->limit = !$paginate;
        $paginate = Request::instance()->get('limit', 10);
        return $this->modelPayCenterUser->where($where)->field($field)->order($order)->paginate($paginate);
//         $this->modelPayCenterUser->getList($where, $field, $order, $paginate);
    }

    /**
     * 保存用户
     * @param $data
     * @return array
     */
    public function saveUser($data)
    {
        $validate = $this->validatePaycenteruser->scene($data['scene'])->check($data);

        if ($validate == false) {
            return ['code' => CodeEnum::ERROR, 'msg' => $this->validatePaycenteruser->getError()];
        }
        if ($data['scene'] == 'add') {
            $data['password'] = pwdMd52($data['password']);
            $data['create_time'] = time();
        }

        $ret = $this->modelPayCenterUser->allowField(true)->isUpdate($data['scene'] == 'add' ? false : true)->save($data);
        $msg = $data['scene'] == 'add' ? '添加' : '修改';
        if (!$ret) {
            return ['code' => CodeEnum::SUCCESS, 'msg' => $msg . '失败'];
        }
        return ['code' => CodeEnum::SUCCESS, 'msg' => $msg . '成功'];
    }


    /**
     * 获取支付中心用户信息详情
     *
     */
    public function getUserInfo($where = [], $field = true)
    {
        return $this->modelPayCenterUser->getInfo($where, $field);
    }


    /**
     * @param $uid  用户中心ID
     * @param int $type 资金操作类型对应数据库中jl_class
     * @param int $add_subtract 添加或者减少
     * @param float $money 操作金额
     * @param string $message 资金流水备注
     * @return bool
     */
    function moneyChange($uid, $type = 1, $add_subtract = 1, $money = 0.00, $message = '')
    {
        $user = $this->modelPayCenterUser->where(['id' => $uid])->find();

        if ($user) {
            $moneys = ($add_subtract == 1) ? $money : 0 - $money;

            $updateBalanceRes = $this->modelPayCenterUser->where(['id' => $uid])->setInc('money', $moneys);

            if ($updateBalanceRes) {
                //记录流水
                $insert['uid'] = $uid;
                $insert['jl_class'] = $type;
                $insert['info'] = $message;
                $insert['jc_class'] = ($add_subtract) ? "+" : "-";
                $insert['change_amount'] = $money;
                $insert['pre_amount'] = $user['money'];
                $insert['last_amount'] = $user['money'] + $moneys;
                $insert['change_amount'] = $moneys;
                $insert['create_time'] = time();
                if ($this->modelPayCenterBill->insert($insert)) {
                    return true;
                }
                return false;
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * @param $uid  用户中心ID
     * @param int $type 资金操作类型对应数据库中jl_class
     * @param int $add_subtract 添加或者减少
     * @param float $money 操作金额
     * @param string $message 资金流水备注
     * @return bool
     */
    function usdtChange($uid, $type = 1, $add_subtract = 1, $money = 0.00, $message = '')
    {
        $user = $this->modelPayCenterUser->where(['id' => $uid])->find();

        if ($user) {
            $moneys = ($add_subtract == 1) ? $money : 0 - $money;
            $updateBalanceRes = $this->modelPayCenterUser->where(['id' => $uid])->setInc('usdt_balance', $moneys);

            if ($updateBalanceRes) {
                //记录流水
                $insert['uid'] = $uid;
                $insert['jl_class'] = $type;
                $insert['info'] = $message;
                $insert['jc_class'] = ($add_subtract) ? "+" : "-";
                $insert['change_amount'] = $money;
                $insert['pre_amount'] = $user['usdt_balance'];
                $insert['last_amount'] = $user['usdt_balance'] + $moneys;
                $insert['change_amount'] = $moneys;
                $insert['create_time'] = time();
                if ($this->modelPayCenterUsdtBill->insert($insert)) {
                    return true;
                }
                return false;
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * 充值
     * @param $data
     */
    public function getTopUpAddress($data)
    {
        $validate = $this->validatePaycenteruser->scene('topUp')->check($data);
        if (false === $validate){
            return ['code' => CodeEnum::ERROR, 'msg'=>
                $this->validatePaycenteruser->getError() == '令牌数据无效' ? '令牌数据无效,请刷新页面' : $this->validatePaycenteruser->getError()
            ];
        }

        $centerUser = $this->modelPayCenterUser->where('id',$data['uid'])->find();

        if (empty($centerUser)){
            return ['code' => CodeEnum::ERROR, 'msg'=> '用户错误'];
        }

        $usdt_topup_withdraw_address = $this->modelConfig->where('name',  'usdt_topup_withdraw_address')->value('value');

        if (empty($usdt_topup_withdraw_address)){
            return ['code' => CodeEnum::ERROR, 'msg'=> '后台未设置USDT充值地址，请联系管理员'];
        }


        Db::startTrans();
        try {

            $usdtQr =  generateQRfromGoogle($usdt_topup_withdraw_address);

            $data['usdt_sum'] = $this->getUsdtSum($data['usdt_sum']);
            $topupData = array(
                'trade_no' => create_order_no(),
                'uid'      => $data['uid'],
                'usdt_sum' => $data['usdt_sum'],
                'complete_type' => 0,
            );
            $this->modelUsdtTopupOrders->save($topupData);

            $payData = array(
                'usdt_sum'  => $data['usdt_sum'],
                'payQr'     => $usdtQr
            );
            Db::commit();
            return ['code' => CodeEnum::SUCCESS, 'msg'=>'SUCCESS', 'data' => $payData];
        }catch (Exception $ex) {
            Db::rollback();
            return ['code' => CodeEnum::ERROR, 'msg'=> config('app_debug') ? $ex->getMessage() : '未知错误' ];
        }
    }

    /**
     * 获取usdt数量
     * @param $amount
     * @param $usdtRate
     * @return float|int|string|null
     */
    private function getUsdtSum($usdtSum){
        $execute_sum = 0;

        $UsdtTopupOrders = $this->modelUsdtTopupOrders->where('usdt_sum', $usdtSum)->find();
        if ($UsdtTopupOrders){
            while ($UsdtTopupOrders){
                $usdtSum = $usdtSum + 0.001;
                $UsdtTopupOrders =  Db::query("SELECT * FROM `cm_usdt_topup_orders` WHERE `usdt_sum` = {$usdtSum} LIMIT 1");
                $execute_sum ++;
                if ($execute_sum >= 100){
                    $usdtSum = 0;
                    break;
                }
            }
        }
        return $usdtSum;
    }


    public function applyWithdraw($data)
    {
        $validate = $this->validatePaycenteruser->scene('applyWithdraw')->check($data);
        if (false === $validate){
            return ['code' => CodeEnum::ERROR, 'msg'=>
                $this->validatePaycenteruser->getError() == '令牌数据无效' ? '令牌数据无效,请刷新页面' : $this->validatePaycenteruser->getError()
            ];
        }

        Db::startTrans();
        try {

            $centerUserWh = array(
                'id' =>   $data['uid'],
                'status' => 1
            );
            $centerUser = $this->modelPayCenterUser->lock(true)->where($centerUserWh)->find();

            if (empty($centerUser)){
                return ['code' => CodeEnum::ERROR, 'msg'=> '用户错误'];
            }

            if (bccomp($centerUser->usdt_balance, $data['usdt_sum'], 5)  == '-1'){
                return ['code' => CodeEnum::ERROR, 'msg'=> '余额小于提现数量'];
            }

            $this->modelPayCenterUser->where($centerUserWh)->setInc('usdt_disable_balance', $data['usdt_sum']);

            $withdrawData = array(
                'trade_no'  => create_order_no(),
                'uid'       => $data['uid'],
                'usdt_sum'  => $data['usdt_sum'],
                'withdraw_usdt_address' => $data['withdraw_usdt_address'],
                'status' => 1
            );

            $this->modelWithdrawUsdtOrders->save($withdrawData);

            $this->usdtChange($data['uid'], 2, 0, $data['usdt_sum'], '用户申请提现'. $data['usdt_sum'] .'USDT，订单：'.
                $withdrawData['trade_no']);

            Db::commit();
            return ['code' => CodeEnum::SUCCESS, 'msg'=>'操作成功，等待后台审核。'];
        }catch (Exception $ex) {
            Db::rollback();
            return ['code' => CodeEnum::ERROR, 'msg'=> config('app_debug') ? $ex->getMessage() : '未知错误' ];
        }
    }
}