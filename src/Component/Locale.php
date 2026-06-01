<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\ComponentBase;

class Locale extends ComponentBase
{
// 配置只在 root 有效。 或者setting 里？
// 如果配置无效，那么退回本层默认locale ，如果本层 locale 没有，那么就是空 locale.
    public $options = [
		// 最终语言，不再判断
		'locale_lang_final' => null,
        // 默认语言
        'locale_lang_default' => null,
		
		'locale_lang_detect_mode' => ['url', 'cookie','header', 'cli','default'],
		
		//使用根 app 的语言
		'locale_lang_follow_root' => true,
        // URL 参数名
        'locale_lang_url_param' => 'lang',
        // Cookie 名称
        'locale_lang_cookie_name' => 'lang',
    ];
    protected function initOptions(array $options)
    {
		if (isset(App::_()->options['locale_lang_final'])){
			return;
		}
		if(!App::IsRoot() && $this->options['locale_lang_follow_root']) {
			App::_()->options['locale_lang_final'] = App::Root()->options['locale_lang_final'];
		}else{
			App::_()->options['locale_lang_final'] = $this->getLanguage();
		}
    }
    protected function getLanguage()
    {
		//detectLocale
    }
    public function lang($str, $args)
    {
        $language = $this->options['locale_lang_final'];
		
        $newstr = Configer::_()->_Config("lang/$language", $str, null);
		
		// 如果我们没有得到 lang 文件，那么退化成默认 lang
        
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
	///////////////////////////////////////////////
    /**
     * 标准化语言代码
     */
    protected function normalizeLocale(string $locale): string
    {
        // 将 zh-cn, zh-CN, zh_cn 统一为 zh_CN
        $locale = str_replace('-', '_', $locale);
        $parts = explode('_', $locale);
        if (isset($parts[0])) {
            $parts[0] = strtolower($parts[0]);
        }
        if (isset($parts[1])) {
            $parts[1] = strtoupper($parts[1]);
        }
        return implode('_', $parts);
    }
	
    /**
     * 自动检测语言
     */
    protected function detectLocale(): string
    {
        $methods = [
            'url' => 'detectFromUrl',
            'cookie' => 'detectFromCookie',
            'header' => 'detectFromHeader',
            'cli' => 'detectFromCli',
            'default' => 'detectFromDefault',
        ];
        
        foreach ($this->options['locale_detect_order'] as $method) {
            if (isset($methods[$method])) {
                $locale = $this->{$methods[$method]}();
                if ($locale !== null && $this->isValidLocale($locale)) {
                    $this->detectedLocale = $this->normalizeLocale($locale);
                    return $this->detectedLocale;
                }
            }
        }
        
        $this->detectedLocale = $this->options['locale_default'];
        return $this->detectedLocale;
    }
	/**
     * 从 URL 参数检测
     */
    protected function detectFromUrl(): ?string
    {
        $param = $this->options['locale_url_param'];
        $_GET = defined('__SUPERGLOBAL_CONTEXT') 
            ? (SuperGlobal::_()->_GET ?? []) 
            : ($_GET ?? []);
        
        return $_GET[$param] ?? null;
    }
    
    /**
     * 从 Cookie 检测
     */
    protected function detectFromCookie(): ?string
    {
        $name = $this->options['locale_cookie_name'];
        $_COOKIE = defined('__SUPERGLOBAL_CONTEXT')
            ? (SuperGlobal::_()->_COOKIE ?? [])
            : ($_COOKIE ?? []);
        
        return $_COOKIE[$name] ?? null;
    }
    
    /**
     * 从 HTTP Header 检测
     */
    protected function detectFromHeader(): ?string
    {
        $_SERVER = defined('__SUPERGLOBAL_CONTEXT')
            ? (SuperGlobal::_()->_SERVER ?? [])
            : ($_SERVER ?? []);
        
        $accept = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
        if (!$accept) {
            return null;
        }
        
        // 解析 Accept-Language
        // 格式: zh-CN,zh;q=0.9,en;q=0.8
        $languages = [];
        $parts = explode(',', $accept);
        
        foreach ($parts as $part) {
            $part = trim($part);
            if (strpos($part, ';') !== false) {
                list($lang, $q) = explode(';', $part, 2);
                $q = (float) str_replace('q=', '', $q);
            } else {
                $lang = $part;
                $q = 1.0;
            }
            $languages[trim($lang)] = $q;
        }
        
        // 按优先级排序
        arsort($languages);
        
        // 查找第一个匹配的语言
        foreach ($languages as $lang => $q) {
            // 标准化语言代码
            $normalized = $this->normalizeLocale($lang);
            if ($this->isValidLocale($normalized)) {
                return $normalized;
            }
            // 尝试匹配主语言 (如 zh-CN -> zh)
            $primary = explode('_', $normalized)[0];
            foreach ($this->options['locale_enabled'] as $enabled) {
                if (strpos($enabled, $primary) === 0) {
                    return $enabled;
                }
            }
        }
        
        return null;
    }
    
    /**
     * 从 CLI 环境检测
     */
    protected function detectFromCli(): ?string
    {
        if (PHP_SAPI !== 'cli') {
            return null;
        }
        
        // 尝试从环境变量获取
        $lang = getenv('LANG') ?: getenv('LC_ALL') ?: getenv('LC_MESSAGES') ?: getenv('LANGUAGE');
        if ($lang) {
            // 格式通常为: zh_CN.UTF-8 或 en_US
            $lang = explode('.', $lang)[0]; // 移除 .UTF-8
            $normalized = $this->normalizeLocale($lang);
            if ($this->isValidLocale($normalized)) {
                return $normalized;
            }
        }
        
        return null;
    }
    
    /**
     * 默认语言
     */
    protected function detectFromDefault(): ?string
    {
        return $this->options['locale_lang_default'];
    }
}

