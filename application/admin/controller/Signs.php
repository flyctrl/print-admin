<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 签约管理
 *
 * @icon fa fa-circle-o
 */
class Signs extends Backend
{

    /**
     * Signs模型对象
     * @var \app\admin\model\Signs
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Signs;
        $this->view->assign("statusList", $this->model->getStatusList());
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                    ->with(['user'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                
                
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    public function pass()
    {

        $id = $this->request->param('ids');
        $row= $this->model->get($id);
        $row->status = 1;
        $user= \app\admin\model\User::get($row->user_id);
        $user->is_document=1;
        $user->save();
        if($row->save()){
            $this->success('审核成功');
        }else{
            $this->error('审核失败');
        }

    }

    public function reject()
    {

        $id = $this->request->param('ids');
        $row= $this->model->get($id);
        $row->status = 2;
        if($row->save()){
            $this->success('驳回成功');
        }else{
            $this->error('驳回失败');
        }
    }

}
