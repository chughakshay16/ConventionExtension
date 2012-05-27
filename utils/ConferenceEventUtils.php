<?php
class ConferenceEventUtils
{
	/**
	 * 
	 * checks if any event in a conference points to a location,
	 * here we are not checking for a conference because
	 * at the time of creating an event we always point an event 
	 * towards a location that is also part of the conference that this event
	 * is part of 
	 * @param unknown_type $locationId
	 */
	public static function isPartOfAnyEvent($locationId)
	{
		$dbr=wfGetDB(DB_SLAVE);
		//important thing to remember here is that we can get more than one rows in the result set as more than 
		//one event can point to the same location
		$result=$dbr->select("page_props",
		"*",
		array("pp_propname"=>"cvext-event-location","pp_value"=>$locationId),
		__METHOD__,
		array(),
		array());
		return $dbr->numRows($result)?true:false;
	}
}