<?php
class ConferenceOrganizer
{
	private $mOrganizerId,$mConferenceId,$mUserId,$mCategory,$mPost;
	
	public function __construct($oid=null,$cid,$uid,$cat,$post){
		$this->mConferenceId=$cid;
		$this->mUserId=$uid;
		$this->mCategory=$cat;
		$this->mPost=$post;
		$this->mOrganizerId=$mid;
			
	}
	private function create()
	{
		/**
		 * if($this->mOrganizerId==null)
		 * 1. create page with XML tag <organizer cat='' post=''>
		 * 2. set page_properties with parent=conference_id, user=user_id, type='organizer'
		 * 3. set $this->mOrganizerId= page_id of the page created 
		 */
	}
	public static function createFromScratch($cid,$uid,$cat,$post)
	{
		$titleObj=Title::newFromText($title);
		$pageObj=WikiPage::factory($titleObj);
		$text=Xml::element('organizer',array('category'=>$cat,'post'=>$post,'organizer-conf'=>$cid,'organizer-user'=>$uid));
		$status=$page->doEdit($text, 'new organizer added',EDIT_NEW);	
		if($status['revision'])
		$revision=$status['revision'];
		$id=$revision->getPage();
		$properties=array('organizer-conf'=>$cid,'organizer-user'=>$uid);
		$dbw=wfGetDB(DB_MASTER);
		foreach ($properties as $name=>$value)
		{
			$dbw->insert('page_props', array('pp_page'=>$id,'pp_propertyname'=>$name,'pp_value'=>$value));
		}
		return new self($id,$cid, $uid, $cat, $post);
	}
	public static function loadFromId($organizerId)
	{
		$article=Article::newFromID($organizerId);
		$text=$article->fetchContent();
		/**
		 * parse content
		 */
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
		return new self($organizerId,$cid, $uid, $cat, $post);
		
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