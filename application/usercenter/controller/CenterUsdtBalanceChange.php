<?php


namespace app\usercenter\controller;


class CenterUsdtBalanceChange extends Base
{

    /**
     * 账单列表
     */
    public function index()
    {
         $change_category = $this->request->param('change_category');
         $where['a.uid']  = $this->user['id'];
         $change_category && $where['a.change_category'] = $change_category;
         $field = 'a.*,u.username';
         $lists = $this->logicCenterUsdtBalanceChange->getBalanceChangeList($where, $field);

         $this->assign('lists', $lists);
         return $this->fetch();
    }
}