<?php


namespace app\common\logic;


use app\common\library\enum\CodeEnum;
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
}