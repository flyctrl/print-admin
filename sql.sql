DROP TABLE IF EXISTS `fa_user`;
CREATE TABLE `fa_user`
(
    `id`           int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `openid`       varchar(100)   DEFAULT NULL COMMENT '用户openid',
    `nickname`     varchar(255)   DEFAULT NULL COMMENT '昵称',
    `avatar_image` varchar(255)   DEFAULT NULL COMMENT '头像',
    `balance`      decimal(11, 2) DEFAULT NULL COMMENT '余额',
    `parent_id`    int(11)        DEFAULT NULL COMMENT '上级用户ID',
    `create_time`  int(11)        DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='用户表';

#php  think crud -t user -u 1    -c user/user


DROP TABLE IF EXISTS `fa_paper`;
CREATE TABLE `fa_paper`
(
    `id`          int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `name`        varchar(255) DEFAULT NULL COMMENT '纸张规格名称',
    `remark`      varchar(255) DEFAULT NULL COMMENT '说明',
    `create_time` int(11)      DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='纸张规格管理';

php  think crud -t paper -u 1    -c prints/paper


DROP TABLE IF EXISTS `fa_brand`;
CREATE TABLE `fa_brand`
(
    `id`          int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `name`        varchar(255) DEFAULT NULL COMMENT '纸张类型名称',
    `weight`      int(11)      DEFAULT NULL COMMENT '重量（克）',
    `remark`      varchar(255) DEFAULT NULL COMMENT '说明',
    `create_time` int(11)      DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='纸张类型管理';



php  think crud -t brand -u 1    -c prints/brand


DROP TABLE IF EXISTS `fa_color`;
CREATE TABLE `fa_color`
(
    `id`          int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `name`        varchar(30) DEFAULT NULL COMMENT '颜色名称',
    `color_value` varchar(10) DEFAULT NULL COMMENT '色值',
    `create_time` int(11)     DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='颜色设置';


php  think crud -t color   -u 1    -c prints/color


DROP TABLE IF EXISTS `fa_reduce`;
CREATE TABLE `fa_reduce`
(

    `id`          int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `name`        varchar(30)  DEFAULT NULL COMMENT '名称',
    `remark`      varchar(255) DEFAULT NULL COMMENT '说明',
    `create_time` int(11)      DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='缩印管理';

php  think crud -t reduce   -u 1    -c prints/reduce

DROP TABLE IF EXISTS `fa_colour`;
CREATE TABLE `fa_colour`
(
    `id`          int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `name`        varchar(30)  DEFAULT NULL COMMENT '名称',
    `remark`      varchar(255) DEFAULT NULL COMMENT '说明',
    `create_time` int(11)      DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='打印方式管理';

php  think crud -t colour   -u 1    -c prints/colour

DROP TABLE IF EXISTS `fa_price`;
CREATE TABLE `fa_price`
(
    `id`                        int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `paper_cover_price`         decimal(11, 2) DEFAULT 0.00 COMMENT '胶装皮纹纸价格',
    `art_paper_price`           decimal(11, 2) DEFAULT 0.00 COMMENT '胶装铜版纸价格',
    `staple_price`              decimal(11, 2) DEFAULT 0.00 COMMENT '订书钉价格',
    `ring_mounting_price`       decimal(11, 2) DEFAULT 0.00 COMMENT '圈装价格',
    `area_json`                 text           DEFAULT NULL COMMENT '收费地区设置',
    `first_weight_price`        decimal(11, 2) DEFAULT NULL COMMENT '首重收费',
    `continuation_weight_price` decimal(11, 2) DEFAULT NULL COMMENT '续重收费（X元/1KG）',
    `random_color_price`        decimal(11, 2) DEFAULT NULL COMMENT '随机颜色收费',
    `select_color_price`        decimal(11, 2) DEFAULT NULL COMMENT '随机颜色收费',
    `cover_price`               decimal(11, 2) DEFAULT NULL COMMENT '封面价格',
    `create_time`               int(11)        DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='价格设置';


php  think crud -t price   -u 1    -c prints/price

DROP TABLE IF EXISTS `fa_introduce`;
CREATE TABLE `fa_introduce`
(
    `id`                         int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `binding_introduction`       varchar(255) DEFAULT NULL COMMENT '装订介绍',
    `cover_type_introduction`    varchar(255) DEFAULT NULL COMMENT '封面类型介绍',
    `cover_content_introduction` varchar(255) DEFAULT NULL COMMENT '封面类型介绍',
    `create_time`                int(11)      DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='介绍设置';


php  think crud -t introduce   -u 1    -c prints/introduce



DROP TABLE IF EXISTS `fa_prints`;
CREATE TABLE `fa_prints`
(
    `id`                int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',

    `brand_id`          int(11)        DEFAULT NULL COMMENT '纸张',
    `paper_id`          int(11)        DEFAULT NULL COMMENT '规格',
    `colour_id`         int(11)        DEFAULT NULL COMMENT '打印方式',
    `single_double`     enum ('0','1') DEFAULT NULL COMMENT '单双面:0=单面,1=双面',
    `price`             int(11)        DEFAULT NULL COMMENT '价格',
    `commission_switch` tinyint(1)     DEFAULT NULL COMMENT '是否反佣',
    `create_time`       int(11)        DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='价格设置';


php  think crud -t prints   -u 1  -r brand -k  brand_id  -r  paper  -k  paper_id   -r  colour  -k  colour_id   -c prints/prints

DROP TABLE IF EXISTS `fa_category`;
CREATE TABLE `fa_category`
(
    `id`          int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `name`        varchar(255) DEFAULT NULL COMMENT '名称',
    `parent_id`   int(11)      DEFAULT 0 COMMENT '上级分类ID',
    `create_time` int(11)      DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='分类管理';

php  think crud -t category   -u 1     -c information/one
php  think crud -t category   -u 1   -r  category -k pid  -c information/two


DROP TABLE IF EXISTS `fa_information`;
CREATE TABLE `fa_information`
(
    `id`              int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `category_one_id` int(11)      DEFAULT NULL COMMENT '一级分类ID',
    `category_two_id` int(11)      DEFAULT NULL COMMENT '二级分类ID',
    `name`            varchar(255) DEFAULT NULL COMMENT '资料名称',
    `image`           varchar(255) DEFAULT NULL COMMENT '资料图片',
    `content`         text         DEFAULT NULL COMMENT '资料详情',
    `create_time`     int(11)      DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='资料设置';

php  think crud -t information   -u 1   -r category -k category_one_id   -r category -k  category_two_id   -c information/information



DROP TABLE IF EXISTS `fa_file`;
CREATE TABLE `fa_file`
(
    `id`             int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `information_id` int(11)                    DEFAULT NULL COMMENT '资料ID',
    `file`           varchar(255)               DEFAULT NULL COMMENT '文件',
    `paper_id`       int(11)                    DEFAULT NULL COMMENT '纸张类型',
    `single_double`  enum ('0','1')             DEFAULT NULL COMMENT '单双面:0=单面,1=双面',
    `colour_id`      int(11)                    DEFAULT NULL COMMENT '色彩',
    `binding_type`   enum ('0','1','2','3','4') DEFAULT NULL COMMENT '装订方式:0=不装订,1=订书钉,2=圈装,3=胶装皮纹纸,4=胶装铜版纸',
    `price`          decimal(11, 2)             DEFAULT NULL COMMENT '价格',
    `create_time`    int(11)                    DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='文件设置';


# php  think crud -t file    -r paper -k paper_id   -r colour -k  colour_id   -c information/file

DROP TABLE IF EXISTS `fa_banner`;
CREATE TABLE `fa_banner`
(
    `id`          int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `image`       varchar(255) DEFAULT NULL COMMENT '图片',
    `create_time` int(11)      DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='轮播图';

php  think crud -t  banner    -u  1      -c basic/banner

DROP TABLE IF EXISTS `fa_ticket`;
CREATE TABLE `fa_ticket`
(
    `id`               int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `ticket_name`      varchar(255)   DEFAULT NULL COMMENT '优惠券名称',
    `limitation_price` decimal(11, 2) DEFAULT NULL COMMENT '满足金额',
    `deduction_price`  decimal(11, 2) DEFAULT NULL COMMENT '抵扣金额',
    `end_time`         int(11)        DEFAULT NULL COMMENT '到期时间',
    `user_id`          int(11)        DEFAULT NULL COMMENT '用户ID',
    `create_time`      int(11)        DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='优惠券';

php  think crud -t ticket   -u 1    -c basic/ticket

DROP TABLE IF EXISTS `fa_news_ticket`;
CREATE TABLE `fa_news_ticket`
(
    `id`               int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `ticket_name`      varchar(255)   DEFAULT NULL COMMENT '优惠券名称',
    `limitation_price` decimal(11, 2) DEFAULT NULL COMMENT '满足金额',
    `deduction_price`  decimal(11, 2) DEFAULT NULL COMMENT '抵扣金额',
    `end_time`         int(11)        DEFAULT NULL COMMENT '到期时间',
    `user_ids`         int(11)        DEFAULT NULL COMMENT '用户ID',
    `create_time`      int(11)        DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='发放优惠券';

php  think crud -t news_ticket   -u 1    -c basic/news

DROP TABLE IF EXISTS `fa_invoice`;
CREATE TABLE `fa_invoice`
(
    `id`             int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `user_id`        int(11)            DEFAULT NULL COMMENT '用户ID',
    `invoice_time`   int(11)            DEFAULT NULL COMMENT '申请时间',
    `order_ids`      varchar(255)       DEFAULT NULL COMMENT '关联订单ID',
    `look_up_id`     varchar(255)       DEFAULT NULL COMMENT '抬头ID',
    `invoice_amount` decimal(11, 2)     DEFAULT NULL COMMENT '开票金额',
    `status`         enum ('0','1','2') DEFAULT '0' COMMENT '开票金额:0=未开票,1=已开票,2=驳回',
    `create_time`    int(11)            DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='开票设置';

php  think crud -t invoice   -u 1    -c basic/invoice


DROP TABLE IF EXISTS `fa_common_question`;
CREATE TABLE `fa_common_question`
(
    `id`          int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `question`    varchar(255) DEFAULT NULL COMMENT '问题名称',
    `answer`      text         DEFAULT NULL COMMENT '答案',
    `create_time` int(11)      DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='常见问题';

php  think crud -t common_question   -u 1    -c basic/question



DROP TABLE IF EXISTS `fa_opinion`;
CREATE TABLE `fa_opinion`
(

    `id`          int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `user_id`     int(11)      DEFAULT NULL COMMENT '用户ID',
    `question`    varchar(255) DEFAULT NULL COMMENT '问题',
    `image`       varchar(255) DEFAULT NULL COMMENT '图片',
    `create_time` int(11)      DEFAULT NULL COMMENT '提交时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='投诉建议';


php  think crud -t opinion   -u 1   -r user -k user_id  -c basic/opinion



DROP TABLE IF EXISTS `fa_system`;
CREATE TABLE `fa_system`
(
    `id`               int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `price_remark`     text COMMENT '价格说明',
    `password`         varchar(255) DEFAULT NULL COMMENT '淘宝口令设置',
    `pay_proportion`   int(11)      DEFAULT NULL COMMENT '自购返点比例（%）',
    `share_proportion` int(11)      DEFAULT NULL COMMENT '分销返点比例（%）',
    `create_time`      int(11)      DEFAULT NULL COMMENT '提交时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='基本配置';


php  think crud -t system   -u 1    -c basic/system


DROP TABLE IF EXISTS `fa_label`;
CREATE TABLE `fa_label`
(
    `id`   int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `name` varchar(255) DEFAULT NULL COMMENT '标签名称',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='标签管理';

php  think crud -t label   -u 1    -c remark/label
DROP TABLE IF EXISTS `fa_comment`;
CREATE TABLE `fa_comment`
(
    `id`           int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `nickname`     varchar(255) DEFAULT NULL COMMENT '昵称',
    `avatar_image` varchar(255) DEFAULT NULL COMMENT '头像',
    `star`         int(11)      DEFAULT NULL COMMENT '星级',
    `label_ids`    varchar(255) DEFAULT NULL COMMENT '标签',
    `remark`       varchar(255) DEFAULT NULL COMMENT '内容',
    `images`       varchar(500) DEFAULT NULL COMMENT '图片',
    `like_num`     int(11)      DEFAULT NULL COMMENT '点赞数',
    `create_time`  int(11)      DEFAULT NULL COMMENT '发布时间',

    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='评论管理';


php  think crud -t comment   -r  label -k   label_ids -u 1    -c remark/comment



CREATE TABLE `fa_user_address`
(
    `id`          int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `user_id`     int(11)        DEFAULT NULL COMMENT '用户ID',
    `name`        varchar(50)    DEFAULT NULL COMMENT '收货人',
    `phone`       varchar(20)    DEFAULT NULL COMMENT '手机号码',
    `province`    varchar(100)   DEFAULT NULL COMMENT '省',
    `city`        varchar(100)   DEFAULT NULL COMMENT '市',
    `area`        varchar(100)   DEFAULT NULL COMMENT '区',
    `address`     varchar(255)   DEFAULT NULL COMMENT '详细地址',
    `default`     enum ('0','1') DEFAULT '0' COMMENT '是否默认:0=正常,1=默认',
    `create_time` int(11)        DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 38
  DEFAULT CHARSET = utf8 COMMENT ='用户地址管理';



DROP TABLE IF EXISTS `fa_user_news_ticket`;
CREATE TABLE `fa_user_news_ticket`
(
    `id`               int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `user_id`          int(11)        DEFAULT NULL COMMENT '用户ID',
    `ticket_name`      varchar(255)   DEFAULT NULL COMMENT '优惠券名称',
    `limitation_price` decimal(11, 2) DEFAULT NULL COMMENT '满足金额',
    `deduction_price`  decimal(11, 2) DEFAULT NULL COMMENT '反余额金额',
    `status`           enum ('0','1') DEFAULT 0 COMMENT '状态:0=未使用,1=已使用',
    `create_time`      int(11)        DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='新用户优惠券领取记录';



DROP TABLE IF EXISTS `fa_cart`;
CREATE TABLE `fa_cart`
(
    `id`                 int(11)                    DEFAULT NULL COMMENT 'ID',
    `user_id`            int(11)                    DEFAULT NULL COMMENT '用户ID',
    `information`        enum ('0','1')             DEFAULT '1' COMMENT '0=用户上传,1=资料库',
    `file`               varchar(255)               DEFAULT NULL COMMENT '文件',
    `name`               varchar(255)               DEFAULT NULL COMMENT '文件名称',
    `paper`              varchar(200)               DEFAULT NULL COMMENT '纸张类型',
    `single_double`      enum ('0','1')             DEFAULT NULL COMMENT '单双面:0=单面,1=双面',
    `colour`             int(11)                    DEFAULT NULL COMMENT '色彩',
    `binding_type`       enum ('0','1','2','3','4') DEFAULT NULL COMMENT '装订方式:0=不装订,1=订书钉,2=圈装,3=胶装皮纹纸,4=胶装铜版纸',
    `film_covering`      enum ('0','1')             DEFAULT '0' COMMENT '覆膜:0=否,1=是',
    `cover_type`         enum ('0','1','2')         DEFAULT NULL COMMENT '封面类型:0=文字封面,1=首页为封面,2=图片封面',
    `reduction_printing` varchar(200)               DEFAULT NULL COMMENT '缩印',
    `print_range`        varchar(500)               DEFAULT NULL COMMENT '打印范围',
    `num`                int(11)                    DEFAULT NULL COMMENT '份数',

    `create_time`        int(11)                    DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='购物车';



DROP TABLE IF EXISTS `fa_user_ticket`;
CREATE TABLE `fa_ticket`
(
    `id`               int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `ticket_name`      varchar(255)       DEFAULT NULL COMMENT '优惠券名称',
    `limitation_price` decimal(11, 2)     DEFAULT NULL COMMENT '满足金额',
    `deduction_price`  decimal(11, 2)     DEFAULT NULL COMMENT '抵扣金额',
    `end_time`         int(11)            DEFAULT NULL COMMENT '到期时间',
    `user_id`          int(11)            DEFAULT NULL COMMENT '用户ID',
    `status`           enum ('0','1','2') DEFAULT NULL COMMENT '状态:0=未使用,1=已使用,2=已过期',
    `create_time`      int(11)            DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='优惠券';


DROP TABLE IF EXISTS `fa_user_collect`;
CREATE TABLE `fa_user_collect`
(
    `id`             int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `user_id`        int(11) DEFAULT NULL COMMENT '用户ID',
    `information_id` int(11) DEFAULT NULL COMMENT '资料ID',
    `create_time`    int(11) DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='用户收藏';



DROP TABLE IF EXISTS `f_look_up`;
CREATE TABLE `fa_look_up`
(
    `id`              int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `name`            varchar(255) DEFAULT NULL COMMENT '抬头',
    `duty_paragraph`  varchar(255) DEFAULT NULL COMMENT '税号',
    `company_address` varchar(255) DEFAULT NULL COMMENT '注册地址',
    `bank`            varchar(255) DEFAULT NULL COMMENT '开户行',
    `bank_account`    varchar(255) DEFAULT NULL COMMENT '银行账户',
    `create_time`     int(11)      DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='抬头';



DROP TABLE IF EXISTS `fa_order`;
CREATE TABLE `fa_order`
(
    `id`             int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `user_id`        int(11)                            DEFAULT NULL COMMENT '用户ID',
    `address_id`     int(11)                            DEFAULT NULL COMMENT '地址ID',
    `name`           varchar(200)                       DEFAULT NULL COMMENT '姓名',
    `phone`          varchar(20)                        DEFAULT NULL COMMENT '手机号',
    `address`        varchar(255)                       DEFAULT NULL COMMENT '收货地址',
    `order_num`      varchar(50)                        DEFAULT NULL COMMENT '订单号',
    `file_num`       int(2)                             DEFAULT NULL COMMENT '文件数量',
    `order_price`    decimal(11, 2)                     DEFAULT NULL COMMENT '订单金额',
    `user_ticket_id` int(11)                            DEFAULT NULL COMMENT '用户优惠券ID',
    `ticket_price`   decimal(11, 2)                     DEFAULT NULL COMMENT '优惠券抵扣金额',
    `balance`        decimal(11, 2)                     DEFAULT NULL COMMENT '余额抵扣金额',
    `total_price`    decimal(11, 2)                     DEFAULT NULL COMMENT '订单实付金额',
    `pay_time`       int(11)                            DEFAULT NULL COMMENT '订单支付时间',
    `freight`        decimal(11, 2)                     DEFAULT NULL COMMENT '运费',
    `pay_type`       enum ('0','1','2')                 DEFAULT NULL COMMENT '支付方式:0=微信,1=淘宝,2=拼多多',
    `express_id`     varchar(200)                       DEFAULT NULL COMMENT '快递公司名称',
    `express_num`    varchar(100)                       DEFAULT NULL COMMENT '快递单号',
    `pay_status`     enum ('0','1','2','3','4','5','6') DEFAULT NULL COMMENT '订单状态:0=待支付,1=待打印,2=待收货,3=已完成,4=退款中,5=已驳回,6=退款成功',
    `create_time`    int(11)                            DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='订单列表';



php  think crud -t order   -r  user -k   user_id -u 1    -c order/prints
php  think crud -t order   -r  user -k   user_id -u 1    -c order/received
php  think crud -t order   -r  user -k   user_id -u 1    -c order/complete
php  think crud -t order   -r  user -k   user_id -u 1    -c order/refund


DROP TABLE IF EXISTS `fa_order_detail`;
CREATE TABLE `fa_order_detail`
(
    `id`                 int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `user_id`            int(11)            DEFAULT NULL COMMENT '用户ID',
    `information`        enum ('0','1')     DEFAULT '1' COMMENT '0=用户上传,1=资料库',
    `order_id`           int(11)            DEFAULT NULL COMMENT '订单ID',
    `file`               varchar(255)       DEFAULT NULL COMMENT '文件',
    `name`               varchar(255)       DEFAULT NULL COMMENT '文件名称',
    `paper`              varchar(200)       DEFAULT NULL COMMENT '纸张类型',
    `single_double`      enum ('0','1')     DEFAULT NULL COMMENT '单双面:0=单面,1=双面',
    `colour`             varchar(200)       DEFAULT NULL COMMENT '色彩',
    `color`              varchar(255)       DEFAULT NULL COMMENT '封面颜色',
    `binding_type`       varchar(200)       DEFAULT NULL COMMENT '装订方式',
    `film_covering`      varchar(200)       DEFAULT NULL COMMENT '覆膜',
    `cover_type`         varchar(200)       DEFAULT NULL COMMENT '封面类型',
    `type`               enum ('0','1','2') DEFAULT NULL COMMENT '方向:0=全是横向,1=全是纵向,2=横纵都有',
    `cover_image`        varchar(255)       DEFAULT NULL COMMENT '封面图片',
    `reduction_printing` varchar(200)       DEFAULT NULL COMMENT '缩印',
    `print_range`        varchar(500)       DEFAULT NULL COMMENT '打印范围',
    `num`                int(11)            DEFAULT NULL COMMENT '份数',
    `price`              decimal(10, 2)     DEFAULT NULL COMMENT '单价',
    `create_time`        int(11)            DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='订单详情';


DROP TABLE IF EXISTS `f_express`;
CREATE TABLE `f_express`
(
    `id`   int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',

    `name` varchar(255) DEFAULT NULL COMMENT '名称',
    `code` varchar(255) DEFAULT NULL COMMENT 'code',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='快递';


php  think crud -t express     -c express



DROP TABLE IF EXISTS `fa_share_info`;
CREATE TABLE `fa_share_info`
(
    `id`               int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `user_id`          int(11)      DEFAULT NULL COMMENT '用户ID',
    `name`             varchar(255) DEFAULT NULL COMMENT '文件名称',
    `share_detail_ids` varchar(255) DEFAULT NULL COMMENT '文件IDS',
    `code`             varchar(255) DEFAULT NULL COMMENT '分享码',
    `create_time`      int(11)      DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='分享清单';



DROP TABLE IF EXISTS `fa_share_detail`;
CREATE TABLE `fa_share_detail`
(
    `id`                 int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `information`        enum ('0','1')     DEFAULT '1' COMMENT '0=用户上传,1=资料库',
    `file`               varchar(255)       DEFAULT NULL COMMENT '文件',
    `page_turning`       varchar(255)       DEFAULT NULL COMMENT '翻页方式',
    `name`               varchar(255)       DEFAULT NULL COMMENT '文件名称',
    `paper`              varchar(200)       DEFAULT NULL COMMENT '纸张类型',
    `brand`              varchar(255)       DEFAULT NULL COMMENT '纸型',
    `single_double`      enum ('0','1')     DEFAULT NULL COMMENT '单双面:0=单面,1=双面',
    `colour`             varchar(200)       DEFAULT NULL COMMENT '色彩',
    `color`              varchar(255)       DEFAULT NULL COMMENT '封面颜色',
    `binding_type`       varchar(200)       DEFAULT NULL COMMENT '装订方式',
    `film_covering`      varchar(200)       DEFAULT NULL COMMENT '覆膜',
    `cover_type`         varchar(200)       DEFAULT NULL COMMENT '封面类型',
    `type`               enum ('0','1','2') DEFAULT NULL COMMENT '方向:0=全是横向,1=全是纵向,2=横纵都有',
    `cover_image`        varchar(255)       DEFAULT NULL COMMENT '封面图片',
    `reduction_printing` varchar(200)       DEFAULT NULL COMMENT '缩印',
    `print_range`        varchar(500)       DEFAULT NULL COMMENT '打印范围',
    `num`                int(11)            DEFAULT NULL COMMENT '份数',
    `price`              decimal(10, 2)     DEFAULT NULL COMMENT '单价',
    `page`               int(11)            DEFAULT NULL COMMENT '页数',
    `commission`         int(11)            DEFAULT '1',
    `create_time`        int(11)            DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='分享内容';



DROP TABLE IF EXISTS `fa_order_express`;
CREATE TABLE `fa_order_express`
(

    `id`         int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `order_id` int(11) DEFAULT NULL COMMENT '订单ID',
    `user_id`    int(11)      DEFAULT NULL COMMENT '用户ID',
    `address_id` int(11)      DEFAULT NULL COMMENT '地址ID',
    `name`       varchar(200) DEFAULT NULL COMMENT '姓名',
    `phone`      varchar(20)  DEFAULT NULL COMMENT '手机号',
    `address`    varchar(255) DEFAULT NULL COMMENT '收货地址',
    `status` enum('0','1','2') DEFAULT '0' COMMENT '类型:0=审核中,1=已同意,2=已驳回',
    `create_time`        int(11)            DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='修改收货地址';



php  think crud -t order_express   -r  order -k   order_id -u 1    -c order/express





DROP TABLE IF EXISTS `fa_notice`;
CREATE TABLE `fa_system`
(
    `id`               int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `system_notice`     varchar(500) COMMENT '系统公告',
    `notice`         varchar(255) DEFAULT NULL COMMENT '客服公告',
    `create_time`      int(11)      DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='公告管理';

php  think crud -t system   -u 1    -c basic/notice


CREATE TABLE `fa_share`(
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `user_id` int(11) DEFAULT NULL COMMENT '用户ID',
    `url` varchar(255) DEFAULT NULL COMMENT '分享链接',
)