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
