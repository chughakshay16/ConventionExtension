<?php
class ConferenceAuthor
{
	/**
	 * 
	 * The page_id of the parent author
	 * @var Int
	 */
	private $mAuthorId;
	/**
	 * 
	 * An array containing the ids of confernces for which this author has submitted a proposal
	 * @var Array
	 */
	private $mConferenceIds;
	/**
	 * 
	 * user_id for the parent author
	 * @var Int
	 */
	private $mUserId;
	/**
	 * 
	 * Country which author belongs to
	 * @var String
	 */
	private $mCountry;
	/**
	 * Author's country
	 * @var String
	 */
	private $mAffiliation;
	/**
	 * 
	 * Author's blog url
	 * @var unknown_type
	 */
	private $mBlogUrl;
	/**
	 * 
	 * An array containing the proposals made by this author(it includes the proposals from all the conferences)
	 * @var Array
	 */
	private $mSubmissions;
	/**
	 * 
	 * constructor function
	 * @param Int $aid
	 * @param Array $cids
	 * @param Int $uid
	 * @param String $country
	 * @param String $affiliation
	 * @param String $url
	 * @param Array $submissions
	 */
	public function __construct($aid=null, $cids=array(), $uid, $country , $affiliation, $url,$submissions=null){
		$this->mAffiliation=$affiliation;
		$this->mAuthorId=$aid;
		$this->mBlogUrl=$url;
		$this->mConferenceIds=$cids;
		$this->mUserId=$uid;
		$this->mSubmissions=$submissions;
		$this->mCountry=$country;

	}
		/**
	 * @param Int $cid page_id of the conference page
	 * @param Int $uid user_id for the speaker
	 * @param String $country
	 * @param String $affiliation
	 * @param String $url
	 * @param Object(AuthorSubmission) $submission - this object only contains the info passed with the form, other author-id 
	 * and ids are set in this function itself
	 * @return ConferenceAuthor
	 * This is a bit different from how its implemented in ConferenceAccount
	 * So in cases where parent, or parent and child both are present this is the function which we would call
	 * For example , if we are adding a new submission for the already created parent and child authors we are gonna call this function with 
	 * the appropriate ConferenceSubmission object
	 * This function takes care of all the various possible scenarios that may occur in the creation of a ConferenceAuthor object
	 * 1. When the parent author is not present
	 * 2. When the parent author exists but child author absent
	 * 3. When both parent and child authors exist
	 */
	public static function createFromScratch($cid, $uid, $country , $affiliation, $url,$submission=null)
	{
		$newParent=!(ConferenceAuthorUtils::hasParentAuthor($uid));
		$confTitle=ConferenceUtils::getTitle($cid);
		$userName=UserUtils::getUsername($uid);
		$titleParent='authors/'.$userName;
		$properties=array();
		$titleObj=Title::newFromText($titleParent);
		$pageObj=WikiPage::factory($titleObj);
		if($newParent)
		{
			
			$text=Xml::element('author',array('country'=>$country,'affiliation'=>$affiliation,'blogUrl'=>$url,'cvext-author-user'=>$uid));
			$status=$pageObj->doEdit($text, 'new parent author added',EDIT_NEW);
			if($status->value['revision'])
			{
				$revision=$status->value['revision'];
				$id=$revision->getPage();
			
			} else {
			//do something here
			}
		} else {
			if($pageObj->exists())
			{
				$id=$pageObj->getId();
			}
		}
		$newChild=!(ConferenceAuthorUtils::hasChildAuthor($id, $cid));
		$username=UserUtils::getUsername($uid);
		$titleChild=$confTitle.'/authors/'.$username;
		$titleChildObj=Title::newFromText($titleChild);
		$pageChildObj=WikiPage::factory($titleChildObj);
		if($newChild)
		{
			$childText=Xml::element('author-sub',array('cvext-author-parent'=>$id,'cvext-author-conf'=>$cid));
			$statusChild=$pageChildObj->doEdit($childText,'new sub author added',EDIT_NEW);
			if($statusChild->value['revision'])
			{
				$revisionChild=$statusChild->value['revision'];
				$idChild=$revisionChild->getPage();
				
				
			} else {
				//do something here
			}
		} else {
				if($pageChildObj->exists())
				{
					$idChild=$pageChildObj->getId();
				}
			
			}
			if($newParent)
			{
				$properties[]=array('id'=>$id,'prop'=>'cvext-author-user','value'=>$uid);
				if($newChild)
				{
					$properties[]=array('id'=>$idChild,'prop'=>'cvext-author-parent','value'=>$id);
					$properties[]=array('id'=>$idChild,'prop'=>'cvext-author-conf','value'=>$cid);
				}
				
			}
			$dbw=wfGetDB(DB_MASTER);
			foreach($properties as $value)
			{
				$dbw->insert('page_props',array('pp_page'=>$value['id'],'pp_propname'=>$value['prop'],'pp_value'=>$value['value']));
			}
			$submission=AuthorSubmission::createFromScratch($idChild, $submission->getTitle(), $submission->getType(), 
			$submission->getAbstract(), $submission->getTrack(), $submission->getLength(), $submission->getSlidesInfo(), 
			$submission->getSlotReq());
			$submissions=array();
			$submissions[]=$submission;
			return new self($id,$cid, $uid, $country, $affiliation, $url,$submissions);
		
	}
	/**
	 * 
	 * deletes all the information related with a user-id 
	 * could also include a lot of other checks within the function
	 * @param Int user_id for the author $uid
	 * $result['done'] true/false depending on whether the delete was successful
	 * $result['cause'] cause for the fail if any occured otherwise its just empty string
	 */
	public static function performAuthorDelete($uid)
	{
		//we need to delete the whole chain of author starting from parent author and ending with submission
		//step 1. get all the sub-author ids
		//step 2. get all the submissions for all the corresponding sub-author ids
		//step 3. delete all the submissions
		//step 4. delete all the sub-author ids
		//step 5. delete the parent author
		$result=array();
		$authorId=ConferenceAuthorUtils::getAuthorId($uid);
		if($authorId)
		{
			$dbr=wfGetDB(DB_SLAVE);
			$result=$dbr->select("page_props",
			"pp_page",
			array("pp_propname"=>"cvext-account-parent","pp_value"=>$authorId),
			__METHOD__,
			array(),
			array());
			$subAuthorIds=array();
			foreach ($result as $row)
			{
				$subAuthorIds[]=$row->pp_page;	
			}
			$resultSubs=$dbr->select("page_props",
			"pp_page",
			array("pp_value IN (".implode(",",$subAuthorIds).")","pp_propname"=>"cvext-submission-author"),
			__METHOD__);
			//now at this point we have the ids of all the pages that we would want to delete
			//we are gonna first start from the bottom of the chain
			foreach ($resultSubs as $row)
			{
				$page=WikiPage::newFromID($row->pp_page);
				
				//this even deletes the entries in page_props table
				$status=$page->doDeleteArticle("deleting submission as the parent author was deleted",Revision::DELETED_TEXT);
				if($status!==true)
				{
					$result['done']=false;
					$result['cause']='submission delete fail';
					$result['flag']=Conference::ERROR_DELETE;
					return $result;
				}
			}
			foreach ($subAuthorIds as $subAuthId)
			{
				$page=WikiPage::newFromID($subAuthId);
				$status=$page->doDeleteArticle("deleting sub author as the parent author was deleted",Revision::DELETED_TEXT);
				if($status!==true)
				{
					$result['done']=false;
					$result['cause']="sub-author delete fail";
					$result['flag']=Conference::ERROR_DELETE;
					return $result;
				}
			}
			$page=WikiPage::newFromID($authorId);
			$status=$page->doDeleteArticle("parent author was deleted by the user",Revision::DELETED_TEXT);
			if($status!==true)
			{
				$result['done']=false;
				$result['cause']='parent author delete fail';
				$result['flag']=Conference::ERROR_DELETE;
				return $result;
			}
			$result['done']=true;
			$result['cause']='';
			$result['flag']=Conference::SUCCESS_CODE;
		} else {
			$result['done']=false;
			$result['cause']='no parent author found for the user';
			$result['flag']=Conference::ERROR_MISSING;
			return $result;
		}
		return $result;
		
		
	}
	/**
	 * 
	 * deletes the submission from the database
	 * @param Int $uid
	 * @param Int $cid
	 * @param String title of the submission $title
	 * @return $result
	 * $result['done'] - true/false depending upon whether the process was successful or not
	 * $result['msg'] - message for success or failure
	 */
	public static function performSubmissionDelete($uid,$cid,$title)
	{
		//step 1. just extract the sub-author id
		//step 2. get all the submissions for that sub-author id
		//step 3. delete all those submissions
		$confTitle=ConferenceUtils::getTitle($cid);
		$username=UserUtils::getUsername($uid);
		$titleText=$confTitle.'/authors/'.$username.'/submissions/'.$title;
		$title=Title::newFromText($titleText);
		$page=WikiPage::factory($title);
		$result=array();
		if($page->exists())
		{
			$status=$page->doDeleteArticle("submission deleted by the author",Revision::DELETED_TEXT);
			if($status===true)
			{
				$result['done']=true;
				$result['msg']='Submission has been successfully deleted';
				$result['flag']=Conference::SUCCESS_CODE;
			} else {
				$result['done']=false;
				$result['msg']="Submission couldnt be deleted";
				$result['flag']=Conference::ERROR_DELETE;
			}
		} else {
			$result['done']=false;
			$result['msg']="Submission with the given title was not found in the database";
			$result['flag']=Conference::ERROR_MISSING;
		}
		return $result;
	}
	/**
	 * 
	 * deletes the sub-author and its related submissions
	 * @param Int $uid
	 * @param Int $cid
	 * @return $result associative array
	 * 	$result['done'] true/false depending on whether the delete was successful
	 * 	$result['cause'] cause for the fail if any occured otherwise its just empty string
	 */
	public static function performSubAuthorDelete($uid,$cid)
	{
		$confTitle=ConferenceUtils::getTitle($cid);
		$username=UserUtils::getUsername($uid);
		$titleText=$confTitle.'/authors/'.$username;
		$title=Title::newFromText($titleText);
		$page=WikiPage::factory($title);
		$result=array();
		if($page->exists())
		{
			$id=$page->getId();
			//now extract all the submissions corresponding to this sub-author id
			$dbr=wfGetDB(DB_SLAVE);
			$result=$dbr->select("page_props",
			"pp_page",
			array("pp_propname"=>"cvext-submission-author","pp_value"=>$id),
			__METHOD__);
			if($dbr->numRows($result)>0)
			{
				foreach ($result as $row)
				{
					$submissionPage=WikiPage::newFromID($row->pp_page);
					$status=$submissionPage->doDeleteArticle("submission deleted as the sub-author was deleted",Revision::DELETEED_TEXT);
					if($status!==true)
					{
						$result['done']=false;
						$result['cause']='submission delete fail';
						$result['flag']=Conference::ERROR_DELETE;
						return $result;
					}
					
				}
				
			}
			//now all submissions have been deleted
			$page->doDeleteArticle("sub-author was deleted by the user",Revision::DELETED_TEXT);
			if($status!==true)
			{
				$result['done']=false;
				$result['cause']='sub-author delete fail';
				$result['flag']=Conference::ERROR_DELETE;
				return $result;
			}
		} else {
			$result['done']=false;
			$result['cause']="the user doesnt have any sub-author for this conference";
			$result['flag']=Conference::ERROR_MISSING;
			return $result;
		}
		$result['done']=true;
		$result['cause']="The sub-author was successfully deleted";
		$result['flag']=Conference::SUCCESS_CODE;
		return $result;
	}
	/**
	 * 
	 * edits the author details in the database
	 * @param Int $uid
	 * @param String $country
	 * @param String $affiliation
	 * @param String $url
	 * @return $result 
	 * $result['done'] - true/false depending upon whether the process was carried out successfully or not
	 * $result['msg'] - message for success or failure
	 */
	public static function performAuthorEdit($uid,$country, $affiliation, $url)
	{
		$username=UserUtils::getUsername($uid);
		$titleText='/authors/'.$username;
		$title=Title::newFromText($titleText);
		$page=WikiPage::factory($title);
		$result=array();
		if($page->exists())
		{
			$id=$page->getId();
			$article=Article::newFromID($id);
			$content=$article->fetchContent();
			preg_match_all("/<author country=\"(.*)\" affiliation=\"(.*)\" blogUrl=\"(.*)\" cvext-author-user=\"(.*)\" \/>/",$content,$matches);
			//we will never be modifying the cvext-author-user property
			if (!$country)
			{
				$country = $matches[1][0];
			}
			if (!$affiliation)
			{
				$affiliation = $matches[2][0];
			}
			if(!$url)
			{
				$url = $matches[3][0];
			}
			
			$newTag = Xml::element('author',array('country'=>$country,'affiliation'=>$affiliation,'blogUrl'=>$url,'cvext-author-user'=>$matches[4][0]));
			
			$content = preg_replace("/<author country=\".*\" affiliation=\".*\" blogUrl=\".*\" cvext-author-user=\".*\" \/>/",$newTag, $content);
			$status=$page->doEdit($content,"Author with $username has been modified",EDIT_UPDATE);
			if($status->value['revision'])
			{
				$result['done']=true;
				$result['msg']="Author info has been successfully edited";
				$result['flag']=Conference::SUCCESS_CODE;
			} else {
				$result['done']=false;
				$result['msg']="The author details couldnt be edited";
				$result['flag']=Conference::ERROR_EDIT;
			}
		} else {
			$result['done']=false;
			$result['msg']="The author with this username doesnt exist in the database";
			$result['flag']=Conference::ERROR_MISSING;
		}
		return $result;
	}
	/**
	 * 
	 * edits the submission details in the database
	 * @param Int $cid
	 * @param Int $uid
	 * @param String $title
	 * @param String $type
	 * @param String $abstract
	 * @param String $track
	 * @param String $length
	 * @param String $slidesInfo
	 * @param String $slotReq
	 * @return $result 
	 * $result['done']true/false depending on whether the operation was successful or not
	 * $result['msg']- message for failure or success
	 */
	public static function performSubmissionEdit($cid,$uid,$title,$type,$abstract, $track, $length, $slidesInfo, $slotReq)
	{
		$confTitle=ConferenceUtils::getTitle($cid);
		$username=UserUtils::getUsername($uid);
		$titleText=$confTitle.'/authors/'.$username.'/submissions/'.$title;
		$title=Title::newFromText($titleText);
		$page=WikiPage::factory($title);
		$result=array();
		if($page->exists())
		{
			$id=$page->getId();
			$article=Article::newFromID($id);
			$content=$article->fetchContent();
			preg_match_all("/<submission title=\"(.*)\" submissionType=\"(.*)\" abstract=\"(.*)\" track=\"(.*)\" length=\"(.*)\" slidesInfo=\"(.*)\" slotReq=\"(.*)\" cvext-submission-author=\"(.*)\" \/>/",$content,$matches);
			if(!$title)
			{
				$title = $matches[1][0];
			}
			if(!$type)
			{
				$type = $matches[2][0];
			}
			if(!$abstract)
			{
				$abstract = $matches[3][0];
			}
			if(!$track)
			{
				$track = $matches[4][0];
			}
			if(!$length)
			{
				$length = $matches[5][0];
			}
			if(!$slidesInfo)
			{
				$slidesInfo = $matches[6][0];
			}
			if(!$slotReq)
			{
				$slotReq = $matches[7][0];
			}
			
			$newTag = Xml::element('submission',array('title'=>$title,'submissionType'=>$type,'abstract'=>$abstract,
			'track'=>$track,'length'=>$length,'slidesInfo'=>$slidesInfo,'slotReq'=>$slotReq,'cvext-submission-author'=>$matches[8][0]));
			
			$content = preg_replace("/<submission title=\".*\" submissionType=\".*\" abstract=\".*\" track=\".*\" length=\".*\" slidesInfo=\".*\" slotReq=\".*\" cvext-submission-author=\".*\" \/>/", $newTag, $content);
			
			$status=$page->doEdit($content,'Submission details has been successfully modified',EDIT_UPDATE);
			if($status->value['revision'])
			{
				$result['done']=true;
				$result['msg']="The submission details have been successfully edited";	
				$result['flag']=Conference::SUCCESS_CODE;
			} else {
				$result['done']=false;
				$result['msg']="The submission details could not be modified";
				$result['flag']=Conference::ERROR_EDIT;
				
			}	
		} else {
			$result['done']=false;
			$result['msg']="The submission with this title name doesnt exist in the database";
			$result['flag']=Conference::ERROR_MISSING;
		}	
	}
	/**
	 * @param Int $authorId (this is the page_id of the parent author)
	 * @return ConferenceAuthor
	 * This function loads the ConferenceAuthor object from the database
	 */
	public static function loadFromId($authorId)
	{
		$article=Article::newFromID($authorId);
		$text=$article->fetchContent();
		preg_match_all("/<author country=\"(.*)\" affiliation=\"(.*)\" blogUrl=\"(.*)\" cvext-author-user=\"(.*)\" \/>/",$text,$matches);
		$dbr=wfGetDB(DB_SLAVE);
		/*$dbr->select('page_props',
		array('pp_propertyname','pp_ value'),
		array('pp_page'=>$speakerId),
		__METHOD__,
		array());
		foreach($res as $row)
		{
			if($row->pp_propertyname=='parent')
			$cid=$row->pp_value;
			else if($row->pp_value=='user')
			$uid=$row->pp_value;
			else {}
		}*/
		//get all the sub authors
		$resSub=$dbr->select('page_props',
		'*',
		array('pp_value'=>$authorId,'pp_propname'=>'cvext-author-parent'),
		__METHOD__,
		array());
		$subIds=array();
		foreach ($resSub as $row)
		{
			$subIds[]=$row->pp_page;
		}
		$resConf=$dbr->select('page_props',
		'*',
		array('pp_page IN ('.implode(',', $subIds).')','pp_propname'=>'cvext-author-conf'),
		__METHOD__);
		$conferenceIds=array();
		foreach ($resConf as $row)
		{
			$conferenceIds[]=array('sub-author'=>$row->pp_page,'conf'=>$row->pp_value);
		}
		$res=$dbr->select('page_props',
		array('pp_page','pp_value'),
		array('pp_value IN ('.implode(',',$subIds).')','pp_propname'=>'cvext-submission-author'),
		__METHOD__,
		array());
		$submissions=array();
		//here one sub-author can contain more than one submissions
		foreach($res as $row)
		{
			foreach ($conferenceIds as $combo)
			{
				if($row->pp_value==$combo['sub-author'])
				{
					$conferenceId = $combo['conf'];
					$subauthorId = $combo['sub-author'];
				}
			}
			$key = 'conf-'.$conferenceId;
			$submissions[$key]['conf']=$conferenceId;
			$submissions[$key]['sub-author']=$subauthorId;
			$submissions[$key]['submissions'][]=AuthorSubmission::loadFromId($row->pp_page);			
		}
		return new self($authorId,$conferenceIds, $matches[4][0], $matches[1][0], $matches[2][0], $matches[3][0],$submissions);
	}
	/**
	 * 
	 * 
	 * @param String $input - text contained within the tag
	 * @param Array $args - an array of tag attributes
	 * @param Parser $parser
	 * @param PPFrame $frame
	 */
	public static function render($input, array $args, Parser $parser, PPFrame $frame)
	{

		$dbw=wfGetDB(DB_MASTER);
		$authorId=$parser->getTitle()->getArticleId();
		if($authorId!=0)
		{
			$dbw->insert('page_props',array('pp_page'=>$authorId,'pp_propname'=>'cvext-author-user','pp_value'=>$args['cvext-author-user']));
		}	
		return '';
	}
	/**
	 * 
	 * @param String $input - text contained within the tag
	 * @param Array $args - an array of tag attributes
	 * @param Parser $parser
	 * @param PPFrame $frame
	 */
	public static function renderSub($input, array $args, Parser $parser, PPFrame $frame)
	{
		$dbw=wfGetDB(DB_MASTER);
		$authorId=$parser->getTitle()->getArticleId();
		if($authorId!=0)
		{
			$properties=array(array('id'=>$authorId,'prop'=>'cvext-author-conf','value'=>$args['cvext-author-conf']),array('id'=>$authorId,'prop'=>'cvext-author-parent','value'=>$args['cvext-author-parent']));
			foreach ($properties as $property)
			{
				$dbw->insert('page_props',array('pp_page'=>$property['id'],'pp_propname'=>$property['prop'],'pp_value'=>$property['value']));
			}
		}
		
		return '';
	}
	/**
	 * 
	 * Returns the country value
	 */
	public function getCountry()
	{
		return $this->mCountry;
	}
	/**
	 * 
	 * Sets the country value
	 * @param String $county
	 */
	public function setCounry($county)
	{
		$this->mCountry=$country;
	}
	/**
	 * 
	 * Returns the affiliation value
	 */
	public function getAffiliation()
	{
		return $this->mAffiliation;
	}
	/**
	 * 
	 * Sets the affiliation value
	 * @param String $aff
	 */
	public function setAffiliation($aff)
	{
		$this->mAffiliation=$aff;
	}
	/**
	 * 
	 * Returns the url of the blog
	 */
	public function getBlogUrl()
	{
		return $this->mBlogUrl;
	}
	/**
	 * 
	 * Sets the blog url
	 * @param String $url
	 */
	public function setBlogUrl($url)
	{
		$this->mBlogUrl=$url;
	}
	/**
	 * 
	 * Returns the parent author id
	 */
	public function getAuthorId()
	{
		return $this->mAuthorId;
	}
	/**
	 * 
	 * Sets the parent author id
	 * @param Int $id
	 */
	public function setAuthorId($id)
	{
		$this->mAuthorId=$id;
	}
	/**
	 * 
	 * Returns an array of conference ids
	 */
	public function getConferenceIds()
	{
		return $this->mConferenceIds;
	}
	/**
	 * 
	 * Sets the value of the conference Ids
	 * @param Array $id
	 */
	public function setconferenceIds($id)
	{
		$this->mConferenceIds=$id;
	}
	/**
	 * 
	 * Returns the user_id for the author object
	 */
	public function getUserId()
	{
		return $this->mUserId;
	}
	/**
	 * 
	 * Sets the user_id for the author object
	 * @param unknown_type $id
	 */
	public function setUserId($id)
	{
		$this->mUserId=$id;
	}
	/**
	 * 
	 * Returns an array of submissions made by the author
	 */
	public function getSubmissions()
	{
		return $this->mSubmissions;
	}
	/**
	 * 
	 * Sets an array of submissions
	 * @param unknown_type $submissions
	 */
	public function setSubmissions($submissions)
	{
		$this->mSubmissions=$submissions;
	}

}