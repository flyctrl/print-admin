<?php

namespace app\api\controller;

use app\api\model\BrandModel;
use app\api\model\CartModel;
use app\api\model\ColorModel;
use app\api\model\ColourModel;
use app\api\model\PaperModel;
use app\api\model\PriceModel;
use app\api\model\PrintsModel;
use app\api\model\ReduceModel;
use app\api\model\ShareDetailModel;
use fast\Random;
use PhpOffice\PhpWord\IOFactory;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\PdfParserException;

class CartController extends BaseController
{
    /**
     * 打印列表
     * @return \think\response\Json
     */
    public function cart_list()
    {
        if (!$this->user_id) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $list = CartModel::cart_list($this->user_id);
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);
    }

    /**
     * 删除打印列表
     * @return \think\response\Json
     */
    public function del()
    {
        if (!input('?post.ids')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $ids = $this->request->param('ids');
        CartModel::where('id', 'in', $ids)->delete();
        return json(['code' => 20000, 'msg' => '删除成功']);
    }


    public function cart_add1()
    {
        if (!input('?post.file') || !$this->user_id || !input('?post.name')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }

        $file = $this->request->param('file');

        $pdf = new Fpdi();
        try {
            $pageCount = $pdf->setSourceFile('.' . $file);
            $Landscape = 0;
            $Portrait = 0;
            // 循环遍历每一页获取方向
            for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
                $templateId = $pdf->importPage($pageNumber);
                $size = $pdf->getTemplateSize($templateId);
                // 检查方向
                if ($size['width'] > $size['height']) {
                    $Landscape++;

                } else {
                    $Portrait++;
                }
            }
            if ($Landscape > 0 && $Portrait > 0) {
                $cart['type'] = 2;
                $turning='';
            }
            if ($Landscape == 0) {
                $cart['type'] = 1;
                $turning='纵向翻页';
            }
            if ($Portrait == 0) {
                $cart['type'] = 0;
                $turning='横向翻页';
            }
            $cart['user_id'] = $this->user_id;
            $cart['name'] = $this->request->param('name');
            $cart['file'] = $file;
            $cart['num'] = 1;
            $cart['create_time'] = time();
            $cart['information'] = 0;
            $cart['page'] = $pageCount;
            $cart['source_file'] = $this->request->param('source_file');
            $cart['password'] = 0;
            $res = CartModel::create($cart);
            return json(['code' => 20000, 'msg' => '文档上传成功', 'data' =>$res->id,'page'=>$cart['page'],'turning'=>$turning]);
        } catch (PdfParserException  $exception) {
            $as_file = $file;
            $file = ROOT_PATH . 'public' . $file;
            $str = "pdftk $file dump_data | grep NumberOfPages | cut -d ' ' -f 2";
            $pageCount = exec($str, $output, $return_var);
            if ($return_var == 0) {
                $cart['page'] = $pageCount;
            } else {
                return json(['code' => 40000, 'msg' => '您输入的文档无法解析']);
            }
            $str = "pdftk $file dump_data | grep MediaRotation | cut -d ' ' -f 2";
            $type = exec($str, $output, $return_var);
            if ($return_var == 0) {
                if ($type == 0) {
                    $cart['type'] = 1;
                } else {
                    $cart['type'] = 0;
                }
            }
            $cart['user_id'] = $this->user_id;
            $cart['name'] = $this->request->param('name');
            $cart['file'] = $as_file;
            $cart['num'] = 1;
            $cart['create_time'] = time();
            $cart['information'] = 0;
            $cart['password'] = 1;
            $cart['source_file'] = $this->request->param('source_file');
            $res = CartModel::create($cart);
            return json(['code' => 20000, 'msg' => '文档上传成功', 'data' => $res->id,'page'=>$cart['page']]);

        }
    }


    public function direction()
    {
        if (!input('?post.id') ) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $id = $this->request->param('id');
        $cart=CartModel::get($id);

        $file = ROOT_PATH . 'public' . $cart['file'];
        $str = "pdftk $file dump_data | grep PageMediaDimensions";
        exec($str, $MediaRotation, $return_var);
        if ($return_var==0) {
            $heng = 0;
            $shu = 0;
            $first=0;
            foreach ($MediaRotation as $key => $line) {
                if (strpos($line, "PageMediaDimensions") !== false) {
                    // 提取页面尺寸数据并拆分为数组
                    $dimensions = explode(" ", $line);
                    // 移除 "PageMediaDimensions:" 部分
                    array_shift($dimensions);
                    // 将页面尺寸添加到 $pageSizes 数组中
                    if ($key == 1) {
                        if ($dimensions[0] > str_replace(',', '', $dimensions[1])) {
                            $first = 0;
                        } else {
                            $first = 1;
                        }
                    } else {
                        if ($dimensions[0] > str_replace(',', '', $dimensions[1])) {
                            $heng++;
                        } else {
                            $shu++;
                        }

                    }
                }
            }
            return json(['code'=>20000,'msg'=>'获取数据成功','data'=>['first'=>$first,'heng'=>$heng,'shu'=>$shu]]);
        }else{
            return json(['code'=>20000,'msg'=>'获取数据成功','data'=>['first'=>2,'heng'=>0,'shu'=>0]]);
        }
    }


    public function test()
    {
         if (!input('?post.file') || !$this->user_id || !input('?post.name')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $file = $this->request->param('file');
        $as_file = $file;


        $file = ROOT_PATH . 'public' . $file;
        $str = "pdftk $file dump_data | grep NumberOfPages | cut -d ' ' -f 2";
        $pageCount = exec($str, $output, $return_var);
        if ($return_var == 0) {
            $cart['page'] = $pageCount;
        } else {
            return json(['code' => 40000, 'msg' => '您输入的文档无法解析']);
        }
        $str = "pdftk $file dump_data | grep PageMediaDimensions";
//        $str = "pdftk $file dump_data";
        exec($str, $MediaRotation, $return_var);
        if ($return_var==0){
            $Landscape = 0;
            $Portrait = 0;
            foreach ($MediaRotation as  $line) {
                if (strpos($line, "PageMediaDimensions") !== false) {
                    // 提取页面尺寸数据并拆分为数组
                    $dimensions = explode(" ", $line);
                    // 移除 "PageMediaDimensions:" 部分
                    array_shift($dimensions);
                    // 将页面尺寸添加到 $pageSizes 数组中
                    if ($dimensions[0] >str_replace(',', '', $dimensions[1])){
                        $Portrait++;
                    }else{
                        $Landscape++;

                    }
                }
            }
            if ($Landscape > 0 && $Portrait > 0) {
                $cart['type'] = 2;
                $turning='';
            }else{
                if ($Landscape > 0) {
                    $cart['type'] = 1;
                    $turning='纵向翻页';
                }
                if ($Portrait > 0) {
                    $cart['type'] = 0;
                    $turning='横向翻页';
                }
            }

        }else{
            $cart['type'] = 2;
            $turning='';
        }
        $cart['user_id'] = $this->user_id;
        $cart['name'] = $this->request->param('name');
        $cart['file'] = $as_file;
        $cart['num'] = 1;
        $cart['create_time'] = time();
        $cart['information'] = 0;
//        $cart['password'] = 1;
        $cart['source_file'] = $this->request->param('source_file');
        $res = CartModel::create($cart);
        return json(['code' => 20000, 'msg' => '文档上传成功', 'data' => $res->id,'page'=>$cart['page'],'turning'=>$turning]);

    }


    public function cart_add()
    {
        if (!input('?post.file') || !$this->user_id || !input('?post.name')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $file = $this->request->param('file');
        $as_file = $file;


        $file = ROOT_PATH . 'public' . $file;
        $str = "pdftk $file dump_data | grep NumberOfPages | cut -d ' ' -f 2";
        $pageCount = exec($str, $output, $return_var);
        if ($return_var == 0) {
            $cart['page'] = $pageCount;
        } else {
            return json(['code' => 40000, 'msg' => '您输入的文档无法解析']);
        }
        $str = "pdftk $file dump_data | grep PageMediaDimensions";
//        $str = "pdftk $file dump_data";
        exec($str, $MediaRotation, $return_var);
        if ($return_var==0){
            $Landscape = 0;
            $Portrait = 0;
            foreach ($MediaRotation as  $line) {
                if (strpos($line, "PageMediaDimensions") !== false) {
                    // 提取页面尺寸数据并拆分为数组
                    $dimensions = explode(" ", $line);
                    // 移除 "PageMediaDimensions:" 部分
                    array_shift($dimensions);
                    // 将页面尺寸添加到 $pageSizes 数组中
                    if ($dimensions[0] >str_replace(',', '', $dimensions[1])){
                        $Portrait++;
                    }else{
                        $Landscape++;

                    }
                }
            }
            if ($Landscape > 0 && $Portrait > 0) {
                $cart['type'] = 2;
                $turning='';
            }else{
                if ($Landscape > 0) {
                    $cart['type'] = 1;
                    $turning='纵向翻页';
                }
                if ($Portrait > 0) {
                    $cart['type'] = 0;
                    $turning='横向翻页';
                }
            }

        }else{
            $cart['type'] = 2;
            $turning='';
        }
        $cart['user_id'] = $this->user_id;
        $cart['name'] = $this->request->param('name');
        $cart['file'] = $as_file;
        $cart['num'] = 1;
        $cart['create_time'] = time();
        $cart['information'] = 0;
//        $cart['password'] = 1;
        $cart['source_file'] = $this->request->param('source_file');
        $res = CartModel::create($cart);
        return json(['code' => 20000, 'msg' => '文档上传成功', 'data' => $res->id,'page'=>$cart['page'],'turning'=>$turning]);

    }

    /**
     * 修改打印参数
     * @return \think\response\Json
     */
    public function cart_edit()
    {
        if (!input('?post.id')
            || !input('?post.paper')
            || !input('?post.single_double')
            || !input('?post.colour')
            || !input('?post.binding_type')
            || !input('?post.brand')
            || !input('?post.film_covering')
            || !input('?post.cover_type')
            || !input('?post.reduction_printing')
            || !input('?post.print_range')
            || !input('?post.num')
        ) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }

        $id = $this->request->param('id');
        $cart['paper'] = $this->request->param('paper');
        $cart['single_double'] = $this->request->param('single_double');
        $cart['binding_type'] = $this->request->param('binding_type');
        $cart['brand'] = $this->request->param('brand');
        $cart['page_turning'] = $this->request->param('page_turning');
        $cart['film_covering'] = $this->request->param('film_covering');
        $cart['colour'] = $this->request->param('colour');
        $cart['color'] = $this->request->param('color');
        $cart['cover_type'] = $this->request->param('cover_type');
        $cart['cover_image'] = $this->request->param('cover_image');
        $cart['reduction_printing'] = $this->request->param('reduction_printing');
        $cart['print_range'] = $this->request->param('print_range');
        $cart['num'] = $this->request->param('num');
        $cart['price'] = $this->request->param('price');
        $cart['commission'] = $this->request->param('commission_switch');
        if ($cart['reduction_printing'] == '二合一' && $cart['page_turning'] == '竖向翻页') {
            $cart['page_turning'] = '横向翻页';
        }
        if ($cart['reduction_printing'] == '二合一' && $cart['page_turning'] == '横向翻页') {
            $cart['page_turning'] = '竖向翻页';
        }
        CartModel::update($cart, ['id' => $id]);
        return json(['code' => 20000, 'msg' => '修改打印配置成功']);
    }



    /**
     * 修改打印参数
     * @return \think\response\Json
     */
    public function cart_all_edit()
    {
        if (!input('?post.ids')
            || !input('?post.paper')
            || !input('?post.single_double')
            || !input('?post.colour')
            || !input('?post.binding_type')
            || !input('?post.brand')
            || !input('?post.film_covering')
            || !input('?post.cover_type')
            || !input('?post.reduction_printing')
            || !input('?post.print_range')
            || !input('?post.num')
        ) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }

        $ids = $this->request->param('ids');
        $cart['paper'] = $this->request->param('paper');
        $cart['single_double'] = $this->request->param('single_double');
        $cart['binding_type'] = $this->request->param('binding_type');
        $cart['brand'] = $this->request->param('brand');
        $cart['page_turning'] = $this->request->param('page_turning');
        $cart['film_covering'] = $this->request->param('film_covering');
        $cart['colour'] = $this->request->param('colour');
        $cart['color'] = $this->request->param('color');
        $cart['cover_type'] = $this->request->param('cover_type');
        $cart['cover_image'] = $this->request->param('cover_image');
        $cart['reduction_printing'] = $this->request->param('reduction_printing');
        $cart['print_range'] = $this->request->param('print_range');
        $cart['num'] = $this->request->param('num');
        $cart['price'] = $this->request->param('price');
        $cart['commission'] = $this->request->param('commission_switch');
        if ($cart['reduction_printing'] == '二合一' && $cart['page_turning'] == '竖向翻页') {
            $cart['page_turning'] = '横向翻页';
        }
        if ($cart['reduction_printing'] == '二合一' && $cart['page_turning'] == '横向翻页') {
            $cart['page_turning'] = '竖向翻页';
        }
        CartModel::where('id','in',$ids)->update($cart);
        return json(['code' => 20000, 'msg' => '修改打印配置成功']);
    }

    /**
     * 修改数量
     * @return \think\response\Json
     */
    public function num_edit()
    {
        if (!input('?post.id') || !input('?post.num')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $id = $this->request->param('id');
        $num = $this->request->param('num');
        CartModel::where('id', $id)->update(['num' => $num]);
        return json(['code' => 20000, 'msg' => '修改数量成功']);
    }

    /**
     * 获取纸张纸型
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function paper()
    {
        $list = PaperModel::all();
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);
    }


    /**
     * 获取纸张纸型
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function brand()
    {
        $list = BrandModel::all();
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);
    }

    /**
     * 获取色彩
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function colour()
    {
        $list = ColourModel::all();
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);

    }

    /**
     * 获取颜色
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function color()
    {
        $list = ColorModel::all();
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);
    }

    /**
     * 获取缩印
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function reduce()
    {
        $list = ReduceModel::all();
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);
    }

    /**
     * 获取数据
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function price()
    {
        $price = PriceModel::get(1);
        $price['area_json'] = json_decode($price['area_json'], true);
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $price]);
    }


    public function prints()
    {
        if (!input('?post.brand_id')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $brand_id = $this->request->param('brand_id');
        $list = PrintsModel::where(['brand_id' => $brand_id])->select();
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);
    }


    public function merge()
    {

        if (!input('?post.ids') || !input('?post.name')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $ids = $this->request->param('ids');
        $cart = CartModel::where('id', 'in', $ids)->orderRaw("find_in_set(id,'" . $ids . "')")->select();
        $name = $this->request->param('name');
        $str = 'pdftk ';
        foreach ($cart as $item) {
            $cart_name = ROOT_PATH . 'public' . $item['file'];
            $str .= $cart_name . ' ';
        }

        $filename = ROOT_PATH . 'public/uploads/' . date('Ymd') . '/' . md5($name) . '.pdf';
        $str .= "cat output $filename";
        exec($str, $output, $return_var);
        if ($return_var == 0) {
            $pdf = new Fpdi();
            $carts['name'] = $name;
            $carts['user_id'] = $this->user_id;
            $carts['file'] = '/uploads/' . date('Ymd') . '/' . md5($carts['name']) . '.pdf';
            $carts['num'] = 1;
            $carts['create_time'] = time();
            $carts['information'] = 0;
            $carts['page'] = $pdf->setSourceFile('.' . $carts['file']);
            CartModel::create($carts);
            CartModel::where('id', 'in', $ids)->delete();
            return json(['code' => 20000, 'msg' => '文档合并成功']);
        } else {
            return json(['code' => 40000, 'msg' => '失败']);

        }
    }


    public function share_add()
    {
        if (!input('?post.ids') || !$this->user_id) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $ids = $this->request->param('ids');
        $share_detail = ShareDetailModel::where('id', 'in', $ids)->select();
        foreach ($share_detail as $value) {
            $detail['information'] = '2';
            $detail['file'] = $value['file'];
            $detail['name'] = $value['name'];
            $detail['paper'] = $value['paper'];
            $detail['brand'] = $value['brand'];
            $detail['page_turning'] = $value['page_turning'];
            $detail['single_double'] = $value['single_double'];
            $detail['colour'] = $value['colour'];
            $detail['color'] = $value['color'];
            $detail['page'] = $value['page'];
            $detail['binding_type'] = $value['binding_type'];
            $detail['film_covering'] = $value['film_covering'];
            $detail['cover_type'] = $value['cover_type'];
            $detail['cover_image'] = $value['cover_image'];
            $detail['reduction_printing'] = $value['reduction_printing'];
            $detail['print_range'] = $value['print_range'];
            $detail['num'] = $value['num'];
            $detail['price'] = $value['price'];
            $detail['commission'] = $value['commission'];
            $detail['create_time'] = time();
            $detail['user_id'] = $this->user_id;
            $detail['share_id'] = $value['share_id'];
            $detail['source_file'] = $this->request->param('source_file');
            CartModel::create($detail);
        }
        return json(['code' => 20000, 'msg' => '添加成功']);
    }


}