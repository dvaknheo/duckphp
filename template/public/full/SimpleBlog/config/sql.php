<?php return array (
  'scheme' => 
  array (
    'ActionLogs' => 'CREATE TABLE `ActionLogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contents` text COLLATE utf8_bin NOT NULL,
  `type` varchar(250) COLLATE utf8_bin NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin',
    'Articles' => 'CREATE TABLE `Articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(250) COLLATE utf8_bin NOT NULL,
  `content` text COLLATE utf8_bin NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT=\'文章表\'',
    'Comments' => 'CREATE TABLE `Comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT \'自增ID\',
  `article_id` int(11) NOT NULL COMMENT \'话题ID，关联其他表\',
  `user_id` int(11) NOT NULL COMMENT \'用户ID\',
  `content` text COLLATE utf8_bin NOT NULL COMMENT \'评论内容\',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `topic_id` (`article_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin',
    'Settings' => 'CREATE TABLE `Settings` (
  `k` varchar(250) COLLATE utf8_bin NOT NULL,
  `v` varchar(250) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`k`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT=\'设置表\'',
    'Users' => 'CREATE TABLE `Users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) COLLATE utf8_bin NOT NULL,
  `password` varchar(64) COLLATE utf8_bin NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT=\'用户表\'',
    'test' => 'CREATE TABLE `test` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT \'ID\',
  `content` varchar(250) NOT NULL COMMENT \'内容\',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
  ),
  'data' => 
  array (
  ),
);
