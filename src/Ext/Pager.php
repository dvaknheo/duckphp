<?php
namespace DNMVCS\Ext;

use DNMVCS\Core\SingletonEx;

class Pager
{
    use SingletonEx;
    const DEFAULT_OPTIONS=[
        'url'=>null,
        'key'=>null,
        'page_size'=>null,
        'rewrite'=>null,
        'current'=>null,
    ];
    protected $context_class;
    public static function SG()
    {
        return static::G()->_SG();
    }
    public function _SG()
    {
        if ($this->context_class) {
            return $this->context_class::SG();
        } else {
            return \DNMVCS\Core\App::G()::SG();
        }
    }
    public static function Current()
    {
        return static::G()->_Current();
    }
    public static function Render($total, $options=[])
    {
        return static::G()->_Render($total, $options);
    }
    public function _current()
    {
        if ($this->current_page!==null) {
            return $this->current_page;
        }
        $this->current_page=intval(static::SG()->_GET[$this->key]??1);
        return $this->current_page;
    }

    protected $page_size=30;
    protected $current_page=null;
    protected $url='';
    protected $key='page';
    
    protected $handel_get_url=null;
    
    /**
     * options: url, key,rewrite,current
     */
    public function init($options=[], $context=null)
    {
        $this->url=$options['url']??static::SG()->_SERVER['REQUEST_URI'];
        $this->key=$options['key']??$this->key;
        $this->page_size=$options['page_size']??$this->page_size;
        
        $this->handel_get_url=$options['rewrite']??$this->handel_get_url;
        
        $this->current_page=$options['current']??intval(static::SG()->_GET[$this->key]??1);
        $this->current_page=$this->current_page>1?$this->current_page:1;
        
        $this->context_class=isset($context)?get_class($context):null;
    }
    public function getUrl($page)
    {
        if ($this->handel_get_url) {
            return ($this->handel_get_url)($page);
        }
        return $this->defaultGetUrl($page);
    }
    public function defaultGetUrl($page)
    {
        $flag=strpos($this->url, '{'.$this->key.'}');
        if ($flag!==false) {
            $page=$page!=1?$page:'';
            return str_replace('{'.$this->key.'}', $page, $this->url);
        }
        $path=parse_url($this->url, PHP_URL_PATH);
        $query=parse_url($this->url, PHP_URL_QUERY);
        
        $get=[];
        parse_str($query, $get);
        $get[$this->key]=$page;
        
        if ($page==1) {
            unset($get['page']);
        }
        
        $url=$path.($get?'?'.http_build_query($get):'');
        return $url;
    }
    public function _render($total, $options=[])
    {
        $this->init($options);
        
        $current_page=$this->_current();
        $total_pages=ceil($total/$this->page_size);
        if ($total_pages<=1) {
            return '';
        }
        
        $window_length=3;
        $page_window_begin=$current_page-floor($window_length/2);
        $page_window_begin=$page_window_begin>1?$page_window_begin:1;
        
        $page_window_end=$page_window_begin+($window_length-1);
        $page_window_end=$page_window_end<=$total_pages?$page_window_end:$total_pages;
        
        $url_first=$this->getUrl(1);
        $url_last=$this->getUrl($total_pages);
        
        $html='<span class="page_wraper">';
        $spliter="<span class='page_spliter'>|</span>";
        if ($page_window_begin>1) {
            $html.="<a href='$url_first' class='page'>1</a>";
            if ($page_window_begin > 2) {
                $html.="<span class='page_blank'>...</span>";
            } else {
                $html.=$spliter;
            }
        }
        $page_htmls=array();
        for ($i=$page_window_begin;$i<=$page_window_end;$i++) {
            $url=$this->getUrl($i);
            $page_htmls[]=($i==$current_page)?"<span class='current'>$i</span>":"<a href='$url' class='page'>$i</a>";
        }
        
        $html.=implode($spliter, $page_htmls);
        
        if ($page_window_end < $total_pages) {
            if ($page_window_end<$total_pages-1) {
                $html.="<span class='page_blank'>...</span>";
            } else {
                $html.=$spliter;
            }
            $html.="<a href='$url_last' class='page'>{$total_pages}</a>";
        }
        $html.='</span>';
        return $html;
    }
}
