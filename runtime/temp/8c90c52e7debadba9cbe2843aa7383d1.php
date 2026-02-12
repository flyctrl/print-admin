<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:74:"/www/wwwroot/print/public/../application/admin/view/basic/system/edit.html";i:1746627254;s:61:"/www/wwwroot/print/application/admin/view/layout/default.html";i:1689043530;s:58:"/www/wwwroot/print/application/admin/view/common/meta.html";i:1689043530;s:60:"/www/wwwroot/print/application/admin/view/common/script.html";i:1689043530;}*/ ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:''); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">
<meta name="referrer" content="never">
<meta name="robots" content="noindex, nofollow">

<link rel="shortcut icon" href="/assets/img/favicon.ico" />
<!-- Loading Bootstrap -->
<link href="/assets/css/backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">

<?php if(\think\Config::get('fastadmin.adminskin')): ?>
<link href="/assets/css/skins/<?php echo \think\Config::get('fastadmin.adminskin'); ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">
<?php endif; ?>

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="/assets/js/html5shiv.js"></script>
  <script src="/assets/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">
    var require = {
        config:  <?php echo json_encode($config); ?>
    };
</script>

    </head>

    <body class="inside-header inside-aside <?php echo defined('IS_DIALOG') && IS_DIALOG ? 'is-dialog' : ''; ?>">
        <div id="main" role="main">
            <div class="tab-content tab-addtabs">
                <div id="content">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <section class="content-header hide">
                                <h1>
                                    <?php echo __('Dashboard'); ?>
                                    <small><?php echo __('Control panel'); ?></small>
                                </h1>
                            </section>
                            <?php if(!IS_DIALOG && !\think\Config::get('fastadmin.multiplenav') && \think\Config::get('fastadmin.breadcrumb')): ?>
                            <!-- RIBBON -->
                            <div id="ribbon">
                                <ol class="breadcrumb pull-left">
                                    <?php if($auth->check('dashboard')): ?>
                                    <li><a href="dashboard" class="addtabsit"><i class="fa fa-dashboard"></i> <?php echo __('Dashboard'); ?></a></li>
                                    <?php endif; ?>
                                </ol>
                                <ol class="breadcrumb pull-right">
                                    <?php foreach($breadcrumb as $vo): ?>
                                    <li><a href="javascript:;" data-url="<?php echo $vo['url']; ?>"><?php echo $vo['title']; ?></a></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                            <!-- END RIBBON -->
                            <?php endif; ?>
                            <div class="content">
                                <form id="edit-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Price_remark'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <textarea id="c-price_remark" class="form-control editor" rows="5" name="row[price_remark]" cols="50"><?php echo htmlentities($row['price_remark']); ?></textarea>
        </div>
    </div>


    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('淘宝返现规则说明'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <textarea id="c-taobao_content" class="form-control editor" rows="5" name="row[taobao_content]" cols="50"><?php echo htmlentities($row['taobao_content']); ?></textarea>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('淘宝返现金额'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-taobao_price" class="form-control" name="row[taobao_price]" type="number" value="<?php echo htmlentities($row['taobao_price']); ?>">
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('分享折扣'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-share_discount" class="form-control" name="row[share_discount]" type="text" value="<?php echo htmlentities($row['share_discount']); ?>">
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('微信二维码'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <div class="input-group">
                <input id="c-image" class="form-control" size="50" name="row[image]" type="text" value="<?php echo htmlentities($row['image']); ?>">
                <div class="input-group-addon no-border no-padding">
                    <span><button type="button" id="faupload-image" class="btn btn-danger faupload" data-input-id="c-image" data-mimetype="image/gif,image/jpeg,image/png,image/jpg,image/bmp,image/webp" data-multiple="false" data-preview-id="p-image"><i class="fa fa-upload"></i> <?php echo __('Upload'); ?></button></span>
                    <span><button type="button" id="fachoose-image" class="btn btn-primary fachoose" data-input-id="c-image" data-mimetype="image/*" data-multiple="false"><i class="fa fa-list"></i> <?php echo __('Choose'); ?></button></span>
                </div>
                <span class="msg-box n-right" for="c-image"></span>
            </div>
            <ul class="row list-inline faupload-preview" id="p-image"></ul>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Password'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-password" class="form-control" name="row[password]" type="text" value="<?php echo htmlentities($row['password']); ?>">
        </div>
    </div>
    
     <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('拼多多口令'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-pdd_password" class="form-control" name="row[pdd_password]" type="text" value="<?php echo htmlentities($row['pdd_password']); ?>">
        </div>
    </div>
     <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('淘宝图片'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <div class="input-group">
                <input id="c-taobao_image" class="form-control" size="50" name="row[taobao_image]" type="text" value="<?php echo htmlentities($row['taobao_image']); ?>">
                <div class="input-group-addon no-border no-padding">
                    <span><button type="button" id="faupload-taobao_image" class="btn btn-danger faupload" data-input-id="c-taobao_image" data-mimetype="image/gif,image/jpeg,image/png,image/jpg,image/bmp,image/webp" data-multiple="false" data-preview-id="p-taobao_image"><i class="fa fa-upload"></i> <?php echo __('Upload'); ?></button></span>
                    <span><button type="button" id="fachoose-taobao_image" class="btn btn-primary fachoose" data-input-id="c-taobao_image" data-mimetype="image/*" data-multiple="false"><i class="fa fa-list"></i> <?php echo __('Choose'); ?></button></span>
                </div>
                <span class="msg-box n-right" for="c-taobao_image"></span>
            </div>
            <ul class="row list-inline faupload-preview" id="p-taobao_image"></ul>
        </div>
    </div>
     <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('拼多多图片'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <div class="input-group">
                <input id="c-pdd_image" class="form-control" size="50" name="row[pdd_image]" type="text" value="<?php echo htmlentities($row['pdd_image']); ?>">
                <div class="input-group-addon no-border no-padding">
                    <span><button type="button" id="faupload-pdd_image" class="btn btn-danger faupload" data-input-id="c-pdd_image" data-mimetype="image/gif,image/jpeg,image/png,image/jpg,image/bmp,image/webp" data-multiple="false" data-preview-id="p-pdd_image"><i class="fa fa-upload"></i> <?php echo __('Upload'); ?></button></span>
                    <span><button type="button" id="fachoose-pdd_image" class="btn btn-primary fachoose" data-input-id="c-pdd_image" data-mimetype="image/*" data-multiple="false"><i class="fa fa-list"></i> <?php echo __('Choose'); ?></button></span>
                </div>
                <span class="msg-box n-right" for="c-pdd_image"></span>
            </div>
            <ul class="row list-inline faupload-preview" id="p-pdd_image"></ul>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Pay_proportion'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-pay_proportion" class="form-control" name="row[pay_proportion]" type="number" value="<?php echo htmlentities($row['pay_proportion']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Share_proportion'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-share_proportion" class="form-control" name="row[share_proportion]" type="number" value="<?php echo htmlentities($row['share_proportion']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('签约协议'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <div class="input-group">
                <input id="c-agreement_file" class="form-control" size="50" name="row[agreement_file]" type="text" value="<?php echo htmlentities($row['agreement_file']); ?>">
                <div class="input-group-addon no-border no-padding">
                    <span><button type="button" id="faupload-agreement_file" class="btn btn-danger faupload" data-input-id="c-agreement_file" data-multiple="false" data-preview-id="p-agreement_file"><i class="fa fa-upload"></i> <?php echo __('Upload'); ?></button></span>
                    <span><button type="button" id="fachoose-agreement_file" class="btn btn-primary fachoose" data-input-id="c-agreement_file" data-multiple="false"><i class="fa fa-list"></i> <?php echo __('Choose'); ?></button></span>
                </div>
                <span class="msg-box n-right" for="c-agreement_file"></span>
            </div>
            <ul class="row list-inline faupload-preview" id="p-agreement_file"></ul>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('签约说明'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <textarea id="c-sign_content" class="form-control editor" rows="5" name="row[sign_content]" cols="50"><?php echo htmlentities($row['sign_content']); ?></textarea>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('弹窗内容'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-taobao_remark" class="form-control" name="row[taobao_remark]" type="text" value="<?php echo htmlentities($row['taobao_remark']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('QQ上传说明'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <textarea id="c-qq_content" class="form-control editor" rows="5" name="row[qq_content]" cols="50"><?php echo htmlentities($row['qq_content']); ?></textarea>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('WPS上传说明'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <textarea id="c-wps_content" class="form-control editor" rows="5" name="row[wps_content]" cols="50"><?php echo htmlentities($row['wps_content']); ?></textarea>
        </div>
    </div>


    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('效果图'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <div class="input-group">
                <input id="c-rendering" class="form-control" size="50" name="row[rendering]" type="text" value="<?php echo htmlentities($row['rendering']); ?>">
                <div class="input-group-addon no-border no-padding">
                    <span><button type="button" id="faupload-rendering" class="btn btn-danger faupload" data-input-id="c-rendering" data-mimetype="image/gif,image/jpeg,image/png,image/jpg,image/bmp,image/webp" data-multiple="false" data-preview-id="p-rendering"><i class="fa fa-upload"></i> <?php echo __('Upload'); ?></button></span>
                    <span><button type="button" id="fachoose-rendering" class="btn btn-primary fachoose" data-input-id="c-rendering" data-mimetype="image/*" data-multiple="false"><i class="fa fa-list"></i> <?php echo __('Choose'); ?></button></span>
                </div>
                <span class="msg-box n-right" for="c-rendering"></span>
            </div>
            <ul class="row list-inline faupload-preview" id="p-rendering"></ul>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Create_time'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-create_time" class="form-control datetimepicker" data-date-format="YYYY-MM-DD HH:mm:ss" data-use-current="true" name="row[create_time]" type="text" value="<?php echo $row['create_time']?datetime($row['create_time']):''; ?>">
        </div>
    </div>
    <div class="form-group layer-footer">
        <label class="control-label col-xs-12 col-sm-2"></label>
        <div class="col-xs-12 col-sm-8">
            <button type="submit" class="btn btn-primary btn-embossed disabled"><?php echo __('OK'); ?></button>
        </div>
    </div>
</form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo htmlentities($site['version']); ?>"></script>
    </body>
</html>
