<?php
class EventLocation
{
	/**
	 * 
	 * page_id of the location page
	 * @var Int
	 */
	private $mLocationId;
	/**
	 * 
	 * Enter description here ...
	 * @var unknown_type
	 * @todo change the name of this property (make it more appropriate)
	 */
	private $mRoomNo;
	/**
	 * 
	 * description of the location
	 * @var String
	 */
	private $mDescription;
	/**
	 * 
	 * url for the location's image
	 * @var String
	 */
	private $mImageUrl;
	/**
	 * 
	 * page_id of the conference page
	 * @var Int
	 */
	private $mConferenceId;
	/**
	 * 
	 * Constructor function
	 * @param String(number) $rno
	 * @param String $desc
	 * @param string $url
	 * @param Int $lid
	 */
	public function __construct($rno,$desc,$url,$lid=null,$cid){
		$this->mRoomNo=$rno;
		$this->mDescription=$desc;
		$this->mImageUrl=$url;
		$this->mLocationId=$lid;
		$this->mConferenceId=$cid;
	}
	/**
	 * @param String $roomNo
	 * @param String $description
	 * @param String $url
	 * @return EventLocation
	 */
	public static function createFromScratch($cid,$roomNo,$description,$url)
	{
		$confTitle=ConferenceUtils::getTitle($cid);
		$title=$confTitle.'/locations/'.$roomNo;
		$titleObj=Title::newFromText($title);
		$pageObj=WikiPage::factory($titleObj);
		$text=Xml::element('location',array('roomNo'=>$roomNo,'description'=>$description,'url'=>$url,'cvext-type'=>'location','cvext-location-conf'=>$cid));
		$status=$pageObj->doEdit($text, 'new location added',EDIT_NEW);	
		if($status->value['revision'])
		{
			$revision=$status->value['revision'];
			$locationId=$revision->getPage();
			$dbw=wfGetDB(DB_MASTER);
			$dbw->insert('page_props',array('pp_page'=>$locationId,'pp_propname'=>'cvext-type','pp_value'=>'location'),__METHOD__,array());
			$dbw->insert('page_props',array('pp_page'=>$locationId,'pp_propname'=>'cvext-location-conf','pp_value'=>$cid),__METHOD__,array());
			return new self($roomNo,$description,$url,$locationId,$cid);
		}
		else
		{
			//do something here
		}
	}
	/**
	 * 
	 * deletes a location page for this conference with this roomNo
	 * @param Int $cid
	 * @param String $roomNo
	 * @return $result
	 * $result['done'] - signifies success or failure (true/false)
	 * $result['msg'] - success or failure message
	 * This function only deletes those locations which are not associated with any event
	 */
	public static function performDelete($cid,$roomNo)
	{
		$confTitle=ConferenceUtils::getTitle($cid);
		$titleText=$confTitle.'/locations/'.$roomNo;
		$title=Title::newFromText($titleText);
		$page=WikiPage::factory($title);
		$result=array();
		if($page->exists())
		{
			$id=$page->getId();
			$hasEvent=ConferenceEventUtils::isPartOfAnyEvent($id);
			if($hasEvent)
			{
				//$result['hasEvent']=true;
				$result['done']=false;
				$result['msg']='This location cant be deleted as its part of an already existing event';
				$result['flag']=Conference::ERROR_PARENT_PRESENT;
			}
			else {
				//do note that doArticleDelete() doesnt delete the rows in page_props so we will have to manually delete them
				$status=$page->doArticleDelete("location deleted by admin",DELETED_TEXT);
				if($status===true)
				{
					$result['done']=true;
					$result['msg']="The location has been successfully deleted";
					$result['flag']=Conference::SUCCESS_CODE;
				} else {
					$result['done']=false;
					$result['msg']="The location couldnt be delelted";
					$result['flag']=Conference::ERROR_DELETE;
				}
			}
		}
		else
		{
			$result['done']=false;
			$result['msg']="The location with this roomNo doesnt exist for this conference";
			$result['flag']=Conference::ERROR_MISSING;
		}
		return $result;
		
	}
	/**
	 * 
	 * performs an edit operation on a location page
	 * @param Int $cid
	 * @param String $roomNo
	 * @param String $description
	 * @param String $url
	 * @return $result
	 * $result['done'] - true/failure signifies if the process was a success or a failure
	 * $result['msg'] - success/failure message
	 * @todo modification of content needs to be done
	 */
	public static function performEdit($cid,$roomNo,$description,$url)
	{
		$confTitle=ConferenceUtils::getTitle($cid);
		$titleText=$confTitle.'/locations/'.$roomNo;
		$title=Title::newFromText($titleText);
		$page=WikiPage::factory($title);
		$result=array();
		if($page->exists())
		{
			$id=$page->getId();
			$article=Article::newFromID($id);
			$content=$article->fetchContent();
			//modify the content
			$status=$page->doEdit($content,"location is modified by the admin",EDIT_UPDATE);
			if($status->value['revision'])
			{
				$result['done']=true;
				$result['msg']="The location has been successfully edited";
				$result['flag']=Conference::SUCCESS_CODE;
			} else {
				$result['done']=false;
				$result['msg']="The location could not be edited";
				$result['flag']=Conference::ERROR_EDIT;
			}
		} else {
			$result['done']=false;
			$result['msg']="The location with this roomNo for this conference doesnt exist in the database";
			$result['flag']=Conference::ERROR_MISSING;
		}
		return $result;
	}
	/**
	 * @param Int $locationId page_id of the location page
	 * @return EventLocation
	 */
	public static function loadFromId($locationId)
	{
		$article=Article::newFromID($locationId);
		$text=$article->fetchContent();
		preg_match_all("/<location roomNo=\"(.*)\" description=\"(.*)\" url=\"(.*)\" cvext-type=\"(.*)\" cvext-location-conf=\"(.*)\" \/>/",$text,$matches);
		return new self($matches[1][0], $matches[2][0], $matches[3][0], $locationId, $matches[4][0]);
	}
	/**
	 * 
	 * Parser Hook function
	 * @param String $input
	 * @param Array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 */
	public static function render($input, array $args, Parser $parser, PPFrame $frame)
	{
		$id=$parser->getTitle()->getArticleId();
		if($id!=0)
		{
			wfGetDB(DB_MASTER)->insert('page_props',array('pp_page'=>$id
			,'pp_propname'=>'cvext-type','pp_value'=>'location'));
		}
		return '';
	}
	/**
	 * 
	 * getter function
	 */
	public function getLocationId()
	{
		return $this->mLocationId;
	}
	/**
	 * 
	 * setter function
	 * @param Int $id
	 */
	public function setLocationId($id)
	{
		$this->mLocationId=$id;
	}
	/**
	 * 
	 * getter function
	 */
	public function getRoomNo()
	{
		return $this->mRoomNo;
	}
	/**
	 * 
	 * setter function
	 * @param String $no
	 */
	public function setRoomNo($no)
	{
		$this->mRoomNo=$no;
	}
	/**
	 * 
	 * getter function
	 */
	public function getDescription()
	{
		return $this->mDescription;
	}
	/**
	 * 
	 * setter function
	 * @param String $desc
	 */
	public function setDescription($desc)
	{
		$this->mDescription=$desc;
	}
	/**
	 * 
	 * getter function
	 */
	public function getImageUrl()
	{
		return $this->mImageUrl;
	}
	/**
	 * 
	 * setter function
	 * @param String $url
	 */
	public function setImageUrl($url)
	{
		$this->mImageUrl=$url;
	}
}