<?php
/**
 * @project_name: localframe
 * @file_name: dateutils.inc
 * @descr:
 * 
 * @version 1.0
 **/ 
if (!defined("PAGERUTILS_INC") ) {
 	define("PAGERUTILS_INC",1);

	class PagerUtils {
		public static function PagerPear($totalrecord, $pagecurrent, $limitpage, $varparam = array()) {
		/****************************Paging pear******************************/
		$params = array(
		    "mode"     		=> "Jumping",
		    "perPage" 		=> $limitpage,
		    "delta"  		=> 10,
			"append" 		=> true,
			"urlVar"		=> "page",
			"extraVars"		=> $varparam,
			"separator"  	=> " ",
		    "totalItems"    => $totalrecord
		);
		
		$pager 		= Pager::factory($params);
		$pageset 	= $pager->getPageData();
		$paging		= $pager->getLinks($pagecurrent);
		
		$smarty->assign("paging", $paging["all"]);
		/*****************************End paging********************************/
		}
		
		public static function PagerSmarty($page, $totalrecord, $url, $limitrecord = 20, $pagesize = 20) {
			if (empty($totalrecord)) return null;
			
			$paginghtml = null;
			$startpoint	= (int)($page - $pagesize) < 0 ? 0 : $page - $pagesize;
			$pagecount 	= ($totalrecord % (int)$limitrecord == 0 ) ? (int)($totalrecord/(int)$limitrecord) : (int)($totalrecord/(int)$limitrecord + 1);
			
			if ($pagesize < $pagecount && $page > floor($pagesize/2)):
				$startpoint = $page - floor($pagesize/2);
			else:
				$startpoint = 0;
			endif;
			
			if ($pagesize >= $pagecount) $pagesize = $pagecount;
			
			while (true):
				if ($pagesize + $startpoint <= $pagecount) break;
				$startpoint--;
			endwhile;
			
			if ($pagesize >= 1) {
				if ($page > 1) $paginghtml .= '<li><a page="'.($page-1).'" href="'.Router::getUrl($url."&page=".($page-1)).'">'.Generals::getTitle('PAGER_PREV').'</a></li>';
				else $paginghtml .= '<li class="disabled"><a page="1" href="javascript:;">'.Generals::getTitle('PAGER_PREV').'</a></li>';
				
				for ($i = ($startpoint+1); $i <= $pagesize+$startpoint; $i++) {
					if ($page == $i) $paginghtml .= '<li class="active"><a page="'.$i.'" href="javascript:;">'.$i.'</a></li>';
					else $paginghtml .= '<li><a page="'.$i.'" href="'.Router::getUrl($url."&page=".$i).'">'.$i.'</a></li>';
				}
				
				if ($page < $pagecount) $paginghtml .= '<li><a page="'.($page+1).'" href="'.Router::getUrl($url."&page=".($page+1)).'">'.Generals::getTitle('PAGER_NEXT').'</a></li>';
				else $paginghtml .= '<li class="disabled"><a page="'.$pagecount.'" href="javascript:;">'.Generals::getTitle('PAGER_NEXT').'</a></li>';
			}

			$_returnhtml = '<div class="page-total">';
			$_returnhtml.= Generals::getTitle('PAGER_TOTAL').": {$totalrecord} ".Generals::getTitle('PAGER_ITEM')." / {$pagecount} ".Generals::getTitle('PAGER_PAGE');
			$_returnhtml.= '</div>';
			
			$_returnhtml.= '<ul class="pagination">';
			if ($page > 1) $_returnhtml.= '<li><a page="1" href="'.Router::getUrl($url.'&page=1').'">'.Generals::getTitle('PAGER_FIRST').'</a></li>';
			else $_returnhtml.= '<li class="disabled"><a page="1" href="javascript:;">'.Generals::getTitle('PAGER_FIRST').'</a></li>';
			
			$_returnhtml.= $paginghtml;
			
			if ($page < $pagecount) $_returnhtml.= '<li><a page="'.$pagesize.'" href="'.Router::getUrl($url.'&page='.$pagecount).'">'.Generals::getTitle('PAGER_LAST').'</a></li>';
			else $_returnhtml.= '<li class="disabled"><a page="'.$pagesize.'" href="javascript:;">'.Generals::getTitle('PAGER_LAST').'</a></li>';
			$_returnhtml.= '</ul>';
			
			return '<div class="page-utils">'.$_returnhtml.'</div>';
		}
	}
}// end defined
?>
