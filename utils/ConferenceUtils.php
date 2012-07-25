<?php
class ConferenceUtils
{
	public static function isConference($conferenceId)
	{
		$dbr=wfGetDB(DB_SLAVE);
		$res=$dbr->select('page_props',
		'*',
		array('pp_propname'=>'type','pp_value'=>$conferenceId),
		__METHOD__,
		array());
		return $dbr->numRows($res) ? true : false;
	}
	public static function getConferenceId( $title )
	{
		# first we will check for session data
		/*global $wgRequest;
		$sessionData = $wgRequest->getSessionData( 'conference' );
		if( isset( $sessionData ) )
		{
			$sessionArray = $wgRequest->getSessionData( 'conference' );
			if( $sessionArray['title'] == $title )
			{
				return $sessionArray['id'];
			}
		}*/
		$dbr = wfGetDB( DB_SLAVE );
		$row = $dbr->selectRow('page',
			array('page_id'),
			array('page_title'=>$title),
			__METHOD__,
			array());
		
		return $row->page_id ? $row->page_id : false;
	}
	public static function getNamespace($conferenceId)
	{
		$dbr=wfGetDB(DB_SLAVE);
		$row=$dbr->selectRow('page',
		array('page_namespace'),
		array('page_id'=>$conferenceId),
		__METHOD__,
		array());
		return $row->page_namespace;
	}
	public static function getTitle($conferenceId)
	{
		global $wgRequest;
		$sessionData = $wgRequest->getSessionData('conference');
		if(isset($sessionData))
		{
			//$sessionArray= $wgRequest->getSessionData('conference');
			if($sessionData['id'] == $conferenceId)
			{
				return $sessionData['title'];
			}
		}
		$dbr=wfGetDB(DB_SLAVE);
		$row=$dbr->selectRow('page',
		array('page_title'),
		array('page_id'=>$conferenceId),
		__METHOD__,
		array());
		return $row->page_title;
	}	
	public static function getConferenceTitles()
	{
		$dbr = wfGetDB( DB_SLAVE );
		//we will perform a join operation on page_props and page table to fetch the conference titles
		$result = $dbr->select(array('page_props','page'),
					'*',
					array('pp_propname'=>'cvext-type','pp_value'=>'conference'),
					__METHOD__,
					array(),
					array('page'=>array('INNER JOIN','page_id=pp_page'))
					);
		return $result;
	}
	
}