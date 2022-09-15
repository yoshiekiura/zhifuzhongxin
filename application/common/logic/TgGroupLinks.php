<?php


namespace app\common\logic;


use app\common\library\enum\CodeEnum;
use think\Db;
use think\Exception;

class TgGroupLinks extends BaseLogic
{

    public function getLinksList($where = [], $join = null , $field = true, $order = 'create_time desc', $paginate = 15)
    {

        $this->modelTgGroupLinks->alias('a');

        if (!is_null($join)){
            $this->modelTgGroupLinks->join = $join;
        }

        $this->modelTgGroupLinks->limit = !$paginate;
        return $this->modelTgGroupLinks->getList($where, $field, $order, $paginate);

    }

    /**
     * 保存or修改链接
     * @param $data
     * @return array
     */
    public function saveLink($data)
    {
        $validate = $this->validateTgGroupLinks->scene($data['scene'])->check($data);
        if (false === $validate){
            return ['code'=>CodeEnum::ERROR, 'msg' => $this->validateTgGroupLinks->getError()];
        }
        Db::startTrans();
        try {
            $this->modelTgGroupLinks->allowField(true)->isUpdate($data['scene'] == 'add' ? false : true)->save($data);
            $msg = $data['scene'] == 'add' ? '添加' : '修改';
            Db::commit();
            return ['code' => CodeEnum::SUCCESS, 'msg' => $msg . '成功'];
        }catch (Exception $ex){
            Db::rollback();
            return ['code' => CodeEnum::ERROR, 'msg' => $ex->getMessage()];
        }
    }
}