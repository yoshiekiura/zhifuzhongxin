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