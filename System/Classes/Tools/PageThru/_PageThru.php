<?php
class PageThru extends Plugin {

	function __construct() {
		parent::__construct();
//		$this->Plugin();
	}

	function exposeServices() {
		return array(
			'PageThru.Display'	=>	array('method'	=>	'display'),
		);
	}

	function getURL($page, $queryString = '') {
		if (strlen($queryString)) $queryString = '&'.$queryString;
		if(substr($this->ATTRIBUTES['URL'],-1) == '?') {
			return $this->ATTRIBUTES['URL'].'CurrentPage='.$page.$queryString;
		}
		return $this->ATTRIBUTES['URL'].'&CurrentPage='.$page.$queryString;
	}
	
	function display() {
		//ss_DumpVar($this,'before param',true);

		$this->display->layout = 'None';
		
		// Set some defaults
		$this->param('ItemCount','1');
		$this->param('CurrentPage','1');
		$this->param('QueryString','');
		$this->param('ItemsPerPage','10');
		$this->param('PagesPerBlock','10');
		$this->param('URL','index.php?act=Test');
		$this->param('HideDisabled10',1);
		$this->param('HideDisabled',1);
		$this->param('HidePreviousNext',0);
		$this->param('HidePreviousNext10',0);
		
		$this->param('Previous10','&lt;&lt;');
		$this->param('Previous','&lt;');
		$this->param('Next','&gt;');
		$this->param('Next10','&gt;&gt;');
		
		$this->param('BeforePageThru','');
		$this->param('BeforePrevious10','');
		$this->param('AfterPrevious10','&nbsp;&nbsp;');
		$this->param('BeforePrevious','');
		$this->param('AfterPrevious','&nbsp;&nbsp;');
		$this->param('BeforeLink','&nbsp;');
		$this->param('BeforeCurrent','');

		$this->param('AfterCurrent','');
		$this->param('AfterLink','&nbsp;');
		$this->param('BeforeNext','&nbsp;');
		$this->param('AfterNext','');
		$this->param('BeforeNext10','&nbsp;&nbsp;');
		$this->param('AfterNext10','');
		$this->param('AfterPageThru','');
		
		
		// Copy some values to make easier to work with
		$currentPage	= $this->ATTRIBUTES['CurrentPage'];
		$itemCount 		= $this->ATTRIBUTES['ItemCount'];
		$itemsPerPage	= $this->ATTRIBUTES['ItemsPerPage']?$this->ATTRIBUTES['ItemsPerPage']:10;
		$pagesPerBlock	= $this->ATTRIBUTES['PagesPerBlock']?$this->ATTRIBUTES['PagesPerBlock']:10;
		$queryString	= $this->ATTRIBUTES['QueryString'];
		$hideDisabled10	= $this->ATTRIBUTES['HideDisabled10'];
		$hideDisabled	= $this->ATTRIBUTES['HideDisabled'];
		
		$previous10	= $this->ATTRIBUTES['Previous10'];
		$previous	= $this->ATTRIBUTES['Previous'];
		$next		= $this->ATTRIBUTES['Next'];
		$next10		= $this->ATTRIBUTES['Next10'];
		
		$bpt	= $this->ATTRIBUTES['BeforePageThru'];
		$bp10	= $this->ATTRIBUTES['BeforePrevious10'];
		$ap10	= $this->ATTRIBUTES['AfterPrevious10'];
		$bp		= $this->ATTRIBUTES['BeforePrevious'];
		$ap		= $this->ATTRIBUTES['AfterPrevious'];
		$bl		= $this->ATTRIBUTES['BeforeLink'];
		$bc		= $this->ATTRIBUTES['BeforeCurrent'];

		$ac		= $this->ATTRIBUTES['AfterCurrent'];
		$al		= $this->ATTRIBUTES['AfterLink'];
		$bn		= $this->ATTRIBUTES['BeforeNext'];
		$an		= $this->ATTRIBUTES['AfterNext'];
		$bn10	= $this->ATTRIBUTES['BeforeNext10'];
		$an10	= $this->ATTRIBUTES['AfterNext10'];
		$apt	= $this->ATTRIBUTES['AfterPageThru'];
		// phew.. thats a lot :)
		//ss_DumpVar($this,'',true);
		$totalPages = ceil($itemCount/$itemsPerPage);

		$startPage = floor(($currentPage-1)/$pagesPerBlock)*$pagesPerBlock+1;
		$finishPage = $startPage+$pagesPerBlock-1;
		if ($finishPage > $totalPages) {
			$finishPage = $totalPages;
		}
/*

<div class="paging clearfix">
	<div class="pull-left">
		<ul class="pagination">
			<li><a href="">|&lt;</a></li>
			<li><a href="">&lt;</a></li>
			<li><a href="">1</a></li>
			<li class="active"><span>2</span></li>
		</ul>
	</div>
	<div class="pull-right">Showing 16 to 16 of 16 (2 Pages)</div>
</div>

*/

		ss_log_message( "Pager: startPage:$startPage, finishPage:$finishPage, totalPages:$totalPages, pagesPerBlock:$pagesPerBlock, itemCount:$itemCount, itemsPerPage:$itemsPerPage" );
		// First check if we need a page thru control at all
		if ($itemCount > $itemsPerPage)
		{
			
			print $bpt;
			echo "Page $currentPage of $totalPages";
			?>
			<div class='paging clearfix'>
				<ul class="pagination">
			<?php
			
			// Display the "previous 10" page arrow
			if (!$this->ATTRIBUTES['HidePreviousNext10']) {
				if ($startPage > 1) {
					print($bp10."<li><A class='previous' HREF=\"".$this->getURL($startPage-1, $queryString).'">'.$previous10.'</a></li>'.$ap10);			
				} else {
					if (!$hideDisabled10) {
						print($bp10.$previous10.$ap10);
					}	
				}
			}
			
			// Display the "previous" page arrow
			if (!$this->ATTRIBUTES['HidePreviousNext']) {
				if ($currentPage > 1) {
					print($bp."<li><A class='previous' HREF=\"".$this->getURL($currentPage-1, $queryString).'">'.$previous.'</a></li>'.$ap);
				} else {
					if (!$hideDisabled) {
						print($bp.$previous.$ap);
					}	
				}
			}
			
			// Display the page numbers
			for ($page = $startPage; $page <= $finishPage; $page++)
			{
				if ($currentPage == $page)
					print($bc.'<li class="active"><span>'.$page.'</span></li>'.$ac );
				else
					print($bl.'<li><a href="'.$this->getURL($page, $queryString).'">'.$page.'</a></li>'.$al);

//				if ($itemCount != $page) print('&nbsp;');
			}
			
			// Display the "next" page arrow
			if (!$this->ATTRIBUTES['HidePreviousNext']) {
				if ($currentPage < $totalPages)	{
					print($bn.'<li><a href="'.$this->getURL($currentPage+1, $queryString).'">'.$next.'</a></li>'.$an);
				} else {
					if (!$hideDisabled) {
						print($bn.$next.$an);
					}	
				}
			}

			// Display the "next 10" page arrow
			if (!$this->ATTRIBUTES['HidePreviousNext10']) {
				if ($finishPage < $totalPages) {
					print($bn10.'<li><a href="'.$this->getURL(floor(($currentPage+$pagesPerBlock-1)/$pagesPerBlock)*$pagesPerBlock+1, $queryString).'">'.$next10.'</a></li>'.$an10);
				} else {
					if (!$hideDisabled10) {
						print($bn10.$next10.$an10);
					}	
				}
			}

			print $apt;

			?>
				</ul>
			</div>
			<?php
		}
		
	}
	
}


?>
