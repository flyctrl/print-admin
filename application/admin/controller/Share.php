<?php

namespace app\admin\controller;

use app\api\model\SystemModel;
use app\api\model\UserBalanceRecordModel;
use app\api\model\UserModel;
use app\common\controller\Backend;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 分享
 *
 * @icon fa fa-share
 */
class Share extends Backend
{

    /**
     * Share模型对象
     * @var \app\admin\model\Share
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Share;
        $this->view->assign("statusList", $this->model->getStatusList());
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */



    /**
     * 编辑
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            if ($row['status'] != 0){
                    $this->error('当前状态不是审核中，不可操作');
            }
            if ($params['status'] == 1){
                $system=SystemModel::get(1);
                UserModel::where('id',$row['user_id'])->setInc('balance',$system['share_proportion']);
                $user=UserModel::get($row['user_id']);
                $user_balance['user_id'] = $row['user_id'];
                $user_balance['balance'] =$system['share_proportion'];
                $user_balance['source_id'] = $row['user_id'];
                $user_balance['source_name'] = $user['nickname'];
                $user_balance['avatar_image'] = $user['avatar_image'];
                $user_balance['remark'] = '小红书分享奖励';
                $user_balance['type'] = '1';
                $user_balance['create_time'] = time();
                UserBalanceRecordModel::create($user_balance);
            }
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

}
