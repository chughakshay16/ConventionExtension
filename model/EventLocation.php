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
	 * Constructor function
	 * @param String(number) $rno
	 * @param String $desc
	 * @param string $url
	 * @param Int $lid
	 */
	public function __construct($rno,$desc,$url,$lid=null){
		$this->mRoomNo=$rno;
		$this->mDescription=$desc;
		$this->mImageUrl=$url;
		$this->mLocationId=$lid;
	}
	/**
	 * @param String $roomNo
	 * @param String $description
	 * @param String $url
	 * @return EventLocation
	 */
	public static function createFromScratch($roomNo,$description,$url)
	{
		$titleObj=Title::newFromText($title);
		$pageObj=WikiPage::factory($titleObj);
		$text=Xml::element('location',array('roomNo'=>$roomNo,'description'=>$description,'url'=>$imageUrl,'cvext-type'=>'location'));
		$status=$page->doEdit($text, 'new location added',EDIT_NEW);	
		if($status->value['revision'])
		{
			$revision=$status->value['revision'];
			$locationId=$revision->getPage();
			$dbw=wfGetDB(DB_MASTER);
			$dbw->insert('page_props',array('pp_page'=>$locationId,'pp_propname'=>'cvext-type','pp_value'=>'location'),__METHOD__,array());
			return new self($roomNo,$description,$url,$locationId);
		}
		else
		{
			//do something here
		}
	}
	/**
	 * @param Int $locationId page_id of the location page
	 * @return EventLocation
	 */
	public static function loadFromId($locationId)
	{
		$article=Article::newFromID($locationId);
		$text=$article->fetchContent();
		preg_match_all("/<location roomNo=\"(.*)\" description=\"(.*)\" url=\"(.*)\" cvext-type=\"(.*)\" \/>/",$text,$matches);
		return new self($matches[1][0], $matches[2][0], $matches[3][0], $locationId);
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
		wfGetDB(DB_MASTER)->insert('page_props',array('pp_page'=>$parser->getTitle()->getArticleId()
		,'pp_propname'=>'cvext-type','pp_value'=>'location'));
		return '';
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