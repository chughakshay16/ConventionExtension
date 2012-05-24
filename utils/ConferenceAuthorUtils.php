<?php
class ConferenceAuthorUtils 
{
	/**
	 * 
	 * Checks if a user already has a parent author page
	 * Only one parent author object is allowed for any user
	 * @param Int $userId
	 */
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
	/**
	 * 
	 * Checks if the sub-author id exists for a particular conference
	 * @param Int (page_id of the sub-author page) $aid
	 * @param Int $cid
	 */
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
	 * Fetches username for a given sub-author
	 * @param Int(page_id of the sub-author wiki page) $subAuthorId
	 * @todo check if database query works
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
	/**
	 * 
	 * Fetch the conference title of the conference which this sub-author page points to
	 * @param Int(page_id of the sub-author wiki page) $subAuthorId
	 */
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