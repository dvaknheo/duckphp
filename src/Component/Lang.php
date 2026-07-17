<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Logger;
use DuckPhp\Core\SuperGlobal;

class Lang extends ComponentBase
{
    // 配置只在 root 有效。 或者setting 里？
    // 如果配置无效，那么退回本层默认locale ，如果本层 locale 没有，那么就是空 locale.
    public $options = [
        // 最终语言，不再判断
        'lang_final' => null,
        // 默认语言
        'lang_default' => null,
        
        'lang_detect_mode' => ['url', 'cookie','header', 'cli','default'],
        
        //使用根 app 的语言
        'lang_follow_root' => true,
        // URL 参数名
        'lang_url_param' => 'lang',
        // Cookie 名称
        'lang_cookie_name' => 'lang',
        'lang_file_path' => 'lang/',
        'lang_simple_mode_only_sentences' => [],
    ];
    public function init(array $options, ?object $context = null)
    {
        parent::init($options, $context);
        if ($this->options['lang_follow_root'] && !App::_()->isRoot()) {
            $this->options['lang_final'] = App::Root()->options['lang_final'];
        } else {
            $this->options['lang_final'] = $this->detectLanguage();
        }
        $this->context()->options['lang_final'] = $this->options['lang_final'];
    }
    protected function getSentenceFromConfig(string $language): ?array
    {
        if (!empty($this->options['lang_simple_mode_only_sentences'])) {
            return $this->options['lang_simple_mode_only_sentences'][$language] ?? null;
        }
        $configs = Configer::_()->_Config($this->options['lang_file_path'].basename($language), null, null);
        return $configs;
    }
    protected function loadLanguage(string $str): ?string
    {
        $language = $this->options['lang_final'];
        if (!isset($language)) {
            //Logger::_()->warning("No Language Dectected");
            return null;
        }
        $configs = $this->getSentenceFromConfig($language);
        if (empty($configs)) {
            Logger::_()->warning("No Language sentences Dectected: $language");
            return null;
        }
        if (!isset($configs[$str])) {
            Logger::_()->warning("No Language sentence Dectected $str");
            return null;
        }
        return $configs[$str];
    }
    public function lang(string $str, array $args = []): string
    {
        $newstr = $this->loadLanguage($str);
        return $this->format($newstr ?? $str, $args);
    }
    protected function format(string $str, array $args): string
    {
        $a = [];
        foreach ($args as $k => $v) {
            $a["{".$k."}"] = $v;
        }
        $ret = str_replace(array_keys($a), array_values($a), $str);
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
    protected function detectLanguage(): ?string
    {
        $methods = [
            'url' => 'detectFromUrl',
            'cookie' => 'detectFromCookie',
            'header' => 'detectFromHeader',
            'cli' => 'detectFromCli',
            'default' => 'detectFromDefault',
        ];
        
        foreach ($this->options['lang_detect_mode'] as $method) {
            if (isset($methods[$method])) {
                $locale = $this->{$methods[$method]}();
                if ($locale !== null) {
                    return $this->normalizeLocale($locale);
                }
            }
        }
        return null;
    }
    /**
     * 从 URL 参数检测
     */
    protected function detectFromUrl(): ?string
    {
        $param = $this->options['lang_url_param'];
        $my_get = defined('__SUPERGLOBAL_CONTEXT')
            ? (SuperGlobal::_()->_GET ?? [])
            : ($_GET ?? []);
        
        return $my_get[$param] ?? null;
    }
    
    /**
     * 从 Cookie 检测
     */
    protected function detectFromCookie(): ?string
    {
        $name = $this->options['lang_cookie_name'];
        $my_cookie = defined('__SUPERGLOBAL_CONTEXT') ? (SuperGlobal::_()->_COOKIE ?? []) : ($_COOKIE ?? []);
        
        return $my_cookie[$name] ?? null;
    }
    
    /**
     * 从 HTTP Header 检测
     */
    protected function detectFromHeader(): ?string
    {
        $my_server = defined('__SUPERGLOBAL_CONTEXT') ? (SuperGlobal::_()->_SERVER ?? []) : ($_SERVER ?? []);
        
        $accept = $my_server['HTTP_ACCEPT_LANGUAGE'] ?? null;
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
            return $normalized;
        }
        
        return null;  // @codeCoverageIgnore
    }
    
    /**
     * 从 CLI 环境检测
     */
    protected function detectFromCli(): ?string
    {
        if (PHP_SAPI !== 'cli') {
            return null; // @codeCoverageIgnore
        }
        
        // 尝试从环境变量获取
        $lang = getenv('LANG') ?: getenv('LC_ALL') ?: getenv('LC_MESSAGES') ?: getenv('LANGUAGE');
        if ($lang) {
            // 格式通常为: zh_CN.UTF-8 或 en_US
            $lang = explode('.', $lang)[0]; // 移除 .UTF-8
            $normalized = $this->normalizeLocale($lang);
            return $normalized;
        }
        
        return null; // @codeCoverageIgnore
    }
    
    /**
     * 默认语言
     */
    protected function detectFromDefault(): ?string
    {
        return $this->options['lang_default'];
    }
}
