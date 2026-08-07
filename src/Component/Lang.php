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
    // Configuration only takes effect at root. Or in setting?
    // If invalid, fall back to this layer's default locale; if this layer has none, use empty locale.
    public $options = [
        // final language, no more detection
        'lang_final' => null,
        // default language
        'lang_default' => null,

        'lang_detect_mode' => ['url', 'cookie','header', 'cli','default'],

        // use root app's language
        'lang_follow_root' => true,
        // URL parameter name
        'lang_url_param' => 'lang',
        // Cookie name
        'lang_cookie_name' => 'lang',
        'lang_file_path' => 'lang/',
        'lang_simple_mode_only_sentences' => [],
    ];
    /**
     * @param array<string, mixed> $options
     * @param object|null $context
     * @return $this
     */
    public function init(array $options, ?object $context = null)
    {
        parent::init($options, $context);
        if ($this->options['lang_follow_root'] && !App::_()->isRoot()) {
            $this->options['lang_final'] = App::Root()->options['lang_final'];
        } else {
            $this->options['lang_final'] = $this->detectLanguage();
        }
        $this->context()->options['lang_final'] = $this->options['lang_final'];
        return $this;
    }
    /**
     * @return array<string, mixed>|null
     */
    protected function getSentenceFromConfig(string $language): ?array
    {
        if (!empty($this->options['lang_simple_mode_only_sentences'])) {
            return $this->options['lang_simple_mode_only_sentences'][$language] ?? null;
        }
        $configs = Configer::_()->_Config($this->options['lang_file_path'].basename($language), null, null);
        return $configs;
    }
    protected function loadLanguage(string $str, ?string $fallback = null): ?string
    {
        $language = $this->options['lang_final'];
        if (!isset($language)) {
            return $fallback;
        }
        $configs = $this->getSentenceFromConfig($language);
        if (empty($configs)) {
            if ($fallback === null) {
                Logger::_()->warning("No Language sentences Dectected: $language");
            }
            return $fallback;
        }
        if (!isset($configs[$str])) {
            if ($fallback === null) {
                Logger::_()->warning("No Language sentence Dectected $str");
            }
            return $fallback;
        }
        return $configs[$str];
    }
    /**
     * @param array<string, mixed> $args
     */
    public function language(string $str, array $args = [], ?string $fallback = null): string
    {
        $newstr = $this->loadLanguage($str, $fallback);
        return $this->format($newstr ?? $str, $args);
    }
    /**
     * Replace all {{lang_key|fallback}} / {{lang_key}} placeholders in text (partial match).
     * fallback may contain {word} blocks, e.g. {{some_key|just {myword}}}.
     * @param array<string, mixed> $args
     */
    public function replaceText(string $text, array $args = []): string
    {
        return preg_replace_callback('/\{\{([^{}|]+)(?:\|((?:[^{}]|\{[^}]*\})*))?\}\}/', function ($m) use ($args) {
            $key = $m[1];
            $fallback = $m[2] ?? null;
            return $this->language($key, $args, $fallback);
        }, $text);
    }
    /**
     * @param array<string, mixed> $args
     */
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
     * Normalize the locale code
     */
    protected function normalizeLocale(string $locale): string
    {
        // Unify zh-cn, zh-CN, zh_cn to zh_CN
        $locale = str_replace('-', '_', $locale);
        $parts = explode('_', $locale);
        $parts[0] = strtolower($parts[0]);
        if (isset($parts[1])) {
            $parts[1] = strtoupper($parts[1]);
        }
        return implode('_', $parts);
    }

    /**
     * Auto detect language
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
     * Detect from URL parameter
     */
    protected function detectFromUrl(): ?string
    {
        $param = $this->options['lang_url_param'];
        $my_get = defined('__SUPERGLOBAL_CONTEXT')
            ? (SuperGlobal::_()->_GET ?? [])
            : $_GET;

        return $my_get[$param] ?? null;
    }

    /**
     * Detect from Cookie
     */
    protected function detectFromCookie(): ?string
    {
        $name = $this->options['lang_cookie_name'];
        $my_cookie = defined('__SUPERGLOBAL_CONTEXT') ? (SuperGlobal::_()->_COOKIE ?? []) : $_COOKIE;

        return $my_cookie[$name] ?? null;
    }

    /**
     * Detect from HTTP Header
     */
    protected function detectFromHeader(): ?string
    {
        $my_server = defined('__SUPERGLOBAL_CONTEXT') ? (SuperGlobal::_()->_SERVER ?? []) : $_SERVER;

        $accept = $my_server['HTTP_ACCEPT_LANGUAGE'] ?? null;
        if (!$accept) {
            return null;
        }

        // Parse Accept-Language
        // Format: zh-CN,zh;q=0.9,en;q=0.8
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

        // Sort by priority
        arsort($languages);

        // Find the first matching language
        foreach ($languages as $lang => $q) {
            // Normalize the locale code
            $normalized = $this->normalizeLocale($lang);
            return $normalized;
        }
    } // @codeCoverageIgnore

    /**
     * Detect from CLI environment
     */
    protected function detectFromCli(): ?string
    {
        if (PHP_SAPI !== 'cli') {
            return null; // @codeCoverageIgnore
        }

        // Try to get from environment variables
        $lang = getenv('LANG') ?: getenv('LC_ALL') ?: getenv('LC_MESSAGES') ?: getenv('LANGUAGE');
        if ($lang) {
            // Format is usually: zh_CN.UTF-8 or en_US
            $lang = explode('.', $lang)[0]; // strip .UTF-8
            $normalized = $this->normalizeLocale($lang);
            return $normalized;
        }

        return null; // @codeCoverageIgnore
    }

    /**
     * Default language
     */
    protected function detectFromDefault(): ?string
    {
        return $this->options['lang_default'];
    }
}
