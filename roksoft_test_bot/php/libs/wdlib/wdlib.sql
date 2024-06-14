CREATE TABLE IF NOT EXISTS `wdlib_config` (
	`key` varchar(128) NOT NULL DEFAULT '',
	`value` blob NOT NULL,
	`is_server` tinyint(3) unsigned NOT NULL DEFAULT 0,
	`comment` text NOT NULL,
	PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table if not exists `wdlib_user_main` (
	`id` bigint unsigned not null auto_increment,
	`name` varchar(256) not null default '',
	`age` tinyint unsigned not null default 0,
	`sex` tinyint unsigned not null default 0,
	`pic` varchar(512) NOT NULL DEFAULT '',
	`flags` int unsigned not null default 0,
	`level` int unsigned not null default 0,
	`reg_date` int unsigned not null default 0,
	`last_date` int unsigned not null default 0,
	`last_ip` int unsigned not null default 0,
	`reg_ip` int unsigned not null default 0,
	`last_api_platform` tinyint unsigned not null default 0,
	`last_api_user_id` bigint unsigned not null default 0,
	`big_pic` varchar(512) not null default '',
	`city` varchar(256) not null default '',
	`anketa_link` varchar(512) not null default '',
	`birthday` varchar(256) not null default '',
	`birthday_date` int unsigned not null default 0,
	`zodiac` tinyint unsigned not null default 0,
	primary key (`id`)
) engine=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

create table if not exists `wdlib_user_auth` (
	`id` bigint unsigned not null default 0,
	`login` varchar(128) not null default '',
	`email` varchar(128) not null default '',
	`password` varchar(128) not null default '',
	primary key (`id`),
	unique index `Index_1` (`login`)
) engine=InnoDB default charset=utf8;

create table if not exists `wdlib_user_api_auth` (
	`platform` tinyint unsigned not null default 0,
	`api_user_id` bigint unsigned not null default 0,
	`user_id` bigint unsigned not null default 0,
	`is_app_user` tinyint unsigned not null default 0,
	`reg_date` int unsigned not null default 0,
	`last_date` int unsigned not null default 0,
	`last_ip` int unsigned not null default 0,
	`reg_ip` int unsigned not null default 0,
	primary key `Index_1` (`platform`, `api_user_id`),
	index `Index_1` (`user_id`)
) engine=InnoDB default charset=utf8;

create table if not exists `wdlib_user_confirm` (
	`key` varchar(64) not null default '',
	`id` bigint unsigned not null default 0,
	`type` tinyint unsigned not null default 0,
	`status` tinyint unsigned not null,
	`date` int unsigned not null default 0,
	primary key (`key`),
	index `Index_1` (`id`, `type`)
) engine=InnoDB default charset=utf8;

create table if not exists `wdlib_user_auth_session` (
	`key` varchar(128) not null default '',
	`user_id` bigint unsigned not null default 0,
	`fingerprint` varchar(128) not null default '',
	`data` blob not null,
	`date` int unsigned not null default 0,
	`status` tinyint unsigned not null default 0,
	`ip` int unsigned not null default 0,
	primary key (`key`),
	index `Index_1` (`user_id`)
) engine=InnoDB default charset=utf8;

create table if not exists `wdlib_user_info` (
	`user_id` bigint unsigned not null default 0,
	`key` tinyint unsigned not null default 0,
	`val` bigint not null default 0,
	`date` bigint unsigned not null default 0,
	primary key (`user_id`, `key`)
) engine=InnoDB default charset=utf8;

create table if not exists `wdlib_email_queue` (
	`id` int unsigned not null auto_increment,
	`to` varchar(256) not null default '',
	`type` tinyint unsigned not null default 0,
	`status` tinyint unsigned not null default 0,
	`subject` varchar(256) not null default '',
	`body` text not null,
	`data` blob not null,
	`date` int unsigned not null,
	primary key (`id`),
	index `Index_1` (`to`, `type`),
	index `Index_2` (`status`, `date`)
) engine=InnoDB default charset=utf8;

create table if not exists `wdlib_stats_app_params` (
	`date` int unsigned not null default 0,
	`platform` tinyint unsigned not null default 0,
	`referrer` varchar(256) not null default '',
	`params` blob not null,
	primary key (`platform`, `date`, `referrer`)
) engine=InnoDB default charset=utf8;

create table if not exists `wdlib_user_payments` (
	`seq_id` int unsigned not null auto_increment,
	`user_id` bigint unsigned not null default 0,
	`platform` tinyint unsigned NOT NULL DEFAULT 0,
	`api_user_id` bigint unsigned NOT NULL DEFAULT 0,
	`platform_order_id` varchar(255) not null default '',
	`status` tinyint not null default 0,
	`amount` int not null default 0,
	`wallet_type` tinyint unsigned not null default 0,
	`wallet_count` int not null default 0,
	`date` int unsigned not null default 0,
	`data` blob not null,
	primary key (`seq_id`),
	index `Index_1` (`user_id`, `date`),
	index `Index_2` (`platform`, `platform_order_id`)
) engine=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
