<?php
class ConferenceOrganizer
{
	/**
	 * 
	 * page_id of the organizer page
	 * @var Int
	 */
	private $mOrganizerId;
	/**
	 * 
	 * page_id of the conference page
	 * @var Int
	 */
	private $mConferenceId;
	/**
	 * 
	 * user_id of the organizer
	 * @var Int
	 */
	private $mUserId;
	/**
	 * 
	 * Combination of category and post stored in a multi-dimensional array
	 * just like this :
	 * array(array('cat1','post1'),array('cat2','post2')....)
	 * @var Array
	 */
	private $mCategoryPostCombination;
	
	public function __construct($oid=null,$cid,$uid,$catpost){
		$this->mConferenceId=$cid;
		$this->mUserId=$uid;
		$this->mCategoryPostCombination=$catpost;
		$this->mOrganizerId=$mid;
			
	}
	/**
	 * @param Int $cid page_id of the conference page
	 * @param Int $uid user_id of the organizer
	 * @param String $cat - category for the organizer
	 * @param String $post - position within that category for the organizer
	 * @return ConferenceOrganizer
	 * If the user is already an organizer for this conference this function just edits the content, whereas if its an organizer for a different Conference 
	 * it just adds a new organizer page
	 * @todo editing logic
	 */
	public static function createFromScratch($cid,$uid,$catpost)
	{
		// do add the logic for having csv value for category
		$isOrganizerForConference=ConferenceOrganizerUtils::isOrganizerFromConference($uid, $cid);
		$titleObj=Title::newFromText($title);
		$pageObj=WikiPage::factory($titleObj);
		if($isOrganizerForConference===false)
		{
			$text=Xml::element('organizer',array('category'=>$catpost[0]['cat'],'post'=>$catpost[0]['post'],'cvext-organizer-conf'=>$cid,'cvext-organizer-user'=>$uid));
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
			//and fetch all the other categories and modfiy the variable $catpost
			// take care of adding changes to other tables such as RecentChanges, Logs.. just like how doEdit() method does
			if($pageObj->exists())
			{
				$id=$pageObj->getId();
				$article=Article::newFromID($id);
				$content=$article->fetchContent();
				//now modify the content and extract all the catpost values
				$pageObj->doEdit($content,'added a pair of category and post',EDIT_UPDATE);
			}
		}
		return new self($id,$cid, $uid, $catpost);
		
	}
	/**
	 * 
	 * Loads the organizer object from the database
	 * @param Int $organizerId
	 */
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
	/**
	 * 
	 * getter function
	 */	
	public function getOrganizerId()
	{
		return $this->mOrganizerId;
	}
	/**
	 * 
	 * setter function
	 * @param Int $id
	 */
	public function setOrganizerId($id)
	{
		$this->mOrganizerId=$id;
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
	public function getUserId()
	{
		return $this->mUserId;
	}
	/**
	 * 
	 * setter function
	 * @param Int $id
	 */
	public function setUserId($id)
	{
		$this->mUserId=$id;
	}
	/**
	 * 
	 * getter function
	 */
	public function getCategory()
	{
		$this->mCategory;
	}
	/**
	 * 
	 * setter function
	 * @param String $cat
	 */
	public function setCategory($cat)
	{
		$this->mCategory=$cat;
	}
	/**
	 * 
	 * getter function
	 */
	public function getPost()
	{
		return $this->mPost;
	}
	/**
	 * 
	 * setter function
	 * @param String $post
	 */
	public function setPost($post)
	{
		$this->mPost=$post;
	}
}