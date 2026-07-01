# DuckPhp\Component\Lang
[toc]

## 简介
多语言支持

## 选项
        'lang_final' => null,

        'lang_default' => null,

        'lang_detect_mode' => ['url', 'cookie','header', 'cli','default'],

        'lang_follow_root' => true,

        'lang_url_param' => 'lang',

        'lang_cookie_name' => 'lang',

        'lang_file_path' => 'lang/',
		'lang_simple_mode_only_sentences'=>[],


## 方法
    
## 详解

    protected function getLanguage()

    public function lang($str, $args)


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


	protected function getSentenceFromConfig($language)

    public function lang($str, $args = [])

        'lang_simple_mode_only_sentences' => [],

    protected function getSentenceFromConfig($language)

