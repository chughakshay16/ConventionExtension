<?php
class ConferencePage
{
	/**
	 * 
	 * page_id for the wiki page
	 * @var Int
	 */
	private $mId;
	/**
	 * 
	 * page_id of the conference wiki page
	 * @var Int
	 */
	private $mConferenceId;
	/**
	 * 
	 * Type/Name of the page
	 * @var String
	 */
	private $mType;
	/**
	 * 
	 * Constructor function
	 * @param Int $mId
	 * @param Int $mConferenceId
	 * @param String $mType
	 */
	public function __construct($mId,$mConferenceId,$mType)
	{
		$this->mId=$mId;
		$this->mConferenceId=$mConferenceId;
		$this->mType=$mType;
	}
	/**
	 * @param Int $mConferenceId - page_id of the conference
	 * @param String $type type/name of the page(Main page, Registration page...)
	 * @param bool $default it tells whether to add default content or not
	 * @return ConferencePage
	 * For already defined types default content will be added to the page at the time of creation
	 */
	public static function createFromScratch($mConferenceId, $type ,$default=true)
	{
		$confTitle=ConferenceUtils::getTitle($mConferenceId);
		$title=$confTitle.'/pages/'.$type;
		$titleObj=Title::newFromText($title);
		$page=WikiPage::factory($titleObj);
		$text = Xml::element('page',array('cvext-page-conf'=>$mConferenceId,'cvext-page-type'=>$type));
		/**
		 * add default text to the page only if $defauult is true
		 * just check if the type belongs to the already defined types, if not then dont add any content other
		 * than <page> tag 
		 */
		$page->doEdit($text,'new page added',EDIT_NEW);
		if($status->value['revision'])
		{
			$revision=$status->value['revision'];
			$id=$revision->getPage();
			$properties=array('cvext-page-conf'=>$mConferenceId,'cvext-page-type'=>$type);
			$dbw=wfGetDB(DB_MASTER);
			foreach ($properties as $name=>$value)
			{
				$dbw->insert('page_props',array('pp_page'=>$id,'pp_propname'=>$name,'pp_value'=>$value));
			}
			return new self($id,$mConferenceId,$type);
		}
		else
		{
		//do something here
		}
	}
	/**
	 * @param Int $pageId page_id of the conference page
	 * @return ConferencePage
	 */
	public static function loadFromId($pageId)
	{
		$article=Article::newFromID($pageId);
		$text=$article->fetchContent();
		preg_match_all("/<page cvext-page-conf=\"(.*)\" cvext-page-type=\"(.*)\" \/>/",$text,$matches);
		$conferenceId=$matches[1][0];
		$type=$matches[2][0];
		return new self($pageId,$conferenceId,$type);
	}
	/**
	 * 
	 * deletes the conference page of the given type
	 * @param Int $cid
	 * @param String $type
	 * @return $result
	 * $result['done'] - true/false depending on if the deletion happened successfully or not
	 * $result['msg'] - success or failure message
	 */
	public static function performDelete($cid,$type)
	{
		$confTitle=ConferenceUtils::getTitle($cid);
		$titleText = $confTitle.'/pages/'.$type;
		$title=Title::newFromText($titleText);
		$page=WikiPage::factory($title);
		$result=array();
		if($page->exists())
		{
			$status=$page->doDeleteArticle("admin deletes the page of type ".$type,DELETED_TEXT);
			if($status===true)
			{
				$result['done']=true;
				$result['msg']="Page has been successfully deleted";
			} else {
				$result['done']=false;
				$result['msg']="The page couldnt be deleted";
			}
		} else {
			$result['done']=false;
			$result['msg']="The page with this title doesnt exist in the database";
		}
		
		return $result;
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
			$dbw=wfGetDB(DB_MASTER);
			foreach ($args as $name=>$value)
			{
				$dbw->insert('page_props',array('pp_page'=>$id,'pp_propname'=>$name,'pp_value'=>$value));
			}
		}
		
		return '';
	}
	/**
	 * 
	 * getter function
	 */
	public function getId()
	{
		return $this->mId;
	}
	/**
	 * 
	 * setter function
	 * @param Int $id
	 */
	public function setId($id)
	{
		$this->mId=$id;
	}
	/**
	 * 
	 * getter function
	 */
	public function getConferenceId()
	{
		return $this->mConferenceId;
	}
	/**
	 * 
	 * setter function
	 * @param Int $id
	 */
	public function setConferenceId($id)
	{
		$this->mConferenceId=$id;
	}
	/**
	 * 
	 * getter function
	 */
	public function gettype()
	{
		return $this->mType;
	}
	/**
	 * 
	 * setter function
	 * @param String $type
	 */
	public function setType($type)
	{
		$this->mType=$type;
	}
}