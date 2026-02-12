<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:74:"/www/wwwroot/print/public/../application/admin/view/prints/price/edit.html";i:1704939227;s:61:"/www/wwwroot/print/application/admin/view/layout/default.html";i:1689043530;s:58:"/www/wwwroot/print/application/admin/view/common/meta.html";i:1689043530;s:60:"/www/wwwroot/print/application/admin/view/common/script.html";i:1689043530;}*/ ?>
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
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Paper_cover_price'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-paper_cover_price" class="form-control" step="0.01" name="row[paper_cover_price]" type="number" value="<?php echo htmlentities($row['paper_cover_price']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Art_paper_price'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-art_paper_price" class="form-control" step="0.01" name="row[art_paper_price]" type="number" value="<?php echo htmlentities($row['art_paper_price']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Staple_price'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-staple_price" class="form-control" step="0.01" name="row[staple_price]" type="number" value="<?php echo htmlentities($row['staple_price']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Ring_mounting_price'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-ring_mounting_price" class="form-control" step="0.01" name="row[ring_mounting_price]" type="number" value="<?php echo htmlentities($row['ring_mounting_price']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Film_covering'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-film_covering" class="form-control" step="0.01" name="row[film_covering]" type="number" value="<?php echo htmlentities($row['film_covering']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Area_json'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            
            <dl class="fieldlist" data-name="row[area_json]" data-template="area">
                <dd>
                    <ins><?php echo __('城市'); ?></ins>
                  
                </dd>
                <dd><a href="javascript:;" class="btn btn-sm btn-success btn-append"><i class="fa fa-plus"></i> <?php echo __('Append'); ?></a></dd>
                <textarea name="row[area_json]" class="form-control hide" cols="30" rows="5"><?php echo htmlentities($row['area_json']); ?></textarea>
            </dl>


        </div>
    </div>
    <script type="text/html" id="area">
    <dd class="form-inline">
        <input type="text" name="row[<%=name%>][<%=index%>][name]" class="form-control" value="<%=row['name']%>" size="10"> 
       
        <span class="btn btn-sm btn-danger btn-remove"><i class="fa fa-times"></i></span> <span class="btn btn-sm btn-primary btn-dragsort"><i class="fa fa-arrows"></i></span>
    </dd>
</script>
    
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('First_weight_price'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-first_weight_price" class="form-control" step="0.01" name="row[first_weight_price]" type="number" value="<?php echo htmlentities($row['first_weight_price']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Continuation_weight_price'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-continuation_weight_price" class="form-control" step="0.01" name="row[continuation_weight_price]" type="number" value="<?php echo htmlentities($row['continuation_weight_price']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Random_color_price'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-random_color_price" class="form-control" step="0.01" name="row[random_color_price]" type="number" value="<?php echo htmlentities($row['random_color_price']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Select_color_price'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-select_color_price" class="form-control" step="0.01" name="row[select_color_price]" type="number" value="<?php echo htmlentities($row['select_color_price']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Cover_price'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-cover_price" class="form-control" step="0.01" name="row[cover_price]" type="number" value="<?php echo htmlentities($row['cover_price']); ?>">
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
