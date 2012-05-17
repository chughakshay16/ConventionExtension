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
}