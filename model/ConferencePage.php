<?php
class ConferencePage
{
	private $mId,$mConferenceId,$mType;
	public function __construct($mId,$mConferenceId)
	{
		$this->mId=$mId;
		$this->mConferenceId=$mConferenceId;
	}
	public static function createFromScratch($mConferenceId, $type)
	{
		$titleObj=Title::newFromText($title);
		$page=WikiPage::factory($titleObj);
		$text = Xml::element('page',array('page-conf'=>$mConferenceId,'page-type'=>$type));
		/**
		 * add default text to the page
		 */
		$page->doEdit($text,'new page added',EDIT_NEW);
		if($status['revision'])
		$revision=$status['revision'];
		$id=$revision->getPage();
		$properties=array('page-conf'=>$mConferenceId,'page-type'=>$type);
		$dbw=wfGetDB(DB_MASTER);
		foreach ($properties as $name=>$value)
		{
			$dbw->insert('page_props',array('pp_page'=>$id,'pp_propname'=>$name,'pp_value'=>$value));
		}
		return new self($id,$mConferenceId,$type);
	}
	public static function loadFromId($pageId)
	{
		$article=Article::newFromID($pageId);
		$text=$article->fetchContent();
		/**
		 * parse the text and get the relevant info
		 */
		return new self($pageId,$conferenceId,$type);
	}
}