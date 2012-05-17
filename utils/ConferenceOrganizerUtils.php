<?php
class ConferenceOrganizerUtils
{
	public static function isOrganizerFromConference($uid,$cid)
	{
		//get the page_id
		$dbr=wfGetDB(DB_SLAVE);
		$result=$dbr->select("page_props",
		"*",
		array('pp_propname'=>'cvext-organizer-user','pp_value'=>$uid),
		__METHOD__,
		array());
			//the user-id is already present as an organizer, just need to check if its for the same conference or not
		foreach ($result as $row)
		{
			$resultRow=$dbr->selectRow("page_props",
			array('pp_value'),
			array("pp_page"=>$row->pp_page,'pp_propname'=>'cvext-organizer-conf'),
			__METHOD__,
			array());
			if($resultRow->pp_value==$cid)
			{
				return true;
			}
		}
		return false;
		
		
	}
}