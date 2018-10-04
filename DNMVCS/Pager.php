<?php
namespace DNMVCS;

class Pager
{
	use DNSingleton;

	public static function Current()
	{
		return static::G()->_Current();
	}
	public static function Render($total,$options)
	{
		return static::G()->_Render($total,$options);
	}
	public function _current()
	{
		if($this->current_page!==null){return $this->current_page;}
		$this->current_page=intval($_GET[$this->key]??1);
		return $this->current_page;
	}

	protected $page_size=30;
	protected $current_page=null;
	protected $url='';
	protected $key='page';

	public function init($options)
	{
		$this->url=$options['url']??$_SERVER['REQUEST_URI'];
		$this->key=$options['key']??$this->key;
		$this->page_size=$options['page_size']??$this->page_size;
		
		//TODO 如果是 rewrite 模式
		$this->current_page=intval($_GET[$this->key]??1);
		$this->current_page=$this->current_page>1?$this->current_page:1;
		
	}
	public function getUrl($page)
	{
		$path=parse_url($this->url,PHP_URL_PATH);
		$query=parse_url($this->url,PHP_URL_QUERY);
		
		parse_str($query,$get);
		$get[$this->key]=$page;
		
		if($page==1){
			unset($get['page']);
		}
		
		$url=$path.($get?'?'.http_build_query($get):'');
		return $url;
	}
	public function _render($total,$options=[])
	{
		if($options){$this->init($options);}
		$current_page=$this->_current();
		$total_pages=ceil($total/$this->page_size);
		if($total_pages<=1){return '';}
		
		$window_length=3;
		$page_window_begin=$current_page-floor($window_length/2);
		$page_window_begin=$page_window_begin>1?$page_window_begin:1;
		
		$page_window_end=$page_window_begin+($window_length-1);
		$page_window_end=$page_window_end<=$total_pages?$page_window_end:$total_pages;
		
		$url_first=$this->getUrl(1);
		$url_last=$this->getUrl($total_pages);
		
		$html='<span class="page_wraper">';
		$spliter="<span class='page_spliter'>|</span>";
		if($page_window_begin>1){
			$html.="<a href='$url_first' class='page'>1</a>";
			if($page_window_begin > 2){
				$html.="<span class='page_blank'>...</span>";
			}else{
				$html.=$spliter;
			}
		}
		$page_htmls=array();
		for($i=$page_window_begin;$i<=$page_window_end;$i++){
			$url=$this->getUrl($i);
			$page_htmls[]=($i==$current_page)?"<span class='current'>$i</span>":"<a href='$url' class='page'>$i</a>";
		}
		
		$html.=implode($spliter,$page_htmls);
		
		if($page_window_end < $total_pages){
			if($page_window_end<$total_pages-1){
				$html.="<span class='page_blank'>...</span>";
			}else{
				$html.=$spliter;
			}
			$html.="<a href='$url_last' class='page'>{$total_pages}</a>";
			
		}
		$html.='</span>';
		return $html;
	}
}