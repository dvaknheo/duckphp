<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\Business;

use SimpleBlog\Helper\BusinessHelper as B;
use SimpleBlog\System\App;

class InstallBusiness extends BaseBusiness
{
    public function checkInstall()
    {
        if(App::Setting('simple_blog_installed')){
            return true;
        }
        return false;
    }
    public function install($database)
    {
        //extract($database);
        $setting = App::LoadConfig('setting');
        $database = [
            'dsn' => "mysql:host={$database['host']};port={$database['port']};dbname={$database['dbname']};charset=utf8mb4;",
            'username' => $database['username'],
            'password' => $database['password'],
            'driver_options' => [],
        ];
        //接下来连接数据库
        try{
            App::G()->checkDb($database);
            App::Db()->execute($this->getSqlForStruct());
            App::Db()->execute($this->getSqlForData());
        }catch(\Exception $ex){
            BusinessException::ThrowOn(true, "安装数据库失败" . $ex->getMessage(),-1);
        }
        $setting['database'] = $database;
        $setting['simple_blog_installed'] = DATE(DATE_ATOM);
        
        $flag = App::G()->writeSettingFile($setting);
        BusinessException::ThrowOn(!$flag,'写入文件失败',-2);
        
        return true;
    }
    
    protected function getSqlForStruct()
    {
        //这里从配置读
        return <<<EOT
DROP TABLE IF EXISTS `ActionLogs`;
CREATE TABLE `ActionLogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contents` text COLLATE utf8_bin NOT NULL,
  `type` varchar(250) COLLATE utf8_bin NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `Articles`;
CREATE TABLE `Articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(250) COLLATE utf8_bin NOT NULL,
  `content` text COLLATE utf8_bin NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='文章表';

DROP TABLE IF EXISTS `Comments`;
CREATE TABLE `Comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `article_id` int(11) NOT NULL COMMENT '话题ID，关联其他表',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `content` text COLLATE utf8_bin NOT NULL COMMENT '评论内容',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `topic_id` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `Settings`;
CREATE TABLE `Settings` (
  `k` varchar(250) COLLATE utf8_bin NOT NULL,
  `v` varchar(250) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`k`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='设置表';

DROP TABLE IF EXISTS `Users`;
CREATE TABLE `Users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) COLLATE utf8_bin NOT NULL,
  `password` varchar(64) COLLATE utf8_bin NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='用户表';

DROP TABLE IF EXISTS `test`;
CREATE TABLE `test` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `content` varchar(250) NOT NULL COMMENT '内容',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

EOT;
    }
    protected function getSqlForData()
    {
    //这里从配置读
        return <<<'EOT'
INSERT INTO `ActionLogs` VALUES (1,'更改 1','编辑文章','2018-05-21 08:27:04'),(2,'管理员登录','管理员登录','2018-05-31 16:15:06'),(3,'管理员登录','管理员登录','2018-05-31 16:15:44'),(4,'管理员登录','管理员登录','2018-06-05 03:39:35'),(5,'管理员登录','管理员登录','2018-06-05 03:39:43'),(6,'管理员登录','管理员登录','2018-06-05 03:39:50'),(7,'管理员登录','管理员登录','2018-06-05 03:41:58'),(8,'管理员登录','管理员登录','2018-06-05 03:42:25'),(9,'管理员登录','管理员登录','2018-06-05 03:43:52'),(10,'a 注册','reg','2018-06-11 02:50:10'),(11,'管理员登录成功','管理员登录','2018-07-17 15:49:15'),(12,'管理员登录成功','管理员登录','2018-07-17 15:51:53'),(13,'管理员登录成功','管理员登录','2018-07-17 15:53:44'),(14,'管理员登录成功','管理员登录','2018-07-18 01:51:01'),(15,'管理员登录成功','管理员登录','2018-07-18 01:51:22'),(16,'管理员登录成功','管理员登录','2018-07-18 01:52:00'),(17,'管理员登录成功','管理员登录','2018-07-18 01:52:36'),(18,'管理员登录成功','管理员登录','2018-07-18 01:53:03'),(19,'管理员登录成功','管理员登录','2018-07-18 01:56:28'),(20,'管理员登录成功','管理员登录','2018-07-18 02:04:01'),(21,'添加文章 ','添加文章','2018-07-18 02:04:09'),(22,'添加文章 ','添加文章','2018-07-18 02:04:14'),(23,'管理员登录成功','管理员登录','2018-07-18 08:56:24'),(24,'管理员登录成功','管理员登录','2018-07-18 09:46:36'),(25,'管理员登录成功','管理员登录','2018-07-18 13:02:26'),(26,'管理员登录成功','管理员登录','2018-07-18 13:02:29'),(27,'管理员登录成功','管理员登录','2018-07-18 13:02:33'),(28,'管理员登录成功','管理员登录','2018-07-18 13:02:35'),(29,'管理员登录成功','管理员登录','2018-07-18 13:05:21'),(30,'管理员登录成功','管理员登录','2018-07-18 13:07:04'),(31,'管理员登录成功','管理员登录','2018-07-18 13:11:25'),(32,'管理员登录成功','管理员登录','2018-07-18 13:16:48'),(33,'管理员登录成功','管理员登录','2018-07-18 13:16:50'),(34,'管理员登录成功','管理员登录','2018-07-18 13:17:18'),(35,'管理员登录成功','管理员登录','2018-07-18 13:17:44'),(36,'管理员登录成功','管理员登录','2018-07-18 13:17:46'),(37,'管理员登录成功','管理员登录','2018-07-18 13:17:51'),(38,'管理员登录成功','管理员登录','2018-07-18 13:19:32'),(39,'管理员登录成功','管理员登录','2018-07-18 13:19:36'),(40,'管理员登录成功','管理员登录','2018-07-18 13:20:09'),(41,'管理员登录成功','管理员登录','2018-07-18 13:20:13'),(42,'管理员登录成功失败','管理员登录','2018-07-19 14:09:03'),(43,'管理员登录成功','管理员登录','2018-07-19 14:09:07'),(44,'添加文章 ','添加文章','2018-07-19 14:09:47'),(45,'添加文章 ','添加文章','2018-07-19 14:09:53'),(46,'编辑 ID 为 5,原标题，原内容，更改后标题，更改后内容','编辑文章','2018-07-19 14:16:52'),(47,'编辑 ID 为 6,原标题，原内容，更改后标题，更改后内容','编辑文章','2018-07-19 14:16:57'),(48,'编辑 ID 为 6,原标题，原内容，更改后标题，更改后内容','编辑文章','2018-07-19 14:17:00'),(49,'编辑 ID 为 6,原标题，原内容，更改后标题，更改后内容','编辑文章','2018-07-19 14:17:03'),(50,'t1 注册','reg','2018-07-23 02:10:14'),(51,'t2 注册','reg','2018-07-24 14:34:51'),(52,'t2 评论成功','','2018-07-24 14:45:31'),(53,'t2 评论成功','','2018-07-24 14:45:39'),(54,'t2 评论成功','','2018-07-24 14:45:43'),(55,'t2 评论成功','','2018-07-24 15:00:58'),(56,'t2 评论成功','','2018-07-24 15:26:04');

INSERT INTO `Articles` VALUES (1,'aa','cc','2018-05-21 08:13:17','2018-05-21 08:27:04',NULL),(2,'aa','cc','2018-05-21 08:14:05','2018-05-21 08:21:46','2018-05-21 08:19:36'),(3,'dfasfsdf','dfsdafdfdsaf','2018-07-18 02:04:09','2018-07-18 02:04:09',NULL),(4,'111','222222','2018-07-18 02:04:14','2018-07-18 02:04:14',NULL),(5,'11111','222222222222','2018-07-19 14:09:47','2018-07-19 14:16:52',NULL),(6,'aaaddd','bbbb1111','2018-07-19 14:09:53','2018-07-19 14:17:03',NULL);

INSERT INTO `Comments` VALUES (1,6,5,'dafsdf','2018-07-24 14:45:31','2018-07-24 14:45:31',NULL),(2,6,5,'1111','2018-07-24 14:45:39','2018-07-24 14:45:39',NULL),(3,6,5,'fdfdsafdsf','2018-07-24 14:45:43','2018-07-24 14:45:43',NULL),(4,6,5,'<b>xx</b>','2018-07-24 15:00:58','2018-07-24 15:00:58',NULL),(5,6,5,'aa\r\nbb','2018-07-24 15:26:04','2018-07-24 15:26:04',NULL);

INSERT INTO `Settings` VALUES ('admin_password','$2y$10$Yi2XxaJDGpOTa6RuSbV6ceWVij0Ntl6x3/PkheZNK5cKTlXKj0bY6');

INSERT INTO `Users` VALUES (1,'aa','$2y$10$HEnsqIcNWAYGiyvwVnJGo.IIogl2X0YQL7JlTuKRYcN.Z/rhvMHI2',NULL,NULL,NULL),(2,'abc','$2y$10$G25OLpq5SRPxR4kFv.ZIwegESIyoEka/j6MV1OPjGzCb6hi5kOtbW',NULL,NULL,NULL),(3,'a','$2y$10$HKlThhmUWgEjLbm1.3qhFOa0Xq.1QXigAfT/7UZT6HUwg7c08UfP.','2018-06-11 02:50:10',NULL,NULL),(4,'t1','$2y$10$xYPQEeIw/V9ITux.EvOr0uCfA5caLew5guCytERcwx4SnuCgwmaxG','2018-07-23 02:10:14',NULL,NULL),(5,'t2','$2y$10$p3lYbiPJuQPnrH6IbhSzFujIFskchrdqjuF8Qs6SnTyCy5KOHrueW','2018-07-24 14:34:51',NULL,NULL);

EOT;
'EOT';
    }
}