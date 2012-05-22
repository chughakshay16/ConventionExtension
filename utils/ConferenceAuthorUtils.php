<?php
class ConferenceAuthorUtils 
{
	public static function hasParentAuthor($userId)
	{
		$dbr=wfGetDB(DB_SLAVE);
		$resultRow=$dbr->select("page_props",
		"*",
		array('pp_propname'=>'cvext-author-user','pp_value'=>$userId),
		__METHOD__,
		array());
		return $resultRow->pp_page?true:false;
	}
	public static function hasChildAuthor($aid,$cid)
	{
		$dbr=wfGetDB(DB_SLAVE);
		$resultRow=$dbr->select("page_props",
		"*",
		array('pp_page'=>$aid,'pp_propname'=>'author-conf','pp_value'=>$cid),
		__METHOD,
		array());
		return $resultRow->pp_page?true:false;
	}
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $subAuthorId
	 * @todo complete this function (INNER JOIN on the same table)
	 */
	public static function getUsernameFromSubAuthor($subAuthorId)
	{
		$dbr=wfGetDB(DB_SLAVE);
		$resultRow=$dbr->selectRow('page_props E',
		'E.pp_value',
		array('E.pp_page'=>$subAuthorId,'E.pp_propname'=>'author-user','F.pp_propname'=>'author-parent'),
		__METHOD__,
		array(),
		array('page_props F'=> array('INNER JOIN','F.pp_value=E.pp_page')));
		return $resultRow->E.pp_value;
	}
	public static function getConferenceTitleFromSubAuthor($subAuthorId)
	{
		$dbr=wfGetDB(DB_SLAVE);
		$resultRow=$dbr->select('page_props',
		'*',
		array('pp_page'=>$subAuthorId,'pp_propname'=>'author-conf'),
		__METHOD__,
		array(),
		array('page'=> array('INNER JOIN','pp_value=page_id')));
		return $resultRow->page_title;
	}
}