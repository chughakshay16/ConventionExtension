<?php
/**
 * 
 * @author chughakshay16
 * @todo - in performEdit(), one error scenario left
 * Important :
 * In case of Organizing Team page at the time of creation pull all the content for the organizers that have 
 * already been created and push it into this page with a specific format 
 */
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
	 * @var unknown_type
	 */
	public static $mPreloadedTypes = array(
			'Welcome',
			'Register',
			'Sponsor Us',
			'Schedule',
			'Submissions',
			'Scholarships',
			'Press',
			'Address Book',
			'About City',
			'Venue',
			'Accommodation',
			'Transportation',
			'Tourist Options',
			'Visas',
			'FAQ',
			'Information Desk',
			'Contact Page',
			'Attendees',
			'Vounteers',
			'Organizing Team'
	);
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
	public static function isPreloadedType($type)
	{
		return in_array($type, ConferencePage::$mPreloadedTypes);
	}
	/**
	 * @param Int $mConferenceId - page_id of the conference
	 * @param String $type type/name of the page(Main page, Registration page...)
	 * @param bool $default it tells whether to add default content or not
	 * @return ConferencePage
	 * For already defined types default content will be added to the page at the time of creation
	 * If the process fails , it just returns a ConferencePage object with id property not set
	 */
	public static function createFromScratch($mConferenceId, $type ,$default=true )
	{
		$confTitle=ConferenceUtils::getTitle($mConferenceId);
		$title=$confTitle.'/pages/'.$type;
		$titleObj=Title::newFromText($title);
		$page=WikiPage::factory($titleObj);
		$text = Xml::element('page',array('cvext-page-conf'=>$mConferenceId,'cvext-page-type'=>$type));
		$text = $text."\n".self::getDefaultContent($type, $mConferenceId); 
		$status=$page->doEdit($text,'new page added',EDIT_NEW);
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
			return new self(null, $mConferenceId, $type);
		}
	}
	/**
	 * @return - the default text for the page type
	 * find a better way of implementing this logic
	 * 
	 */
	public static function getDefaultContent($type, $conferenceId)
	{
		global $wgCategoryPostTree;
		$text = '';
		$conferenceTag = ConferenceUtils::loadFromConferenceTag($conferenceId);
		$conferenceTitle = ConferenceUtils::getTitle($conferenceId); /* we are not fetching it from the tag because we need the one with underscores */
		switch ($type){
			case 'Welcome':
				break;
			case 'Register':
				break;
			case 'Sponsor Us':
				break;
			case 'Schedule':
				$text .= "==Conference Schedule== \n";
				$days = CommonUtils::getAllConferenceDays($conferenceTag['startDate'], $conferenceTag['endDate']);
				foreach ($days as $day)
				{
					$title = Title::makeTitle(NS_TEMPLATE, $conferenceTitle.'/'.$day);
					$text .= "===".$day."=== \n";
					$text .= "{{".$title->getPrefixedDBkey()."}} \n";
				}
				break;
			case 'Submissions':
				$text .= "==Overview== \n";
				$text .= "==Presentation Length== \n";
				$text .= "==Tracks== \n";
				/*
				 * put all the tracks as sub headings
				 */
				$text .= "==How to Submit a Proposal ?== \n";
				break;
			case 'Scholarships':
				$text .= "==Scholarship Program== \n";
				$text .= "==Goals of the Program== \n";
				$text .= "==About Application for Scholarship== \n";
				$text .= "'''Eligibility''' : "."\n";
				$text .= "'''Selection''' : "."\n";
				$text .= "'''Deadline for Submission''' : "."\n";
				$text .= "===Process=== \n";
				$text .= "===Types of Scholarships=== \n";
				$text .= "===Selection Criteria=== \n";
				$text .= "==Questions== \n";
				$text .= "==Apply== \n";
				break;
			case 'Press':
				break;
			case 'Address Book':
				break;
			case 'About City':
				break;
			case 'Venue':
				$text .= "==Locations== \n";
				$locations = Conference::getLocations($conferenceId);
				foreach ($locations as $location)
				{
					$locationLink = Title::newFromText($conferenceTitle.'/locations/'.$location->getRoomNo())->getDBKey();
					$text .= "*[[".$locationLink."|".$location->getRoomNo()."]] \n";
				}
				break;
			case 'Accommodation':
				break;
			case 'Transportation':
				break;
			case 'Tourist Options':
				break;
			case 'Visas':
				break;
			case 'FAQ':
				break;
			case 'Information Desk':
				break;
			case 'Contact Page':
				break;
			case 'Attendees':
				/* attendees template */
				break;
			case 'Volunteers':
				/* put all the categories for volunteers */
				break;
			case 'Organizing Team':
				$conference = Conference::loadFromId($conferenceId);
				$organizers = $conference->getOrganizers();
				$categories = array();
				foreach ($wgCategoryPostTree as $index => $value)
				{
					$categories[] = $value['category'];
				}
				$indexedArray = array();
				$nonIndexedArray = array();
				$indexedPostArray = array();
				$nonIndexedPostArray = array();
				if($organizers)
				{
					foreach ($organizers as $organizer)
					{
						$catpost = $organizer->getCategoryPostCombination();
						foreach ($catpost as $combination)
						{
							$category = $combination['category'];
							$post = $combination['post'];
							$key = array_search($category, $categories);
							$user = User::newFromId($organizer->getUserId());
							if($key !== false)
							{
								$indexedArray[$key]['category'] = $category;
								$postKey =  array_search($post, isset($wgCategoryPostTree[$key]['posts']) ? $wgCategoryPostTree[$key]['posts'] : array());
								if($postKey !== false)
								{
									/* indexed posts */
									$indexedPostArray[$category]['posts'][$postKey][] = array('post' => $post, 'user' => $user);
								} else {
									/* non-indexed posts */
									$nonIndexedPostArray[$category]['posts'][] = array('post' => $post, 'user' => $user);
								}
								/* dont use this logic, this doesnt work (in case you need it do some modifications first)
								 if(isset($wgCategoryPostTree[$key]['posts']))
								{
									/* mixed block */
									/*$posts = isset($indexedArray[$key]['posts']) ? $indexedArray[$key]['posts'] : array();
									$postKey = array_search($post, $wgCategoryPostTree[$key]['posts']);
									if($postKey === false)
									{
										$indexedArray[$key]['posts'][] = array('post' => $post , 'user' => $user);
									} else {
										if(isset($posts[$postKey]))
										{
											/*$absentKeyPost = $posts[$postKey];
											 unset($posts[$postKey]);
											$posts[$postKey] = array('post' => $post, 'user' => $user);
											$posts[] = $absentKeyPost;*/
											/*$newPost = array(array('post' => $post, 'user' => $user));
											array_splice($posts, $postKey, 0, $newPost);
										} else {
											$posts[$postKey] = array('post' => $post, 'user' => $user);
										}
										$indexedArray[$key]['posts'] = $posts;
									}
					
								} else {
									$indexedArray[$key]['posts'][] = array('post' => $post, 'user' => $user);
								}*/
							} else {
								/* non-indexed category */
								if($count = count($nonIndexedArray))
								{
									foreach ($nonIndexedArray as $index => $value)
									{
										if($value['category'] == $category)
										{
											$nonIndexedArray[$index]['posts'][] = array('post' => $post, 'user' => $user);
											break;
										} else {
											$nonIndexedArray[$count]['category'] = $category;
											$nonIndexedArray[$count]['posts'][] = array('post' => $post, 'user' => $user);
										}
									}
								} else {
									$nonIndexedArray[0]['category'] = $category;
									$nonIndexedArray[0]['posts'][] = array('post' => $post, 'user' => $user);
								}
							}
						}
					
					}
					/* horizontally to vertically */
					foreach ($indexedPostArray as $cat => $val)
					{
						$count = count($val['posts']);
						$verPosts = array();
						for($i =0 ; $i < $count ; $i++)
						{
							$fetchedPosts = $val['posts'][$i];
							$verPosts = array_merge($verPosts, $fetchedPosts);
						}
						unset($indexedPostArray[$cat]);
						$indexedPostArray[$cat]['posts'] = $verPosts;
					}
					/* add both the indexed and non-indexed post arrays */
					foreach ($categories as $index => $cat)
					{
						$indexedPosts = $indexedPostArray[$cat]['posts'];
						$nonIndexedPosts = isset($nonIndexedPostArray[$cat]['posts']) ? $nonIndexedPostArray[$cat]['posts'] : array();
						$indexedPosts = array_merge($indexedPosts, $nonIndexedPosts);
						$indexedArray[$index]['posts'] = $indexedPosts;
					}
					/* now add both the indexed and non-indexed category arrays */
					$indexedArray = array_merge($indexedArray, $nonIndexedArray);
					/* generate the content for the page (dont use the foreach loop here)*/
					for ($i = 0; $i < count($indexedArray); $i++)
					{
						$section = $indexedArray[$i];
						$category = $section['category'];
						$posts = $section['posts'];
						$text .= '=='.$category."== \n";
						for ($j = 0; $j < count($posts); $j++)
						{
							$user = $posts[$j]['user'];
							$userLink = $user->getUserPage()->getPrefixedDBKey();
							$text .= '==='.$posts[$j]['post']."=== \n";
							$realName = $user->getRealName();
							$text .= "*'''[[".$userLink.'|'.($realName ? $realName : $user->getName())."]]''' \n";
							$text .= "** Email : \n";
							$text .= "** Phone : \n";
							$text .= "** Cellphone : \n";
							$text .= "** Skype : \n";
							$text .= "** Other Contacts : \n";
							$text .= "** City/Timezone : \n";
							$text .= "** Accessibility : \n";
							$text .= "** Languages : \n";
						}
					}
				}
				break;													
			default :
				break;	
											
		}
		return $text;
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
		$conferenceId = $matches[1][0];
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
			$status=$page->doDeleteArticle("admin deletes the page of type ".$type,Revision::DELETED_TEXT);
			if($status===true)
			{
				$result['done']=true;
				$result['msg']="Page has been successfully deleted";
				$result['preloaded']=self::isPreloadedType($type);
				$result['pagetype'] = $type;
				$result['flag']=Conference::SUCCESS_CODE;
			} else {
				$result['done']=false;
				$result['msg']="The page couldnt be deleted";
				$result['flag']=Conference::ERROR_DELETE;
			}
		} else {
			$result['done']=false;
			$result['msg']="The page with this title doesnt exist in the database";
			$result['flag']=Conference::ERROR_MISSING;
		}
		
		return $result;
	}
	public static function performEdit($conferenceId, $type , $oldType)
	{
		
		$confTitle = ConferenceUtils::getTitle($conferenceId);
		$titleText = $confTitle.'/pages/'.$type;
		$title = Title::newFromText($titleText);
		if(!$title)
		{
			
			$result['done']=false;
			$result['msg']='Invalid value of type';
			$result['flag']=Conference::ERROR_EDIT;
			return $result;
			
		} 
		
		
		$page =WikiPage::factory($title);
		$result = array();
		if($title->exists())
		{
			
			$id = $page->getId();
			$article = Article::newFromID($id);
			$content = $article->fetchContent();
			preg_match_all("/<page cvext-page-conf=\"(.*)\" cvext-page-type=\"(.*)\" \/>/",$content,$matches);
			if(!$type)
			{
				
				$result['done']=false;
				$result['msg']='The type value passed as null';
				$result['flag']=Conference::INVALID_CONTENT;
				return $result;
				
			} elseif ($type==$matches[2][0]){
				
				$result['done']=true;
				$result['msg']='Passed content is same as previous content';
				$result['flag']=Conference::NO_EDIT_NEEDED;
				$result['urlto'] = $page->getTitle()->getFullURL();
				return $result;
				
			} else {
				
				$oldType = $matches[2][0];
				
			}
			
			
			$newTag = Xml::element('page',array('cvext-page-conf'=>$matches[1][0],'cvext-page-type'=>$type));
			$content = preg_replace("/<page cvext-page-conf=\".*\" cvext-page-type=\".*\" \/>/", $newTag, $content);
			$status = $page->doEdit($content, 'modifying page type',EDIT_UPDATE);
			if($status->value['revision'])
			{
				
				$result['done']=true;
				$result['msg']="The page details were successfully updated";
				$result['pagetypeto'] = $type;
				$result['urlto'] = $page->getTitle()->getFullURL();
				/*$dbw = wfGetDB(DB_MASTER);
				/* deleting properties from the old page, thereby removing all the connections between the old page and the conference 
				$oldTitleText = $confTitle.'/pages/'.$oldType;
				$oldtitle = Title::newFromText($oldTitleText);
				/* for some reason it returns 0 
				//$oldid = $oldtitle->getArticleID();//
				//so we will have to fetch the id from the database
				
				$row = $dbw->selectRow('page',
						'page_id',
						array('page_title'=>$oldtitle->getDBkey()),
						__METHOD__);
				if($row)
				{
					$dbw->delete('page_props',
							array('pp_page'=>$row->page_id,'page_propname IN (cvext-page-conf,cvext-page-type)'),
							__METHOD__);
				}
				//do check to see if row was updated properly
				if ($dbw->affectedRows()!=0)
				{
					
				} else {
					//what would be the perfect solution in this case ? 
				}*/

				//$result['flag']=Conference::SUCCESS_CODE;
				
				
			} else {
				
				$result['done']=false;
				$result['msg']="Page details could not be updated";
				$result['flag']=Conference::ERROR_EDIT;
				
			}
		} else {
			
			$result['done']=false;
			$result['msg']='The page with these details was not found in the database';
			$result['flag']=Conference::ERROR_MISSING;
			
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
			//$dbw=wfGetDB(DB_MASTER);
			foreach ($args as $name=>$value)
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
	public function getType()
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