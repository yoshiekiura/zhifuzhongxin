<?php


namespace app\admin\controller;


use app\common\library\enum\CodeEnum;
use think\Request;

class TgGroupLinks extends BaseAdmin
{

    public function index()
    {
        return $this->fetch();
    }

    public function getLinksList(Request $request)
    {
        $where = [];

        $trade_no = $request->param('trade_no');
        $status = $request->param('status', -1);

        $trade_no && $where['a.trade_no'] = $trade_no;
        $status >= 0 && $where['a.status'] = $status;

        $data = $this->logicTgGroupLinks->getLinksList($where);

        $this->result(!$data->isEmpty()?
            [
                'code' => CodeEnum::SUCCESS,
                'msg'=> '',
                'count'=>$data->count(),
                'data'=>$data->items()
            ] : [
                'code' => CodeEnum::ERROR,
                'msg'=> '暂无数据',
                'count'=>$data->count(),
                'data'=>$data->items()
            ]);
    }

    public function addLink(Request $request)
    {
        if ($request->isAjax()){
           $this->result( $this->logicTgGroupLinks->saveLink($request->param()));
        }
        return $this->fetch();
    }

    public function editLink(Request $request)
    {
        if ($request->isAjax()) $this->result($this->logicTgGroupLinks->saveLink($request->param()));
        $this->assign('row' , $this->modelTgGroupLinks->find($request->param('id')));
        return $this->fetch();
    }
}