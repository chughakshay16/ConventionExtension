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
		$resultRow=$dbr->selectRow("page_props",
		"*",
		array('pp_propname'=>'cvext-author-user','pp_value'=>$userId),
		__METHOD__,
		array());
		return $resultRow?$resultRow->pp_page:false;
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
		$resultRow=$dbr->selectRow("page_props",
		"*",
		array('pp_page'=>$aid,'pp_propname'=>'cvext-author-conf','pp_value'=>$cid),
		__METHOD__,
		array());
		return $resultRow?$resultRow->pp_page:false;
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
		/*$resultRow=$dbr->selectRow(array('E'=>'page_props','F'=>'page_props'),
		array('value'=>'E.pp_value','E.pp_page','E.pp_propname','F.pp_page','F.pp_propname','F.pp_value'),
		array('E.pp_page'=>$subAuthorId,'E.pp_propname'=>'cvext-author-user','F.pp_propname'=>'cvext-author-parent'),
		__METHOD__,
		array(),
		array('F'=> array('INNER JOIN','F.pp_value=E.pp_page')));
		var_dump($resultRow);
		return $resultRow->value;*/
		$resultRow=$dbr->selectRow('page_props',
		'*',
		array('pp_propname'=>'cvext-author-parent','pp_page'=>$subAuthorId),
		__METHOD__);
		if($resultRow)
		{
			$parentRow=$dbr->selectRow('page_props',
			'*',
			array('pp_propname'=>'cvext-account-user','pp_page'=>$resultRow->pp_value),
			__METHOD__);
			if($parentRow)
			{
				$userId=$parentRow->pp_value;
				return UserUtils::getUsername($userId);
			}
		}
		return null;
	}
	/**
	 * 
	 * Fetch the conference title of the conference which this sub-author page points to
	 * @param Int(page_id of the sub-author wiki page) $subAuthorId
	 */
	public static function getConferenceTitleFromSubAuthor($subAuthorId)
	{
		$dbr=wfGetDB(DB_SLAVE);
		$resultRow=$dbr->selectRow(array('page_props','page'),
		'*',
		array('pp_page'=>$subAuthorId,'pp_propname'=>'cvext-author-conf'),
		__METHOD__,
		array(),
		array('page'=> array('INNER JOIN','pp_value=page_id')));
		return $resultRow?$resultRow->page_title:null;
	}
	public static function getAuthorId($uid)
	{
		$dbr=wfGetDB(DB_SLAVE);
		$resultRow=$dbr->selectRow("page_props",
		"pp_page",
		array("pp_propname"=>"cvext-author-user","pp_value"=>$uid),
		__METHOD__,
		array(),
		array());
		return $resultRow->pp_page?$resultRow->pp_page:null;
	}
}