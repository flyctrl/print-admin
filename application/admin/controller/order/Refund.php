<?php

namespace app\admin\controller\order;

use app\api\controller\PayController;
use app\common\controller\Backend;

/**
 * 订单列管理
 *
 * @icon fa fa-circle-o
 */
class Refund extends Backend
{

    /**
     * Refund模型对象
     * @var \app\admin\model\order\Refund
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\order\Refund;
        $this->view->assign("payTypeList", $this->model->getPayTypeList());
        $this->view->assign("payStatusList", $this->model->getPayStatusList());
        $this->view->assign("invoiceList", $this->model->getInvoiceList());
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
                ->where('pay_status','>','3')
                // ->where('pay_type','neq',0)
                    ->paginate($limit);

            foreach ($list as $row) {
                
                
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }



    public function refund($ids=null)
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
        $pay = new PayController();
        $order =$this->model->get($ids);
        $refund['out_trade_no'] = $order['order_num'];
        $refund['out_refund_no'] = date('YmdHis') . rand(1000, 9999);
        $refund['refund']=intval($params['refund_price'] *100);
        $refund['total']=intval($order['total_price'] *100);
       
        $res = $pay->refund($refund);
        if ($res['status'] == 'PROCESSING' || $res['status'] == 'SUCCESS') {
            $this->model->update(['pay_status' => 6], ['order_num' => $order['order_num']]);

        } else {
            $this->error($res['message']);
        }
        $this->success('操作成功');
    }

}
