<?php


namespace app\usercenter\controller;


class Help extends Base
{

    public function index()
    {
        $where['status'] = 1;
        !empty($this->request->param('title')) && $where['title'] =
            ['like', "%". $this->request->param('title') ."%"];
        $article_list = $this->modelArticle->where($where)->paginate($this->request->param('limit'));
        $this->assign('list', $article_list);
        return $this->fetch();
    }

    public function articleDetail()
    {
        $article =  $this->modelArticle->get($this->request->param('id'));
        if (!$article){
            $this->error('文档不存在');
        }

        $this->assign('article',  $article);

        return $this->fetch();

    }
}