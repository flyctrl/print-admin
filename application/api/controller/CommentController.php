<?php

namespace app\api\controller;

use app\api\model\CommentModel;

class CommentController extends BaseController
{
    /**
     * 获取评论列表
     * @return \think\response\Json
     */
    public function comment_list()
    {
        if (!input('?post.page')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $page=$this->request->param('page');
        $list=CommentModel::comment_list($page);
        return json(['code' => 20000, 'msg' => '获取地址成功', 'data' => $list]);
    }
}