<?php
/**
* 类似百度分页类
*/
class Page
{
	var $pagecount;         //总页数
	var $pagearg = "page";  //分页参数名称
	var $pagesize = 25;     //每页显示记录数
	var $recordcount;       //总记录数
	var $pagenum;           //当前页码
	var $denyGetStr = array();	//要除去的GET参数		
	
	public $start;			//排序起始页
	public $end;			//排序终止页
	
	var $argstr = "";       //GET参数字符串
	var $display = 5;		//左边显示的页数,例如是5,就是说左边有5页,总共是10页
	var $perpage = 10;		//每次显示页码个数
	
	/*
	* 功能：构造函数
	* 参数 $recordcount 为记录总数
	* 参数 $pagesize 为每页显示记录数,默认为20
	* 参数 $display 为左偏移量,例如是5,就是说左边有5页,总共是10页
	*/
	public function __construct($recordcount, $pagesize = 10, $display = 5, $denyGetArr = array())
	{
		$this->pagesize = $pagesize;
		$this->recordcount = $recordcount;
		$this->display = $display;
		$this->denyGetStr = $denyGetArr;
		$this->perpage = 2 * $this->display;
		$this->pagecount = ceil($recordcount/$pagesize);//总页数
		$this->pagenum = $this->currentpage();//当前页码
		$this->argstr = $this->newarg();//GET参数字符串
		$this->order();
	}
	
	/*
	* 功能：取得当前页码函数
	*/
	function currentpage()
	{
		if(isset($_GET[$this->pagearg]))
		{
			if($_GET[$this->pagearg] <= 0)
			{
				$page = 1;
			}
			else if($_GET[$this->pagearg] > $this->pagecount)
			{
				$page = $this->pagecount;
			}
			else
			{
				$page = $_GET[$this->pagearg];
			}
		}
		else
		{
			$page = 1;
		}
		return $page;
	}
	
	/*
	* 功能：重新整理GET参数
	*/
	function newarg()
	{
		$str = "";
		$urlar = $_GET;
		unset($urlar[$this->pagearg]);

		if($urlar)
		{
			foreach($urlar as $key=>$val)
			{
				//echo $key."|".$val."<br>";
				$val = rawurldecode($val);
				//echo $key."|".$val."<br>";
				$val = rawurlencode($val);
				//echo $key."|".$val."<br>";
				if($str == "")
				{
					$str = "?$key=$val";
				}
				else
				{
					$str .= "&$key=$val";
				}
			}
			$str .= "&$this->pagearg=";
		}
		else
		{
			$str = "?$this->pagearg=";
		}

		if(sizeof($this->denyGetStr) > 0)
		{
			foreach($this->denyGetStr as $key => $val)
			{
				$searchArr[$key] = "/".$val."=([^&]*)/is";
				$replaceArr[$key] = "";
			}
			$str = preg_replace($searchArr, $replaceArr, $str);
			
			$searchArr = array ("/([&]+)/is", "/&$/is");
			$replaceArr = array ("&", "");
			$str = preg_replace($searchArr, $replaceArr, $str);
			
			//$str = str_replace("?&", "?", $str);
		}
		return $str;
	}
	
	/**
    *这里将是怎么显示为百度分页的那种效果,当然,已经够用了
    *还有局部没有处理好,如果处理好麻烦告诉我
    */
	function order()
	{
		if($this->pagecount <= 2 * $this->display)
		{
			$this->start = 1;
			$this->end = $this->pagecount;
		}
		else
		{
			if ($this->pagenum <= $this->display)
			{
				$this->start = 1;
				$this->end = 2 * $this->display;
			}
			else
			{
				if($this->pagenum > $this->display && ($this->pagecount - $this->pagenum >= $this->display - 1))
				{
					$this->start = $this->pagenum - $this->display;
					$this->end = $this->pagenum + $this->display - 1;
				}
				else
				{
					$this->start = $this->pagecount - 2 * $this->display + 1;
					$this->end = $this->pagecount;
				}
			}
		}
	}
	
	/*
	* 功能：分页字符输出函数
	*/
	function show_page($showTotal = 1)
	{
		$trunpage = "";
		if($this->pagecount>1)
		{
			//如果当前页是第一页
			if($this->pagenum == 1)
			{
				$trunpage .= "<a href=\"{$this->argstr}1\">上一页</a> ";
			}else{
				$pre = ($this->pagenum - 1 <= 0) ? 1 : ($this->pagenum - 1);
				$trunpage .= "<a href=\"{$this->argstr}$pre\">上一页</a> ";
			}

			/*for ($i = $this->start; $i <= $this->end; $i++)
			{
				$trunpage .= ($i == $this->pagenum)? "<a class=\"current\">$i</a>": "<a href=\"{$this->argstr}$i\">$i</a>";
			}*/
			$trunpage .= "第".$this->pagenum."/".$this->pagecount."页 ";
			
			$next = ($this->pagenum + 1 >= $this->pagecount) ? $this->pagecount : ($this->pagenum + 1);
			//当前页是最后一页的页数,不要下一页和最后一页
			if ($this->pagenum != $this->pagecount)
			{
				$trunpage .= " <a href=\"{$this->argstr}$next\">下一页</a>";
				//$trunpage .= "<a class=\"end\" href=\"{$this->argstr}$this->pagecount\"></a>";
			}else{
				$trunpage .= " <a href=\"{$this->argstr}$next\">下一页</a>";
			}
		}
		
		//if($showTotal == 1) $trunpage = "<nav class=\"simple_pager\"><ul class=\"pager\">" . $trunpage . "</ul></nav>";
		return $trunpage;
	}
}
?>