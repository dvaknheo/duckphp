<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\ComponentBase;

class Pager extends ComponentBase implements PagerInterface
{
    public $options = [
        'url' => null,
        'current' => null,
        'page_size' => 30,
        'page_key' => 'page',
        'rewrite' => null,
    ];
    protected $context_class = null;
    protected $url;
    
    protected function getDefaultUrl()
    {
        $_SERVER = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
        return $_SERVER['REQUEST_URI'] ?? '';
    }
    protected function getDefaultPageNo()
    {
        $_GET = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_GET : $_GET;
        return $_GET[$this->options['page_key']] ?? 1;
    }
    ////////////////////////
    //@override
    public function init(array $options, object $context = null)
    {
        parent::init($options, $context);
        $this->options['current'] = $this->current();
        return $this;
    }
    //@override
    protected function initOptions(array $options)
    {
        $this->url = $this->options['url'] ?? $this->getDefaultUrl();
    }
    //@override
    protected function initContext(object $context)
    {
        $this->context_class = get_class($context);
    }
    
    public function current($new_value = null): int
    {
        if (isset($new_value)) {
            $this->options['current'] = $new_value;
            
            return $new_value;
        }
        if ($this->options['current'] !== null) {
            return $this->options['current'];
        }
        $page_no = $this->getDefaultPageNo();
        $page_no = intval($page_no) ?? 1;
        $page_no = $page_no > 1 ? $page_no : 1;
        $this->options['current'] = $page_no;
        
        return $page_no;
    }
    public function pageSize($new_value = null): int
    {
        if (empty($new_value)) {
            return $this->options['page_size'];
        }
        $this->options['page_size'] = $new_value;
        return $this->options['page_size'];
    }
    public function getPageCount($total): int
    {
        return (int)ceil($total / $this->options['page_size']);
    }
    public function getUrl($page)
    {
        if ($this->options['rewrite']) {
            return ($this->options['rewrite'])($page);
        }
        return $this->defaultGetUrl($page);
    }
    public function defaultGetUrl($page)
    {
        $page_key = $this->options['page_key'];
        $url = $this->url ?? '';
        $flag = strpos($url, '{'.$page_key.'}');
        if ($flag !== false) {
            $page = $page != 1?$page:'';
            return str_replace('{'.$page_key.'}', $page, $url);
        }
        $path = (string) parse_url($url, PHP_URL_PATH);
        $query = (string) parse_url($url, PHP_URL_QUERY);
        
        $get = [];
        parse_str($query, $get);
        $get[$page_key] = $page;
        
        if ($page == 1) {
            unset($get['page']);
        }
        
        $url = $path.($get?'?'.http_build_query($get):'');
        return $url;
    }
    ////////////////////////
    public function render($total, $options = []): string
    {
        $this->init($options);
        $current_page = $this->current();
        $total_pages = $this->getPageCount($total);
        
        if ($total_pages <= 1) {
            return '';
        }
        ///////////////////////////////
        
        $window_length = 3;
        $page_window_begin = $current_page - floor($window_length / 2);
        $page_window_begin = $page_window_begin > 1?$page_window_begin:1;
        
        $page_window_end = $page_window_begin + ($window_length - 1);
        $page_window_end = $page_window_end <= $total_pages?$page_window_end:$total_pages;
        
        $url_first = $this->getUrl(1);
        $url_last = $this->getUrl($total_pages);
        
        $html = '<span class="page_wraper">';
        $spliter = "<span class='page_spliter'>|</span>";
        if ($page_window_begin > 1) {
            $html .= "<a href='$url_first' class='page'>1</a>";
            if ($page_window_begin > 2) {
                $html .= "<span class='page_blank'>...</span>";
            } else {
                $html .= $spliter;
            }
        }
        $page_htmls = array();
        for ($i = $page_window_begin;$i <= $page_window_end;$i++) {
            $url = $this->getUrl($i);
            $page_htmls[] = ($i == $current_page)?"<span class='current'>$i</span>":"<a href='$url' class='page'>$i</a>";
        }
        
        $html .= implode($spliter, $page_htmls);
        
        if ($page_window_end < $total_pages) {
            if ($page_window_end < $total_pages - 1) {
                $html .= "<span class='page_blank'>...</span>";
            } else {
                $html .= $spliter;
            }
            $html .= "<a href='$url_last' class='page'>{$total_pages}</a>";
        }
        $html .= '</span>';
        return $html;
    }
}
