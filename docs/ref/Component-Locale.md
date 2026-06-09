# DuckPhp\Component\Locale
[toc]

## 简介
多语言支持

## 选项

无选项

## 方法
    
## 详解

    protected function getLanguage()

    public function lang($str, $args)

        'locale_lang_final' => null,

        'locale_lang_default' => null,

        'locale_lang_detect_mode' => ['url', 'cookie','header', 'cli','default'],

        'locale_lang_follow_root' => true,

        'locale_lang_url_param' => 'lang',

        'locale_lang_cookie_name' => 'lang',

        'local_lang_file_path' => 'lang/',

    public function init(array $options, ?object $context = null)

    protected function loadLanguage($str)

    protected function format($str, $args)

    protected function normalizeLocale(string $locale): string

    protected function detectLanguage(): ?string

    protected function detectFromUrl(): ?string

    protected function detectFromCookie(): ?string

    protected function detectFromHeader(): ?string

    protected function detectFromCli(): ?string

    protected function detectFromDefault(): ?string

