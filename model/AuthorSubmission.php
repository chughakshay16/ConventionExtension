<?php
class AuthorSubmission
{
	private $mSubmissionId,$mAuthorId,$mTitle,$mType,$mAbstract,$mTrack,$mLength,$mSlidesInfo,$mSlotRequest;
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $id
	 * @param unknown_type $aid
	 * @param unknown_type $title
	 * @param unknown_type $type
	 * @param unknown_type $abstract
	 * @param unknown_type $track
	 * @param unknown_type $length
	 * @param unknown_type $slidesInfo
	 * @param unknown_type $slotReq
	 */
	public function __construct($id, $aid, $title,$type,$abstract, $track, $length, $slidesInfo, $slotReq)
	{
		$this->mSubmissionId=$id;
		$this->mAuthorId=$aid;
		$this->mTitle=$title;
		$this->mType=$type;
		$this->mAbstract=$abstract;
		$this->mTrack=$track;
		$this->mLength=$length;
		$this->mSlidesInfo=$slidesInfo;
		$this->mSlotRequest=$slotReq;
	}
		/**
	 * @todo check for conditions if the database call fails
	 * Enter description here ...
	 * @param unknown_type $aid
	 * @param unknown_type $title
	 * @param unknown_type $type
	 * @param unknown_type $abstract
	 * @param unknown_type $track
	 * @param unknown_type $length
	 * @param unknown_type $slidesInfo
	 * @param unknown_type $slotReq
	 */
	public static function createFromScratch($aid, $title,$type,$abstract, $track, $length, $slidesInfo, $slotReq)
	{
		$titleObj=Title::newFromText($title);
		$pageObj=WikiPage::factory($titleObj);
		$text=Xml::element('submission',array('title'=>$title,'submissionType'=>$type,'abstract'=>$abstract,
		'track'=>$track,'length'=>$length,'slidesInfo'=>$slidesInfo,'slotReq'=>$slotReq,'submission-author'=>$aid));
		$status=$page->doEdit($text, 'new submission added',EDIT_NEW);	
		if($status['revision'])
		$revision=$status['revision'];
		$submissionId=$revision->getPage();
		$dbw=wfGetDB(DB_MASTER);
		$properties=array('submission-author'=>$aid);
		foreach($properties as $name=>$value)
		{
			$dbw->insert('page_props',array('pp_page'=>$submissionId,'pp_propname'=>$name,'pp_value'=>$value),__METHOD__,array());
		}
		
		return new self($submissionId, $aid, $title,$type,$abstract, $track, $length, $slidesInfo, $slotReq);
	}
	public static function loadFromId($submissionId)
	{
		$article=Article::newFromID($submissionId);
		$text=$article->fetchContent();
		/**
		 * parse content
		 */
		/*wfProfileIn(__METHOD__.'-db');
		$dbr=wfGetDB(DB_SLAVE);
		$res = $dbr->selectRow( 'page_props',
		array('pp_propertyname','pp_value'),
		array( 'pp_page' => $submissionId,'pp_propertyname'=>'parent'),
		__METHOD__,
		array()
		);
		wfProfileOut(__METHOD__.'-db');*/
		return new self($submissionId, $res->pp_value, $title, $type, $abstract, $track, $length, $slidesInfo, $slotReq);
	}
	public function __construct(){}
}