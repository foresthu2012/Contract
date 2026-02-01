DROP TABLE IF EXISTS `{{prefix}}addon_contract_contract`;
CREATE TABLE `{{prefix}}addon_contract_contract` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(11) NOT NULL DEFAULT '0' COMMENT '站点id',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '合同标题',
  `content` text COMMENT '合同内容(富文本)',
  `file_path` varchar(255) NOT NULL DEFAULT '' COMMENT '合同文件路径',
  `member_id` int(11) NOT NULL DEFAULT '0' COMMENT '会员id',
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联订单ID',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0待签署 1已签署',
  `sign_image` varchar(255) NOT NULL DEFAULT '' COMMENT '签名图片路径',
  `sign_time` int(11) NOT NULL DEFAULT '0' COMMENT '签署时间',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='合同表';
