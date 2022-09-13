CREATE TABLE `cm_pay_center_channels` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '渠道名称',
  `order_address` varchar(255) NOT NULL COMMENT '下单地址',
  `callback_ip` text NOT NULL COMMENT '回调ip',
  `template_id` int(11) NOT NULL COMMENT '渠道模板id',
  `remarks` text COMMENT '备注',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



ALTER TABLE `cm_user` ADD COLUMN pay_center_uid int(10) COMMENT '用户中心人员id';
CREATE TABLE cm_merchant_binding (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `merchant_user_id` int(11) NOT NULL COMMENT '商户所属用户中心ID',
  `merchant_id` int(11) NOT NULL COMMENT '商户所ID',
 `channel_user_id` int(11) NOT NULL COMMENT '渠道所属用户中心ID',
  `addtime` int(10) NOT NULL COMMENT '创建时间',
  `status` int(10) NOT NULL DEFAULT '0' COMMENT '0表示申请中，表示绑定成功，2表示驳回',
  PRIMARY KEY (id) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='商户绑定表';

alter table cm_merchant_binding add column `channel_account_id` int(11) NOT NULL COMMENT '渠道账户id';
alter table  cm_pay_center_channel_account add column `status` int(11) NOT NULL COMMENT '状态，是否绑定了商户';
ALTER TABLE cm_merchant_binding ADD COLUMN `en_able` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0禁用，1启用';
ALTER TABLE cm_merchant_binding ADD COLUMN `is_cancle` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0取消绑定，1未取消绑定';
ALTER TABLE cm_pay_channel ADD COLUMN `template_id` int(11) NOT NULL COMMENT '模板ID';
ALTER TABLE cm_pay_channel ADD COLUMN `pay_center_uid` int(11) NOT NULL COMMENT '支付中心用户ID';
ALTER TABLE cm_pay_center_user ADD COLUMN   `pid` int(11) NOT NULL DEFAULT '0' COMMENT '上级代理用户';
ALTER TABLE cm_orders ADD COLUMN `pay_center_uid` int(11) NOT NULL COMMENT '支付中心商户id';


INSERT INTO cm_menu(`pid`, `sort`, `name`, `module`, `url`, `is_hide`, `icon`, `status`, `update_time`,  `create_time`)
  VALUES('0' , '100', '支付中心管理', 'admin', 'Paycenteruser', '0', '',  '1', '1652878001', '1652878001');

INSERT INTO cm_menu(`pid`, `sort`, `name`, `module`, `url`, `is_hide`, `icon`, `status`, `update_time`,  `create_time`)
  VALUES('146' , '100', '用户列表', 'admin', 'Paycenteruser/index', '0', '',  '1', '1652878001', '1652878001');

alter table cm_orders add column `user_agent_uid` int(11) unsigned not null default '0' comment '商户所属支付中心用户代理';
alter table cm_orders add column `channel_agent_uid` int(11) unsigned not null default '0' comment '渠道所属支付中心用户代理';

CREATE TABLE `cm_pay_center_bill` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int(11) NOT NULL COMMENT '支付中心用户ID',
  `jl_class` int(11) NOT NULL COMMENT '流水类别：1渠道收益2商户收益3代理收益',
  `info` varchar(225) NOT NULL COMMENT '说明',
  `jc_class` varchar(225) NOT NULL COMMENT '分+ 或-',
  `pre_amount` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '变化前',
  `last_amount` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '变化后',
  `addtime` varchar(225) NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='支付中心用户流水账单';

alter table cm_pay_center_user add column `money` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户余额';

CREATE TABLE `cm_pay_center_bill` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int(11) NOT NULL COMMENT '支付中心用户ID',
  `jl_class` int(11) NOT NULL COMMENT '流水类别：1渠道收益2商户收益3代理收益',
  `info` varchar(225) NOT NULL COMMENT '说明',
  `jc_class` varchar(225) NOT NULL COMMENT '分+ 或-',
  `pre_amount` decimal(11,3) NOT NULL DEFAULT '0.000' COMMENT '变化前',
  `last_amount` decimal(11,3) NOT NULL DEFAULT '0.000' COMMENT '变化后',
  `change_amount` decimal(11,3) NOT NULL DEFAULT '0.000' COMMENT '变动金额',
  `create_time` int(11) NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='支付中心用户流水账单';

alter table cm_pay_center_user add column `money` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '用户余额';

alter table cm_pay_center_user add column `is_info_public` tinyint(1) NOT NULL DEFAULT 0 COMMENT '公开信息 0否 1是';
alter table cm_pay_center_user add column `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像';

insert into  cm_channel_template (`name`, `port_address`, `params`, `class_name`, `create_time`)
values ('小小帮支付', 'http://wx.xiaoxiaobang.cn/pay/', '', 'XiaoxiaobangPay', unix_timestamp(now()));
insert into  cm_channel_template (`name`, `port_address`, `params`, `class_name`, `create_time`)
values ('惠通支付', 'http://2203114680.swz.huitong1688.cn/api/createOrder', '', 'HuitongPay', unix_timestamp(now()));
insert into  cm_channel_template (`name`, `port_address`, `params`, `class_name`, `create_time`)
values ('快快付支付', 'http://jin.canaskme.com/Pay_Index.html', '', 'KuaikuaifuPay', unix_timestamp(now()));
insert into  cm_channel_template (`name`, `port_address`, `params`, `class_name`, `create_time`)
values ('H支付', 'http://xxx.9i608o3.cn/api/payinfos', '', 'HzhifuPay', unix_timestamp(now()));
insert into  cm_channel_template (`name`, `port_address`, `params`, `class_name`, `create_time`)
values ('大大支付', 'http://www.y01pay.com/Pay_Index.html', '', 'DadaPay', unix_timestamp(now()));
insert into  cm_channel_template (`name`, `port_address`, `params`, `class_name`, `create_time`)
values ('宇宙支付', 'http://pay.meloqiao.com/lpay/pay/gateway', '', 'YuzhouPay', unix_timestamp(now()));
insert into  cm_channel_template (`name`, `port_address`, `params`, `class_name`, `create_time`)
values ('太阳支付', 'http://a.sunfu.store/api/pay', '', 'TaiyangPay', unix_timestamp(now()));
insert into  cm_channel_template (`name`, `port_address`, `params`, `class_name`, `create_time`)
values ('赢乾支付', '/api/pay/unifiedorder', '', 'YinqianPay', unix_timestamp(now()));

insert into cm_menu(`pid`, `sort`, `name`, `module`, `url`, `is_hide`, `icon`, `status`, `update_time`, `create_time`)
values (29, 100, '模板列表','admin', 'Pay/channelTemplate', 0, '' , 1, unix_timestamp(now()), unix_timestamp(now()));

alter table cm_channel_template add column `status` int(10) NOT NULL DEFAULT 1 COMMENT '-1删除 0禁用 1启用';
alter table cm_channel_template add column `update_time` int(10) NOT NULL DEFAULT '' COMMENT '修改时间';
alter table cm_article add column `category_id` int(5) NOT NULL  COMMENT '文章分类';

insert into cm_config(`name`, `title`, `type`, `sort`, `group`, `value`, `extra`, `describe`, `status`, `create_time`, `update_time`)
values ('pay_address', '下单地址', 1, 0, 0, 'http://68.178.164.187:85/apis/order', '', '', 1, unix_timestamp(now()),unix_timestamp(now()));

insert into cm_config(`name`, `title`, `type`, `sort`, `group`, `value`, `extra`, `describe`, `status`, `create_time`, `update_time`)
values ('query_address', '查询地址', 1, 0, 0, 'http://68.178.164.187:85/apis/query', '', '', 1, unix_timestamp(now()),unix_timestamp(now()));


alter table cm_pay_center_user add column `is_need_google_verify` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否google验证';
alter table cm_pay_center_user add column `google_secret_key` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'google密钥';


CREATE TABLE `cm_bind_channel` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `merchant_user_id` int(11) NOT NULL COMMENT '商户所属用户中心ID',
  `channel_user_id` int(11) NOT NULL COMMENT '渠道所属用户中心ID',
  `user_id` int(11) NOT NULL COMMENT '商户ID',
  `channel_id` int(11) NOT NULL COMMENT '渠道ID',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '状态 0表示申请中，1表示绑定成功，2表示驳回',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_time` int(10) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='绑定渠道表';


CREATE TABLE `cm_pay_center_user_account` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pay_center_uid` int(11) NOT NULL COMMENT '所属支付中心用户',
  `user_id` int(11) NOT NULL COMMENT '商户标识',
  `channel_id` int(11) NOT NULL COMMENT '渠道标识',
  `bind_id` int(11) NOT NULL COMMENT '商户绑定渠道用户标识',
  `name` varchar(100) NOT NULL COMMENT '账号名称',
  `pay_merchant` varchar(100) NOT NULL COMMENT '商户号',
  `pay_secret` varchar(255) NOT NULL COMMENT '支付密钥',
  `extra_param` varchar(255) DEFAULT NULL COMMENT '额外参数',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态 -1删除 0禁用 1启用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='用户账号表';


CREATE TABLE `cm_guarantee_orders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `trade_no` varchar(32) NOT NULL COMMENT '订单号',
  `merchant_user_id` int(11) NOT NULL COMMENT '商户用户支付中心标识',
  `channel_user_id` int(11) NOT NULL COMMENT '渠道用户支付中心标识',
  `amount` decimal(10,2) NOT NULL COMMENT '担保金额',
  `usdt_sum` decimal(10,3) NOT NULL COMMENT '金额对应usdt数量',
  `status` tinyint(3) NOT NULL COMMENT '状态 0 订单关闭 1待支付 2支付成功',
  `pay_type` tinyint(3) DEFAULT '0' COMMENT '交易方式 0为交易 1余额转账 2usdt转账',
  `pay_arrival_time` int(11) DEFAULT '0' COMMENT 'usdt转账到账时间',
  `transaction_id` varchar(100) DEFAULT NULL COMMENT '转账交易ID',
  `from_transaction_address` varchar(255) DEFAULT NULL COMMENT '转入的地址',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


insert into cm_config(`name`, `title`, `type`, `sort`, `group`, `value`, `extra`, `describe`, `status`, `create_time`, `update_time`)
values ('usdt_rate', 'usdt费率', 1, 0, 0, '6.5', '', '', 1, unix_timestamp(now()),unix_timestamp(now()));

insert into cm_config(`name`, `title`, `type`, `sort`, `group`, `value`, `extra`, `describe`, `status`, `create_time`, `update_time`)
values ('usdt_address', 'usdt地址', 1, 0, 0, 'TB1VCPsLF9ekbM3D7whth8WDtRLiZoWpQa', '', '', 1, unix_timestamp(now()),unix_timestamp(now()));

alter table cm_guarantee_orders add column `effective_time` int(11) NOT NULL COMMENT '订单有效时间';
alter table cm_guarantee_orders add column `channel_id` int(11) NOT NULL COMMENT '支付渠道标识' AFTER `amount`;

alter table cm_bind_channel add column `channel_status` tinyint(3) NOT NULL COMMENT '渠道状态';
ALTER TABLE cm_bind_channel ADD COLUMN `en_able` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0禁用，1启用';

ALTER TABLE cm_pay_center_user ADD COLUMN `usdt_balance` decimal(5) DEFAULT 0 COMMENT 'usdt余额' after `money`;

CREATE TABLE `cm_pay_center_usdt_bill` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int(11) NOT NULL COMMENT '支付中心用户ID',
  `jl_class` int(11) NOT NULL COMMENT '流水类别：1USDT充值，2管理员账变',
  `info` varchar(225) NOT NULL COMMENT '说明',
  `jc_class` varchar(225) NOT NULL COMMENT '分+ 或-',
  `pre_amount` decimal(11,3) NOT NULL DEFAULT '0.000' COMMENT '变化前',
  `last_amount` decimal(11,3) NOT NULL DEFAULT '0.000' COMMENT '变化后',
  `change_amount` decimal(11,3) NOT NULL DEFAULT '0.000' COMMENT '变动金额',
  `create_time` int(11) NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='支付中心用户USDT流水账单';


ALTER TABLE cm_guarantee_orders ADD COLUMN `refund_time` int(10) DEFAULT 0 COMMENT '退款时间' after `pay_arrival_time`;

ALTER TABLE cm_guarantee_orders modify  COLUMN `status`  tinyint(3) NOT NULL COMMENT '状态 0 订单关闭 1待支付 2支付成功 3申请退保 4退保成功' ;

ALTER TABLE cm_guarantee_orders ADD COLUMN `channel_refund_note` varchar (255)  COMMENT '渠道退款描述' after `refund_time`;
ALTER TABLE cm_guarantee_orders ADD COLUMN `merchant_refund_note` varchar (255)  COMMENT '商户退款描述' after `refund_time`;

ALTER TABLE cm_pay_center_usdt_bill modify COLUMN `jl_class` int(11) NOT NULL COMMENT '流水类别：1USDT充值，2USDT体现，3管理员账变， 4担保订单支付，5担保订单退款';

insert into cm_config(`name`, `title`, `type`, `sort`, `group`, `value`, `extra`, `describe`, `status`, `create_time`, `update_time`)
values ('usdt_topup_withdraw_address', 'USDT充值提现地址', 1, 0, 0, 'TB1VCPsLF9ekbM3D7whth8WDtRLiZoWpQa', '', '', 1, unix_timestamp(now()),unix_timestamp(now()));