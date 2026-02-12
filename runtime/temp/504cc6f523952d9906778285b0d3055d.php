<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:75:"/www/wwwroot/print/public/../application/admin/view/order/order/detail.html";i:1711263303;s:61:"/www/wwwroot/print/application/admin/view/layout/default.html";i:1689043530;s:58:"/www/wwwroot/print/application/admin/view/common/meta.html";i:1689043530;s:60:"/www/wwwroot/print/application/admin/view/common/script.html";i:1689043530;}*/ ?>
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
                                <!DOCTYPE html >
<html >
	<head>
		<meta charset="utf-8">
		<title></title>
		<style>
			* {
				margin: 0;
				padding: 0;
			}

			.main {
				width: 90%;
				max-width: 1200px;
				margin: 20px auto;
			}
			.title {
				color: red;
				font-size: 16px;
				line-height: 36px;
				text-align: center;
				border-bottom: none !important;
			}

			.wrapper {
				border: 1px solid #9a9a9a;
				border-bottom: none;
			}

			.wrapper:last-child {
				border-bottom: 1px solid #9a9a9a;
			}

			.wrapper1 {
				border-left: 1px solid #9a9a9a;
				border-right: 1px solid #9a9a9a;
			}

			.content {
				font-size: 15px;
				line-height: 24px;
				border-top-color: #9a9a9a;
				min-height: 160px !important;
				 padding: 0!important;
				 margin: 0;
				/* margin-left: auto; */
				/* padding-left: 15px; */
				/* padding-right: 15px; */

			}

			.block {
				display: flex;
				border-bottom: 1px solid #9a9a9a;
				border-left: 1px solid #9a9a9a;
				border-right: 1px solid #9a9a9a;
			}

			.wrapper .block:last-child,
			.wrapper1 .block:last-child {
				border-bottom: none;
			}

			.wrapper .block,
			.wrapper1 .block {
				border-left: none;
				border-right: none;
			}

			.block.active {
				text-align: center;
			}

			.block div {
				flex: 1;
				/*padding: 6px 5px;*/
				border-left: 1px solid #9a9a9a;
			}

			.block div:first-child {
				flex: 5;
				border-left: none;
			}

			.block .padd {
				/*padding: 5px 5px;*/
				height: 40px;
			}

			.block1 {
				display: flex;
				border-bottom: 1px solid #9a9a9a;
			}
			
			.block1:last-child {
				border-bottom: none;
			}

			.block1 div {
				flex: 1.4;
				padding: 6px 5px;
				border-left: 1px solid #9a9a9a;
			}

			.block1 div:first-child {
				flex: 3;
				border-left: none;
			}

			.block1 div:nth-child(4) {
				flex: 2;
			}

			.block1 div:nth-child(5) {
				flex: 3;
			}
		</style>
	</head>
	<body>
		<div>
			<div class="main">
				<div>
					<div class="title wrapper">费用清单</div>
					<div class="content wrapper">
						<?php if(is_array($order_detail) || $order_detail instanceof \think\Collection || $order_detail instanceof \think\Paginator): $i = 0; $__LIST__ = $order_detail;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
						<div class="block">
							<div><?php echo $vo['name']; ?></div>
							<div><?php echo $vo['num']; ?>份</div>
							<div><?php echo $vo['num'] * $vo['price']; ?>元</div>
						</div>
						<?php endforeach; endif; else: echo "" ;endif; ?>
						<div class="block active">
							<div>共计<?php echo $order['order_price']; ?>，总装订<?php echo $ding; ?>本，共打印<?php echo $total; ?>份</div>
						</div>
						<div class="block">
							<div class="padd">系统已经给予优惠了，电商平台不支持改小数点后面金额，下单将在以上价格上采取四舍五入</div>
						</div>
						<div class="block">
							<div class="padd" style="flex: 1;">付款时间：<?php echo date('Y-m-d H:i',$order['create_time']); ?></div>
							<div class="padd">付款方式：
								<?php if($order['pay_type'] == '0'): ?>
								小程序
								<?php endif; if($order['pay_type'] == '1'): ?>
								淘宝
								<?php endif; if($order['pay_type'] == '2'): ?>
								拼多多
								<?php endif; ?>
							</div>

							<div class="padd"></div>


							<div class="padd"></div>

							<div class="padd"></div>

						</div>
					</div>
				</div>
				<div>
					<div class="title wrapper1">打印清单</div>
					<div class="wrapper">
						<div class="block1">
							<div>文件名</div>
							<div>大小</div>
							<div>打印方式</div>
							<div>颜色</div>
							<div>单双面</div>
							<div>打印范围/页数/张数</div>
							<div>份数</div>
							<div>装订</div>
							<div>纸张</div>
						</div>
						<?php if(is_array($order_detail) || $order_detail instanceof \think\Collection || $order_detail instanceof \think\Paginator): $i = 0; $__LIST__ = $order_detail;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
						<div class="block1">
							<div><?php echo $vo['name']; ?></div>
							<div><?php echo $vo['paper']; ?></div>
							<div><?php echo $vo['colour']; ?></div>
							<div><?php echo $vo['color']; ?></div>
							<?php if($vo['single_double'] == '0'): ?>
							<div>单面</div>
							<?php else: ?>
							<div>双面</div>
							<?php endif; ?>
							<div><?php echo $vo['print_range']; ?>/<?php echo $vo['page']; ?>/
								<?php if($vo['single_double'] == '0'): ?>
								<?php echo $vo['page']; else: ?>
								<?php echo $vo['page']/2; endif; ?>

							</div>
							<div><?php echo $vo['num']; ?></div>
							<div><?php echo $vo['binding_type']; ?></div>
							<div><?php echo $vo['brand']; ?></div>
						</div>
						<?php endforeach; endif; else: echo "" ;endif; ?>
					</div>
				</div>
				<div class="block">
					<div>说明：</div>
				</div>
				<div class="block">
					<div class="padd">1.WORD、PPT格式兼容性差，卖家不对跑版跳行，格式变化掉字等承担负责，如对格式要求高请自行转为PDF格式
					</div>
				</div>
				<div class="block">
					<div class="padd">2. 文件清晰度，亲们自行检查后传送文档本身不清晰，字体过小属顾客责任，卖家错漏，亲们可联系客服售后(退款、补发退货)
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo htmlentities($site['version']); ?>"></script>
    </body>
</html>
