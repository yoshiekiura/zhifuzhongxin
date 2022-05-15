<?php


namespace app\admin\controller;


use app\common\library\enum\CodeEnum;

class Paycenteruser extends BaseAdmin
{

    public function index()
    {
        if ($this->request->isAjax()){
            $where = [];
            //组合搜索
            !empty($this->request->param('username')) && $where['username']
                = ['like', '%'.$this->request->param('username').'%'];

           $data = $this->logicPayusercenter->getUserList($where,true, 'create_time desc',false);
            $this->result($data || !empty($data) ?
                [
                    'code' => CodeEnum::SUCCESS,
                    'msg'=> '',
                    'count'=>10,
                    'data'=>$data
                ] : [
                    'code' => CodeEnum::ERROR,
                    'msg'=> '暂无数据',
                    'count'=>10,
                    'data'=>$data
                ]);
        }
        return $this->fetch();
    }

}