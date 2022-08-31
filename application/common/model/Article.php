<?php

/**
 *  +----------------------------------------------------------------------
 *  | 中通支付系统 [ WE CAN DO IT JUST THINK ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2018 http://www.iredcap.cn All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed ( https://www.apache.org/licenses/LICENSE-2.0 )
 *  +----------------------------------------------------------------------
 *  | Author: Brian Waring <BrianWaring98@gmail.com>
 *  +----------------------------------------------------------------------
 */
namespace app\common\model;


class Article extends BaseModel
{
    public $categoryMap = array(
        1 => ['id' => 1, 'name' => 'category1'],
        2 => ['id' => 2, 'name' => 'category2'],
        3 => ['id' => 3, 'name' => 'category3'],
    );


    protected $append = [
        'categoryText'
    ];

    public function getcategoryTextAttr($value,$data)
    {
            return $this->categoryMap[$data['category_id']]['name'];
    }
}