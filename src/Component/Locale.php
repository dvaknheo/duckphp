<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\ComponentBase;

class Locale extends ComponentBase
{
    public $options = [
        // 默认语言
        'locale_default' => 'zh_CN',
        // 语言文件目录
        'locale_path' => 'config/locale/',
        
        // 启用的语言列表
        'locale_enabled' => ['zh_CN', 'en_US'],
        
        // 语言检测方式优先级
        'locale_detect_order' => ['url', 'cookie', 'header', 'cli', 'default'],
        // URL 参数名
        'locale_url_param' => 'lang',
        // Cookie 名称
        'locale_cookie_name' => 'duckphp_locale',
        
    ];
    
    protected function getLanguage()
    {
        //App::Root()->options['default_language'];
        $_SERVER = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
        $accept = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
        return "";
        //$lang = getenv('LANG') ?: getenv('LC_ALL') ?: getenv('LC_MESSAGES');
        //zh-CN, zh;q=0.9, en;q=0.8, en-GB;q=0.7, en-US;q=0.6
        // from CLI
    }
    public function lang($str, $args)
    {
        $language = $this->getLanguage();
        $newstr = Configer::_()->_Config("lang/$language", $str, null);
        
        //
        if (!isset($str)) {
            $newstr = $str;
        }
        
        $a = [];
        foreach ($args as $k => $v) {
            $a["{".$k."}"] = $v;
        }
        $ret = str_replace(array_keys($a), array_values($a), $newstr);
        return $ret;
    }
}
/*
// 获取系统语言环境（Linux/macOS）
$lang = getenv('LANG') ?: getenv('LC_ALL') ?: getenv('LC_MESSAGES');
if ($lang) {
    $languageCode = strtolower(substr(trim($lang), 0, 2));
    echo "系统语言环境: " . $languageCode . "\n";
} else {
    // Windows 系统或其他情况
    $lang = getenv('LANGUAGE') ?: getenv('LANG');
    if ($lang) {
        $languageCode = strtolower(substr(trim($lang), 0, 2);
        echo "系统语言环境: " . $languageCode . "\n";
    } else {
        echo "无法检测系统语言。\n";
    }
}

if (extension_loaded('intl')) {
    $defaultLocale = Locale::getDefault();
    $languageCode = strtolower(substr($defaultLocale, 0, 2));
    echo "PHP 默认区域设置: " . $languageCode . "\n";
} else {
    echo "intl 扩展未安装，无法获取语言信息。\n";
}
*/
