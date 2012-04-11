<?php
class EventLocation
{
	private $mLocationId,$mRoomNo,$mDescription,$mImageUrl;
	public function __construct($rno,$desc,$url,$lid=null){
		$this->mRoomNo=$rno;
		$this->mDescription=$desc;
		$this->mImageUrl=$url;
		$this->mLocationId=$lid;
	}
	public static function createFromScratch($roomNo,$description,$url)
	{
		$titleObj=Title::newFromText($title);
		$pageObj=WikiPage::factory($titleObj);
		$text=Xml::element('location',array('roomNo'=>$roomNo,'description'=>$description,'url'=>$imageUrl));
		$status=$page->doEdit($text, 'new location added',EDIT_NEW);	
		if($status['revision'])
		$revision=$status['revision'];
		$locationId=$revision->getPage();
		//$dbw=wfGetDB(DB_MASTER);
		//$dbw->insert('page_props',array('pp_page'=>$locationId,'pp_propname'=>'type','pp_value'=>'location'),__METHOD__,array());
		return new self($roomNo,$description,$url,$locationId);
	}
	public static function loadFromId($locationId)
	{
		$article=Article::newFromID($locationId);
		$text=$article->fetchContent();
		/**
		 * parse content and get the values for roomNo,description,$url
		 */
		return new self($rno, $desc, $url, $id);
	}
	public function getLocationId()
	{
		return $this->mLocationId;
	}
	public function setLocationId($id)
	{
		$this->mLocationId=$id;
	}
	public function getRoomNo()
	{
		return $this->mRoomNo;
	}
	public function setRoomNo($no)
	{
		$this->mRoomNo=$no;
	}
	public function getDescription()
	{
		return $this->mDescription;
	}
	public function setDescription($desc)
	{
		$this->mDescription=$desc;
	}
	public function getImageUrl()
	{
		return $this->mImageUrl;
	}
	public function setImageUrl($url)
	{
		$this->mImageUrl=$url;
	}
}