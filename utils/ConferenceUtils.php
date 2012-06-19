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
		return $dbr->numRows($res)?true:false;
	}
	public static function getConferenceId($title)
	{
		//first we will check for session data
		global $wgRequest;
		if(isset($wgRequest->getSessionData('conference')))
		{
			$sessionArray= $wgRequest->getSessionData('conference');
			if($sessionArray['title']===$title)
			{
				return $sessionArray['id'];
			}
		}
		$dbr=wfGetDB(DB_SLAVE);
		$row=$dbr->selectRow('page',
		array('page_id'),
		array('page_title'=>$title),
		__METHOD__,
		array());
		return $row->page_id?$row->page_id:false;
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
		if($wgRequest->getSessionData('conference'))
		{
			$sessionArray= $wgRequest->getSessionData('conference');
			if($sessionArray['id']===$id)
			{
				return $sessionArray['title'];
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
	
}