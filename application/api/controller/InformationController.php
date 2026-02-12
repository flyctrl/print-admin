<?php

namespace app\api\controller;

use app\api\model\CartModel;
use app\api\model\CategoryModel;
use app\api\model\ColourModel;
use app\api\model\FileModel;
use app\api\model\InformationModel;
use app\api\model\PaperModel;

class InformationController extends BaseController
{
    /**
     * 获取购物车数量
     * @return \think\response\Json
     * @throws \think\Exception
     */
    public function cart_num()
    {
        if (!$this->user_id) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $cart_list=CartModel::cart_list($this->user_id);
        $count=count($cart_list);
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $count]);
    }
    
    
    /**
     * 获取一级分类
     * @return \think\response\Json
     */
    public function category_one()
    {
        $list = CategoryModel::category_one();
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);
    }

    /**
     * 获取二级分类
     * @return \think\response\Json
     */
    public function category_two()
    {
        if (!input('?post.parent_id')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $parent_id = $this->request->param('parent_id');
        $list = CategoryModel::category_two($parent_id);
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);
    }

    /**
     * 资料列表
     * @return \think\response\Json
     */
    public function information()
    {
        if (!input('?post.category_one_id') || !input('?post.category_two_id') ) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $keywords = $this->request->param('keywords');
        $category_one_id = $this->request->param('category_one_id');
        $category_two_id = $this->request->param('category_two_id');
        $list = InformationModel::information($category_one_id, $category_two_id, $keywords);
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);
    }

    /**
     * 资料详情
     * @return \think\response\Json
     */
    public function detail()
    {
        if (!input('?post.id') || !$this->user_id) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $id = $this->request->param('id');
        $detail = InformationModel::detail($id,$this->user_id);
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $detail]);
    }

    /**
     * 资料库加入打印列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add_cart()
    {
        if (!input('?post.id') || !$this->user_id) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $id = $this->request->param('id');
        $file = FileModel::where('information_id', $id)->select();
        foreach ($file as  $value) {
            $carts=CartModel::get(['user_id'=>$this->user_id,'file_id'=>$value['id']]);
            if ($carts){
                CartModel::where(['user_id'=>$this->user_id,'file_id'=>$value['id']])->setInc('num');
            }else{
                $colour=ColourModel::get($value['colour_id']);
                $paper=PaperModel::get($value['paper_id']);
                $cart['commission']=$value['commission_switch'];
                $cart['user_id'] = $this->user_id;
                $cart['file_id']=$value['id'];
                $cart['name']=$value['name'];
                $cart['information_id']=$id;
                $cart['paper'] = $paper['name'];
                $cart['colour'] = $colour['name'];
                $cart['file'] = $value['file'];
                $cart['single_double'] = $value['single_double'];
                $cart['binding_type'] = $this->binding_type($value['binding_type']) ;
                $cart['num'] = 1;
                $cart['price']=$value['price'];
                $cart['create_time'] = time();
                CartModel::create($cart);
            }

        }
        return json(['code' => 20000, 'msg' => '加入打印列表成功']);
    }


    public function binding_type($binding_type)
    {
        switch ($binding_type){
            case '0':
                return '不装订';

            case '1':
                return '订书钉';

            case '2':
                return '圈装';

            case '3':
                return '胶装皮纹纸';

            case '4':
                return '胶装铜版纸';

        }
    }





}