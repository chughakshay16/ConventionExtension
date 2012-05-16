<?php
class ConferencePage
{
	private $mId,$mConferenceId,$mType;
	public function __construct($mId,$mConferenceId)
	{
		$this->mId=$mId;
		$this->mConferenceId=$mConferenceId;
	}
	/**
	 * @param Int $mConferenceId - page_id of the conference
	 * @param String $type type of the page(Main page, Registration page...)
	 * @return ConferencePage
	 */
	public static function createFromScratch($mConferenceId, $type)
	{
		$titleObj=Title::newFromText($title);
		$page=WikiPage::factory($titleObj);
		$text = Xml::element('page',array('cvext-page-conf'=>$mConferenceId,'cvext-page-type'=>$type));
		/**
		 * add default text to the page
		 */
		$page->doEdit($text,'new page added',EDIT_NEW);
		if($status->value['revision'])
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
	public static function render($input, array $args, Parser $parser, PPFrame $frame)
	{
		$ids=array();
		foreach ($args as $attribute=>$value)
		{
			$ids[]=$value;
		}
		$id=$parser->getTitle()->getArticleId();
		$dbw=wfGetDB(DB_MASTER);
		foreach ($ids as $name=>$value)
		{
			$dbw->insert('page_props',array('pp_page'=>$id,'pp_propname'=>$name,'pp_value'=>$value));
		}
		return '';
	}
	public function getId()
	{
		return $this->mId;
	}
	public function setId($id)
	{
		$this->mId=$id;
	}
	public function getConferenceId()
	{
		return $this->mConferenceId;
	}
	public function setConferenceId($id)
	{
		$this->mConferenceId=$id;
	}
	public function gettype()
	{
		return $this->mType;
	}
	public function setType($type)
	{
		$this->mType=$type;
	}
}