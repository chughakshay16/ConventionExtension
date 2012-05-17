<?php
class ConferenceOrganizer
{
	private $mOrganizerId,$mConferenceId,$mUserId,$mCategories,$mPost;
	
	public function __construct($oid=null,$cid,$uid,$cat=array(),$post){
		$this->mConferenceId=$cid;
		$this->mUserId=$uid;
		$this->mCategories=$cat;
		$this->mPost=$post;
		$this->mOrganizerId=$mid;
			
	}
	/**
	 * @param Int $cid page_id of the conference page
	 * @param Int $uid user_id of the organizer
	 * @param String $cat - category for the organizer
	 * @param String $post - position within that category for the organizer
	 * @return ConferenceOrganizer
	 */
	public static function createFromScratch($cid,$uid,$cat,$post)
	{
		// do add the logic for having csv value for category
		$isOrganizerForConference=ConferenceOrganizerUtils::isOrganizerFromConference($uid, $cid);
		$titleObj=Title::newFromText($title);
		$pageObj=WikiPage::factory($titleObj);
		if($isOrganizerForConference===false)
		{
			$text=Xml::element('organizer',array('category'=>$cat,'post'=>$post,'cvext-organizer-conf'=>$cid,'cvext-organizer-user'=>$uid));
			$status=$page->doEdit($text, 'new organizer added',EDIT_NEW);	
			if($status->value['revision'])
			{
				$revision=$status->value['revision'];
				$id=$revision->getPage();
				$properties=array('cvext-organizer-conf'=>$cid,'cvext-organizer-user'=>$uid);
				$dbw=wfGetDB(DB_MASTER);
				foreach ($properties as $name=>$value)
				{
					$dbw->insert('page_props', array('pp_page'=>$id,'pp_propertyname'=>$name,'pp_value'=>$value));
				}
				
			}
			else
			{
			//do something here
			}
		}
		else 
		{
			//just add one more category to the cat attribute 
			//and fetch all the other categories and modfiy the variable $cat
			// take care of adding changes to other tables such as RecentChanges, Logs.. just like how doEdit() method does
			if($pageObj->exists())
			{
				$id=$pageObj->getId();
			}
		}
		return new self($id,$cid, $uid, $cat, $post);
		
	}
	public static function loadFromId($organizerId)
	{
		$article=Article::newFromID($organizerId);
		$text=$article->fetchContent();
		preg_match_all("/<organizer category=\"(.*)\" post=\"(.*)\" cvext-organizer-conf=\"(.*)\" cvext-organizer-user=\"(.*)\" \/>/",$text,$matches);
		/*$dbr=wfGetDB(DB_SLAVE);
		$res=$dbr->select('page_props',
		array('pp_propertyname','pp_value'),
		array('pp_page'=>$organizerId),
		__METHOD__,
		array());
		foreach($res as $row)
		{
			if($res->pp_propertyname=='parent')
			$cid=$res->pp_value;
			else if($res->pp_propertyname=='user')
			$uid=$res->pp_value;
			else
			{}
		}*/
		return new self($organizerId,$matches[3][0], $matches[4][0], $matches[1][0], $matches[2][0]);
		
	}
	public static function render($input, array $args, Parser $parser, PPFrame $frame)
	{
		$ids=array();
		foreach ($args as $attribute=>$value)
		{
			if($attribute=='cvext-organizer-conf')
			{
				$ids[]=$value;
			}
			if($attribute=='cvext-organizer-user')
			{
				$ids[]=$value;
			}
			
		}
		$id=$parser->getTitle()->getArticleId();
		$dbw=wfGetDB(DB_MASTER);
		foreach ($ids as $name=>$value)
		{
			$dbw->insert('page_props',array('pp_page'=>$id,'pp_propname'=>$name,'pp_value'=>$value));
		}
		return '';
	}	
		
	public function getOrganizerId()
	{
		return $this->mOrganizerId;
	}
	public function setOrganizerId($id)
	{
		$this->mOrganizerId=$id;
	}
	public function getConferenceId()
	{
		return $this->mConferenceId;
	}
	public function setConferenceId($id)
	{
		$this->mConferenceId=$id;
	}
	public function getUserId()
	{
		return $this->mUserId;
	}
	public function setUserId($id)
	{
		$this->mUserId=$id;
	}
	public function getCategory()
	{
		$this->mCategory;
	}
	public function setCategory($cat)
	{
		$this->mCategory=$cat;
	}
	public function getPost()
	{
		return $this->mPost;
	}
	public function setPost($post)
	{
		$this->mPost=$post;
	}
}