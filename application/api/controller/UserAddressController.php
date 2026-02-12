<?php
/**
 * Created by PhpStorm.
 * User: win10-06-28
 * Date: 2021/5/6
 * Time: 9:56
 */


namespace app\api\controller;

use app\api\model\UserAddressModel;
use think\Db;



/**
 * 用户地址控制器
 * Class AddressController
 * @package app\api\controller
 */
class UserAddressController extends BaseController
{





    /**
     * 地址列表
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function address_list()
    {
        if (!$this->user_id || !input('?post.page')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $page = $this->request->param('page');
        $list = UserAddressModel::address_list($this->user_id, $page);
        return json(['code' => 20000, 'msg' => '获取地址成功', 'data' => $list]);

    }

    /**
     * 获取用户地址详情
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_address()
    {
        if (!input('?post.id')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $id = $this->request->param('id');
        $data = UserAddressModel::detail($id);
        if (!$data) {
            return json(['code' => 40000, 'msg' => '地址不不存在']);
        }
        return json(['code' => 20000, 'msg' => '获取地址成功', 'data' => $data]);
    }

    /**
     * 用户地址添加与修改
     * @return \think\response\Json
     */
    public function set_address()
    {
        if (!input('?post.name')
            || !input('?post.phone')
            || !input('?post.province')
            || !input('?post.city')
            || !input('?post.area')
            || !input('?post.address')
            || !input('?post.default')
            || !$this->user_id
        ) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        Db::startTrans();
        try {
            $id = $this->request->param('id');
            $data['name'] = $this->request->param('name');
            $data['phone'] = $this->request->param('phone');
            $data['province'] = $this->request->param('province');
            $data['city'] = $this->request->param('city');
            $data['area'] = $this->request->param('area');
            $data['address'] = $this->request->param('address');
            $data['default'] = $this->request->param('default');
            $data['user_id'] = $this->user_id;
            if ($data['default'] == '1') {
                UserAddressModel::update(['default' => '0'], ['user_id' => $this->user_id]);
            }
            if ($id) {
                $address = UserAddressModel::get($id);
                if (!$address) {
                    return json(['code' => 40000, 'msg' => '地址不不存在']);
                }
                UserAddressModel::update($data, ['id' => $id]);
                Db::commit();
                return json(['code' => 20000, 'msg' => '修改地址成功']);
            } else {
                $data['create_time'] = time();
                UserAddressModel::create($data);
                Db::commit();
                return json(['code' => 20000, 'msg' => '添加地址成功']);
            }

        } catch (\Exception $exception) {
            Db::rollback();
            return json(['code' => 40000, 'msg' => $exception->getMessage()]);
        }
    }

    /**
     * 删除用户地址
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function del()
    {
        if (!input('?post.id') || !$this->user_id) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $id = $this->request->param('id');
        $address = UserAddressModel::get(['id' => $id, 'user_id' => $this->user_id]);
        if (!$address) {
            return json(['code' => 40000, 'msg' => '地址不不存在']);
        }
        $res = UserAddressModel::destroy(['id' => $id, 'user_id' => $this->user_id]);
        if ($res) {
            return json(['code' => 20000, 'msg' => '删除地址成功']);
        } else {
            return json(['code' => 40000, 'msg' => '删除地址成功']);
        }
    }
}