<?php
// 一个野鸡分页，不要问我从哪里来。
class Pager
{
	private $page_size;
	private $total_pages;
	public $key='page';
	public function __construct($total_nums,$page_size)
	{
		$this->page_size=$page_size;		//每页显示的数据条数
		$this->nums=$total_nums;		//总的数据条数
		
		$current_page=isset($_GET[$this->key])?$_GET[$this->key]:0;
		$current_page=($current_page>0)?$current_page:1;
		$this->current_page=$current_page;
		$this->total_pages=ceil($total_nums/$page_size);
	}
	public function get_url($page)
	{
		$uri=$_SERVER['REQUEST_URI'];
		$a=array();
		$path=parse_url($uri,PHP_URL_PATH);
		$query=parse_url($uri,PHP_URL_QUERY);
		parse_str($query,$a);
		$a[$this->key]=$page;
		if($page==1){
			unset($a['page']);
		}

		$url=$path.($a?'?'.http_build_query($a):'');
		return $url;
	}
	public  function get_page_output()
	{
		if($this->total_pages==1){
			return '';
		}
		$window_length=3;
		$page_window_begin=$this->current_page-floor($window_length/2);
		$page_window_begin=$page_window_begin>1?$page_window_begin:1;
		$page_window_end=$page_window_begin+($window_length-1);
		$page_window_end=$page_window_end<=$this->total_pages?$page_window_end:$this->total_pages;
		
		$html='<span class="page_wraper">';
			$spliter="<span class='page_spliter'></span>";
			if($page_window_begin>1){
				$url=$this->get_url(1);
				$html.="<a href='$url' class='page'>1</a>";
				if($page_window_begin>2){
					$html.="<span class='page_blank'>...</span>";
				}else{
					$html.=$spliter;
				}
			}
			$page_htmls=array();
			for($i=$page_window_begin;$i<=$page_window_end;$i++){
				
				if($i==$this->current_page){
					$page_htmls[]="<span class='current'>$i</span>";
				}else{
					$url=$this->get_url($i);
					$page_htmls[]="<a href='$url' class='page'>$i</a>";
				}
			}
			
			$html.=implode($spliter,$page_htmls);
			if($page_window_end<$this->total_pages){
				$url=$this->get_url($this->total_pages);
				if($page_window_end<$this->total_pages-1){
					$html.="<span class='page_blank'>...</span>";
				}else{
					$html.=$spliter;
				}
				$html.="<a href='$url' class='page'>{$this->total_pages}</a>";
				
			}
		$html.='</span>';
		return $html;
	}
}