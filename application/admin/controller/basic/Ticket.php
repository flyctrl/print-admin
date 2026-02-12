<?php

namespace app\admin\controller\basic;

use app\api\model\UserTicketModel;
use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 优惠券
 *
 * @icon fa fa-ticket
 */
class Ticket extends Backend
{

    /**
     * Ticket模型对象
     * @var \app\admin\model\basic\Ticket
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\basic\Ticket;
        $this->view->assign("typeList", $this->model->getTypeList());

    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    /**
     * 添加
     *
     * @return string
     * @throws \think\Exception
     */
    public function add()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }
//            $user_ids=explode(',',$params['user_ids']);
//
//            foreach ($user_ids as $user_id){
//                $user_ticket['ticket_name']=$params['ticket_name'];
//                $user_ticket['limitation_price']=$params['limitation_price'];
//                $user_ticket['deduction_price']=$params['deduction_price'];
//                $user_ticket['star_time']=time();
//                $user_ticket['end_time']=strtotime($params['end_time']);
//                $user_ticket['status']=0;
//                $user_ticket['user_id']=$user_id;
//                UserTicketModel::create($user_ticket);
//            }
            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }


}
