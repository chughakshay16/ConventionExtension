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
		$this->mOrganizerId=$oid;
			
	}
	/**
	 * @param Int $cid page_id of the conference page
	 * @param Int $uid user_id of the organizer
	 * @param String $cat - category for the organizer
	 * @param String $post - position within that category for the organizer
	 * @return ConferenceOrganizer
	 * If the user is already an organizer for this conference this function just edits the content, whereas if its an organizer for a different Conference 
	 * it just adds a new organizer page
	 * Although there is already a performEdit() function for adding cat,post values
	 */
	public static function createFromScratch($cid,$uid,$catpost)
	{

		$isOrganizerForConference=ConferenceOrganizerUtils::isOrganizerFromConference($uid, $cid);
		$confTitle=ConferenceUtils::getTitle($cid);
		$username=UserUtils::getUsername($uid);
		$title=$confTitle.'/organizers/'.$username;
		$titleObj=Title::newFromText($title);
		$pageObj=WikiPage::factory($titleObj);
		if($isOrganizerForConference===false)
		{
			$text=Xml::element('organizer',array('category'=>$catpost[0]['category'],'post'=>$catpost[0]['post'],'cvext-organizer-conf'=>$cid,'cvext-organizer-user'=>$uid));
			$status=$pageObj->doEdit($text, 'new organizer added',EDIT_NEW);	
			if($status->value['revision'])
			{
				$revision=$status->value['revision'];
				$id=$revision->getPage();
				$properties=array('cvext-organizer-conf'=>$cid,'cvext-organizer-user'=>$uid);
				$dbw=wfGetDB(DB_MASTER);
				foreach ($properties as $name=>$value)
				{
					$dbw->insert('page_props', array('pp_page'=>$id,'pp_propname'=>$name,'pp_value'=>$value));
				}
				
			}
			else
			{
				return new self(null,$cid, $uid, $catpost);
			}
		}
		else 
		{
			if($pageObj->exists())
			{
				$id = $pageObj->getId();
				$result = self::performEdit($cid, $username, $catpost);
				if($result['done'])
				{
					
					return new self($id,$cid, $uid, $result['catpost']);
					
				} else {
					return new self(null,$cid, $uid, null);
				}
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
		$categoryString = $matches[1][0];
		$postString = $matches[2][0];
		$categoryArray = explode(',',$categoryString);
		$postArray = explode(',',$postString);
		//no of elements in both postArray and categoryArray will be the same
		$catpost = array();
		foreach ($postArray as $index=>$post)
		{
			$catpost[] = array('category'=>$categoryArray[$index],'post'=>$post);
		}
		//$catpost=array(array('cat'=>$matches[1][0],'post'=>$matches[2][0]));
		return new self($organizerId,$matches[3][0], $matches[4][0], $catpost);
		
	}
	/**
	 * 
	 * Modifies the organizer wiki page in the database
	 * @param Int $cid
	 * @param String $username
	 * @param Array $catpost
	 * @return $result
	 * $result['done'] - true/false (success or failure)
	 * $result['msg'] - success or failure message
	 * This function kind of works for adding a new cat,post for the same organizer (ugly hack)
	 * if $catpostOld is not passed it means its an add operation, and if it is not null it will perform an edit operation on the same catpostOld
	 */
	public static function performEdit($cid,$username,$catpostNew , $catpostOld=null )
	{
		$confTitle=ConferenceUtils::getTitle($cid);
		//$username=UserUtils::getUsername($uid);
		$title=$confTitle.'/organizers/'.$username;
		$titleObj=Title::newFromText($title);
		$page=WikiPage::factory($titleObj);

		if(!count($catpostNew) || !count($catpostNew[0]))
		{
			$result['done']=false;
			$result['msg']='Both category and post must be present';
			$result['flag']=Conference::ERROR_EDIT;
			return $result;
		}

		$result=array();
		if($page->exists())
		{
			$id=$page->getId();
			$article=Article::newFromID($id);
			$content=$article->fetchContent();
			//check if the passed array is already present or not if not throw error
			preg_match_all("/<organizer category=\"(.*)\" post=\"(.*)\" cvext-organizer-conf=\"(.*)\" cvext-organizer-user=\"(.*)\" \/>/",$content,$matches);
			$categoryString = $matches[1][0];
			$postString = $matches[2][0];
			$categoryArray = explode(',', $categoryString);
			$postArray = explode(',', $postString);
			//The rule of thumb is that (category,post) combination should not be the same for the user in the same conference
			$available = false;
			foreach ($categoryArray as $index=>$category)
			{
				# check if the new pair is unique
				if($category==$catpostNew[0]['category'])
				{
					if ($catpostNew[0]['post']==$postArray[$index])
					{
						
						$available = true;
						break;
					}
				} 
				if( $catpostOld && $category == $catpostOld[0]['category'])
				{
					if($catpostOld[0]['post'] == $postArray[$index])
					{
						$changeIndex = $index;
					}
				}
			}
			
			if($available)
			{
				
				#error
				$result['done']=false;
				$result['msg']='The same (category,post) combination already available';
				$result['flag']=Conference::ERROR_EDIT;
				return $result;
				
			} elseif (isset($changeIndex)) {
				
				#edit
				$categoryArray[$changeIndex] = $catpostNew[0]['category'];
				$postArray[$changeIndex] = $catpostNew[0]['post'];
				
			} else {
				
				# add
				$categoryArray[]=$catpostNew[0]['category'];
				$postArray[]=$catpostNew[0]['post'];
				
			}
				
			
			$newCategoryPost = array();
			for ($i=0;$i<count($categoryArray);$i++)
			{
				$newCategoryPost[]= array('category'=>$categoryArray[$i],'post'=>$postArray[$i]);
			}
			$categoryString = implode(',', $categoryArray);
			$postString = implode(',', $postArray);
			$newTag = Xml::element('organizer',array('category'=>$categoryString,'post'=>$postString,'cvext-organizer-conf'=>$matches[3][0],'cvext-organizer-user'=>$matches[4][0]));
			$content = preg_replace("/<organizer category=\".*\" post=\".*\" cvext-organizer-conf=\".*\" cvext-organizer-user=\".*\" \/>/", $newTag, $content);
			$status=$page->doEdit($content, "organizer updated",EDIT_UPDATE);
			if($status->value['revision'])
			{
				$result['done']=true;
				$result['msg']="The organizer info has been successfully updated";
				$result['category'] = $catpostNew[0]['category'];
				$result['post'] = $catpostNew[0]['post'];
				$result['flag']=Conference::SUCCESS_CODE;
				$result['catpost']=$newCategoryPost;
			} else {
				$result['done']=false;
				$result['msg']="The organizer info could not be updated";
				$result['flag']=Conference::ERROR_EDIT;
			}
		} else {
			$result['done']=false;
			$result['msg']="The organizer with the username ".$username." doesnt exist in the database";
			$result['flag']=Conference::ERROR_MISSING;
		}
		return $result;
		
	}
	/**
	 * 
	 * Deletes the organizer from the database
	 * @param Int $cid
	 * @param String $username
	 * @param String category
	 * @param String post
	 * @return $result 
	 * $result['done'] - true/false (success or failure)
	 * $result['msg'] - success or failure message 
	 * If category and post both are passed as null values, it will delete the organizer page completely
	 */
	public static function performDelete($cid,$username, $category=null, $post=null, $completeDelete=false)
	{
		$confTitle=ConferenceUtils::getTitle($cid);
		$title=$confTitle.'/organizers/'.$username;
		$titleObj=Title::newFromText($title);
		$page=WikiPage::factory($titleObj);
		
		if(!$page->exists())
		{
			$result['done']=false;
			$result['msg']="The organizer with this username ".$username." doesnt exist for the conference ".$confTitle;
			$result['flag']=Conference::ERROR_MISSING;
			return $result;
		}
		
		$id = $page->getId();
		$article = Article::newFromID($id);
		$content = $article->fetchContent();
		preg_match_all("/<organizer category=\"(.*)\" post=\"(.*)\" cvext-organizer-conf=\"(.*)\" cvext-organizer-user=\"(.*)\" \/>/",$content,$matches);
		$categoryString = $matches[1][0];
		$postString = $matches[2][0];
		$categoryArray = explode(',',$categoryString);
		$postArray = explode(',',$postString);
			
		if($completeDelete || (is_null($category) || is_null($post)) || 
				(count($categoryArray)==1 && $category==$categoryArray[0] && $post == $postArray[0]))
		{
			
			$status=$page->doDeleteArticle("admin deletes the organizer",DELETED_TEXT);
			if($status===true)
			{
				$result['done']=true;
				$result['msg']="The organizer has been successfully deleted";
				$result['flag']=Conference::SUCCESS_CODE;
			} else {
				$result['done']=false;
				$result['msg']="The organizer could not be deleted";
				$result['flag']=Conference::ERROR_EDIT;
			}
			
		} else {
			//the edit form of delete
			$available = false;
			foreach ($categoryArray as $index=>$value)
			{
				if($value == $category)
				{
					if($postArray[$index] == $post)
					{
						$available = true;
						array_splice($categoryArray, $index, 1);
						array_splice($postArray, $index, 1);
						break;
					}
				}
			}
			if(!$available)
			{
				
				//it means cat,post pair was not found
				$result['done'] = false;
				$result['msg'] = 'The category,post pair supplied was not found in the database.';
				$result['flag'] = Conference::ERROR_MISSING;
				return $result;
				
			}
			$categoryString = implode(',',$categoryArray);
			$postString = implode(',',$postArray);
			$newTag = Xml::element('organizer',array('category'=>$categoryString,'post'=>$postString,'cvext-organizer-conf'=>$matches[3][0],'cvext-organizer-user'=>$matches[4][0]));
			$content = preg_replace("/<organizer category=\".*\" post=\".*\" cvext-organizer-conf=\".*\" cvext-organizer-user=\".*\" \/>/", $newTag, $content);
			$status=$page->doEdit($content, "The (".$category.','.$post.') pair was deleted',EDIT_UPDATE);
			if($status->value['revision'])
			{
				$result['done']=true;
				$result['msg']="The organizer info has been successfully deleted";
				$result['flag']=Conference::SUCCESS_CODE;
			} else {
				$result['done']=false;
				$result['msg']="The organizer info could not be deleted. Try again .";
				$result['flag']=Conference::ERROR_EDIT;
			}
			
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
		$ids=array();
		foreach ($args as $attribute=>$value)
		{
			if($attribute=='cvext-organizer-conf')
			{
				$ids['cvext-organizer-conf']=$value;
			}
			if($attribute=='cvext-organizer-user')
			{
				$ids['cvext-organizer-user']=$value;
			}
			
		}
		$id=$parser->getTitle()->getArticleId();
		if($id!=0)
		{
			foreach ($ids as $name=>$value)
			{
				//$dbw->insert('page_props',array('pp_page'=>$id,'pp_propname'=>$name,'pp_value'=>$value));
				$parser->getOutput()->setProperty($name, $value);
			}
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
	public function getCategoryPostCombination()
	{
		return $this->mCategoryPostCombination;
	}
	/**
	 * 
	 * setter function
	 * @param Array $catpost
	 */
	public function setCategoryPostCombination($catpost)
	{
		$this->mCategoryPostCombination=$catpost;
	}
}