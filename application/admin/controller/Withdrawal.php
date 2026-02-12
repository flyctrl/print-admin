<?php

namespace app\admin\controller;

use app\api\controller\PayController;
use app\api\controller\WechatController;
use app\api\model\UserModel;
use app\api\model\UserRechargeModel;
use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use Yansongda\Pay\Pay;

/**
 * 用户提现记录
 *
 * @icon fa fa-circle-o
 */
class Withdrawal extends Backend
{

    /**
     * Withdrawal模型对象
     * @var \app\admin\model\Withdrawal
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Withdrawal;
        $this->view->assign("statusList", $this->model->getStatusList());
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */



    public function confirm()
    {
        $ids = $this->request->param('ids');

        $row = $this->model->get($ids);
        $user=UserModel::get($row->user_id);
        $pay = new PayController();
        $config = $pay->processConfig();
        Pay::config($config);
        $order = [
            'appid'=>config('wxinfo.appId'),
            'out_bill_no' => $row->order_num,
//            'out_batch_no' =>date('YmdHis').rand(1000,9999),
            'transfer_scene_id' => '1005',
            'openid' =>  $user['openid'],
            // 'user_name' => '闫嵩达'  // 明文传参即可，sdk 会自动加密
            'transfer_amount' => (int)($row->price *100),
            'transfer_remark' => 'test',
            'transfer_scene_report_infos' => [
                ['info_type' => '岗位类型', 'info_content' => '评论员'],
                ['info_type' => '报酬说明', 'info_content' => '用户评论审核通过奖励'],
            ],

        ];

        $result = WechatController::transfer($order);
        if ($result['state'] == 'WAIT_USER_CONFIRM') {
            $row->save(['status' => 4,'package_info'=>$result['package_info']]);
            $this->success('提现成功,微信处理中');
        } else {
            $this->error($result['message']);
        }
    }



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
            if ($params['status']==3){
                UserModel::where('id',$row->user_id)->setInc('balance',$row->price);
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
