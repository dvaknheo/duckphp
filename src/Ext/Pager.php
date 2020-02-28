<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\SingletonEx;

class Pager
{
    use SingletonEx;
    
    public $options = [
        'url' => null,
        'current' => null,
        'page_size' => 30,
        'page_key' => 'page',
        'rewrite' => null,
    ];
    protected $context_class;
    protected $url;
    public function __construct()
    {
    }
    public static function SG()
    {
        return static::G()->_SG();
    }
    public function _SG()
    {
        if ($this->context_class) {
            return $this->context_class::SG();
        } else {
            return \DuckPhp\Core\App::G()::SG();
        }
    }
    ////////////////////////
    public function init(array $options, object $context = null)
    {
        $this->options = array_intersect_key(array_replace_recursive($this->options, $options) ?? [], $this->options);
        
        $this->context_class = isset($context)?get_class($context):null;
        
        $this->url = $this->options['url'] ?? static::SG()->_SERVER['REQUEST_URI'];
        
        $this->options['current'] = $this->current();
        /////
    }
    
    public function current()
    {
        if ($this->options['current'] !== null) {
            return $this->options['current'];
        }
        $this->options['current'] = intval(static::SG()->_GET[$this->options['page_key']] ?? 1);
        $this->options['current'] = $this->options['current'] > 1 ? $this->options['current'] : 1;
        return $this->options['current'];
    }
    public function pageSize($new_value = null)
    {
        if (empty($new_value)) {
            return $this->options['page_size'];
        }
        $this->options['page_size'] = $new_value;
    }
    public function getPageCount($total)
    {
        return ceil($total / $this->options['page_size']);
        ;
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
        $path = parse_url($url, PHP_URL_PATH) ?? '';
        $query = parse_url($url, PHP_URL_QUERY) ?? '';
        
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
    public function render($total, $options = [])
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
