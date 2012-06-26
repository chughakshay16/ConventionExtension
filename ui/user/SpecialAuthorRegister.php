<?php
class SpecialAuthorRegister extends SpecialPage
{
	const INVALID_CONF_TITLE=0;
	const CONF_NOT_EXISTS = 1;
	const VALID_CONF_TITLE =2;
	const LOAD_FROMID_FAIL=3;
	const LOAD_TEMPLATE_SUCCESS = 4;
	const LOAD_OBJECT_ABSENT =5;
	const MISSING_PARAM_VALUE =6;
	const NO_SUB_AUTHOR =7;
	const INVALID_SUB_TITLE=8;
	const INVALID_PARAM_VALUE=9;
	const MISSING_HIDDEN_PARAM_VALUE =10;
	private $conferenceId;
	private $conferenceTitle;
	public function __construct($name = 'AuthorRegister')
	{
		parent::__construct($name);
	}
	public function execute($par)
	{
		//note :  1. here par is the title of the conference
		//we wont be storing any conference details in session object for the author
		//atleast not for now, if in future we see a need for it we will
		// 2. user should just be logged in to access this page
		$this->setHeaders();
		$user = $this->getUser();
		$request = $this->getRequest();
		$out = $this->getOutput();
		$out->setPageTitle('Submission Form');
		if(true)
		{
			$template = new AuthorRegisterTemplate();
			$template->set('showAuthor',true);
			$template->set('showSubmission',true);
			$template->set('showAuthorSubmit',false);
			$template->set('action','');
			$template->set('heading','Author Registration Setup');
			$template->set('authorLegend','Edit Author Details');
			$template->set('submissionLegend','Edit Submission Details');
			$template->set('countries',$this->getCountries());
			$template->set('country','Country');
			$template->set('affiliation','Affiliation');
			$template->set('url','Blog Url');
			$template->set('title','Title');
			$template->set('type','Type');
			$template->set('track','Track');
			$template->set('abstract','Abstract');
			$template->set('length','Length');
			$template->set('slidesinfo','Slides Info');
			$template->set('slotreq','Slot Request');
			$template->set('submit','Save');
			$template->set('create','bothauthorsub');
			$template->set('minsmessage','in mins');
			$out->addModules('ext.conventionExtension.authorregister');
			$out->addTemplate($template);
		} else {
			
		
		
		//now we will check for the validity of $par
		/*$title = Title::newFromText($par);
		if(!$title)
		{
			//error page
		} elseif (!$title->exists()) {
			//error page
		}*/
		if(!$user->isLoggedIn())
		{
			//error page
		} else {	
			$check = $this->performTitleCheck($par);
			if($check['flag']==VALID_CONF_TITLE)
			{
				//here we could have used ConferenceUtils::getConferenceId($title->getDBKey()); instead to get the conference id
				$this->conferenceId = $title->getArticleID();
				$this->conferenceTitle = $title->getDBKey();
				//this whole function will deal with three different actions create | edit | delete
				// 1. create 
				$action = $request->getVal('action',null);
				$template =null;
				if($action == 'createview') 
				{
					
					$template = $this->prepareCreateViewTemplate();
						
				} elseif ($action == 'editauthorview') {
					
					$output = $this->prepareAuthorEditViewTemplate();
					if($output['flag']==LOAD_TEMPLATE_SUCCESS)
					{
						$template = $output['template'];
					} elseif ($output['flag']==LOAD_OBJECT_ABSENT) {
						//error
						//add an error template
					} elseif ($output['flag']==LOAD_FROMID_FAIL) {
						//re-load the form to the user as internal error caused the process to finish
					}
					
				} elseif ($action == 'editsubview') {
					$output = $this->prepareSubEditViewTemplate();
					if($output['flag']==LOAD_TEMPLATE_SUCCESS)
					{
						$template = $output['template'];
					} elseif ($output['flag']==NO_SUB_AUTHOR) {
						//error
					} elseif ($output['flag']==LOAD_OBJECT_ABSENT) {
						//error
					} elseif ($output['flag']==MISSING_PARAM_VALUE) {
						//error
					} elseif ($output['flag']==LOAD_FROMID_FAIL) {
						//redirect to this page again
					} else {
						
					}
						
				} elseif ($action == 'processcreate') {
					$output = $this->processCreate();
					if($output['flag']==CREATE_SUCCESS)
					{
						$redirectUrl = $output['redirect'];
						$out->redirect($redirectUrl);
					} elseif ($output['flag']==)
				} elseif ($action == 'processauthoredit') {
					
				} elseif ($action == 'processsubedit') {
					
				} elseif ($action == 'processauthordelete') {
					
				} elseif ($action == 'processsubdelete') {
					
				} else {
					//error page
				}
				$out->addTemplate($template);
			} elseif ($check['flag']==INVALID_CONF_TITLE) {
			
			} elseif ($check['flag']==TITLE_NOT_EXISTS) {
			
			}
		}
		
		
		if( $action == 'createview')
		{
			/*$showAuthor = !UserUtils::isSpeaker($user->getId());
			$template = new AuthorRegisterTemplate();
			/* isAuthorPresent will decide if author fields are to be put in the form or not*/
			/*$template->set('showAuthor', $showAuthor);
			$titleObj = $this->getTitle();
			$queryUrl = 'action=processcreate';
			$actionUrl = $titleObj->getLocalURL($queryUrl);
			$template->set('action', $actionUrl);
			if(!$showAuthor)
			{
				
				$template->set('create','onlysub');
				
			} else {
				
				$template->set('country',wfMsg('author-sub-reg-country'));
				$template->set('affiliation',wfMsg('author-sub-reg-affiliation'));
				$template->set('url',wfMsg('author-sub-reg-blogurl'));
				$template->set('countries',$this->getCountries());
				$template->set('showAuthorSubmit',false);
				$template->set('create','bothauthorsub');
				
			}
			$template->set('showSubmission',true);
			$template->set('authorLegend', wfMsg('author-create-legend'));
			$template->set('submissionLegend',wfMsg('sub-create-legend'));
			$template->set('title',wfMsg('author-sub-reg-title'));
			$template->set('type',wfMsg('author-sub-reg-type'));
			$template->set('track',wfMsg('author-sub-reg-track'));
			$template->set('minsmessage',wfMsg('author-sub-reg-minutes-msg'));
			$template->set('abstract',wfMsg('author-sub-reg-abstract'));
			$template->set('slotreq',wfMsg('author-sub-reg-slotreq'));
			$template->set('slidesinfo',wfMsg('author-sub-reg-slidesinfo'));
			$template->set('length',wfMsg('author-sub-reg-length'));
			$template->set('submit',wfMsg('author-sub-reg-submit'));
			$out->addTemplate($template);*/
			
			
		} elseif ($action == 'editauthorview') {
					
			//do some checks
			// 1. parent author exists or not
			// 2. child author exists or not
			/*$isParentPresent = UserUtils::isSpeaker($user->getId());
			if($isParentPresent)
			{
			
				$authorId = ConferenceAuthorUtils::getAuthorId($user->getId());
				$author = ConferenceAuthor::loadFromId($authorId);	
				if($author && $author->getAuthorId())
				{
					$country = $author->getCountry();
					$affiliation = $author->getAffiliation();
					$blogUrl = $author->getBlogUrl();
					$template= new AuthorRegisterTemplate();
					$template->set('showAuthor',true);
					$template->set('showSubmission',false);
					$template->set('showAuthorSubmit',true);
					$template->set('submit',wfMsg('author-edit-submit'));
					$titleObj = $this->getTitle();
					$queryUrl = 'action=processauthoredit';
					$actionUrl = $titleObj->getLocalURL($queryUrl);
					$template->set('action',$actionUrl);
					$template->set('authorLegend',wfMsg('author-edit-legend'));
					$template->set('country',wfMsg('author-edit-country'));
					$template->set('affiliation',wfMsg('author-edit-affiliation'));
					$template->set('url',wfMsg('author-edit-blogurl'));
					$template->set('countries',$this->getCountries($country));
					$template->set('affiliationVal',$affiliation);
					$template->set('urlVal',$blogUrl);
					$out->addTemplate($template);
				} else {
					//error page
				}
			} else {
				//error page
			}*/
		} elseif ($action == 'editsubview') {
			/*if(UserUtils::isSpeaker($user->getId()))
			{
				//now check if the user has a sub-author account
				$accountId = ConferenceAuthorUtils::getAuthorId($user->getId());
				if(ConferenceAuthorUtils::hasChildAuthor($accountId,$conferenceId))
				{
					$submissionTitle = $request->getVal('submission',null);
					if($submissionTitle)
					{
						$author = ConferenceAuthor::loadFromId($authorId);
						$confKey = 'conf-'.$conferenceId;
						$allSubmissions = $author->getSubmissions();
						$confSubmissions = $submissions[$confKey]['submissions'];
						foreach ($confSubmissions as $confSubmission)
						{
							if($confSubmission->getTitle()==$submissionTitle)
							{
								$thisSubmission = $confSubmission;
								break;
							}
						}
						//now set up the template
						$template = new AuthorRegisterTemplate();
						$template->set('showAuthor',false);
						$template->showSubmission('showSubmission',true);
						$titleObj = $this->getTitle();
						$queryUrl = 'action=processsubedit';
						$actionUrl = $titleObj->getLocalURL($queryUrl);
						$template->set('action',$actionUrl);
						$template->set('title',wfMsg('sub-edit-title'));
						$template->set('titleVal',$thisSubmission->getTitle());
						$template->set('type',wfMsg('sub-edit-type'));
						$template->set('typeVal',$thisSubmission->getType());
						$template->set('track',wfMsg('sub-edit-track'));
						$template->set('trackVal',$thisSubmission->getTrack());
						$template->set('abstract',wfMsg('sub-edit-abstract'));
						$template->set('abstractVal',$thisSubmission->getAbstract());
						$template->set('length',wfMsg('sub-edit-length'));
						$template->set('lengthVal',$thiSubmission->getLength());
						$template->set('slotreq',wfMsg('sub-edit-slotreq'));
						$template->set('slotreqVal',$thisSubmission->getSlotReq());
						$template->set('slidesinfo',wfMsg('sub-edit-slidesinfo'));
						$template->set('slidesinfoVal',$thisSubmission->getSlidesInfo());
						$template->set('submissionLegend',wfMsg('sub-edit-legend'));
						$template->set('minsmessage',wfMsg('sub-edit-minutes-msg'));
						$template->set('submit',wfMsg('sub-edit-submit'));
						$out->addTemplate($template);
					} else {
						//error page
					}
				} else {
					//error page
				}
			} else {
				//error page
			}*/
		
		} elseif ($action == 'processcreate') {
			//no need for any checks , only criteria for anyone to create a new author is that the user must be logged in, which we have already tested before
			$actionType = $request->getVal('create',null);
			$title = $request->getVal('title',null);
			if($title)
			{
				$titleObj = Title::newFromText($title);
				if(!$title)
				{
					//error page
				} else {
					$type = $request->getVal('type','');
					//we still have to figure out how we are storing the track values
					$track = $request->getVal('track','');
					$abstract = $request->getVal('abstract','');
					$length = $request->getVal('length','');
					$slotreq = $request->getVal('slotreq','');
					$slidesinfo = $request->getVal('slidesinfo','');
					$errors = $this->mustValidateInputs($type,$track,$abstract,$length,$slotreq,$slidesinfo);
					if(count($errors))
					{
						//error page 
					} else {
						$submision = new AuthorSubmission(null,null,$title, $type, $abstract, $track, $length, $slidesinfo, $slotreq);
						if($actionType==='onlysub')
						{
							//title is the only value that must be passed , rest all of the values are optional
							$author = ConferenceAuthor::createFromScratch($conferenceId,$user->getId(),'','','',$submission);
							$submissions = $author->getSubmissions();
							$key = 'conf-'.$conferenceId;
							$thisSubmission = $submissions[$key]['submissions'][0];
							if($thisSubmission && $thisSubmission->getId())
							{
								$submissionTitle = $conferenceTitle.'/authors/'.$user->getName().'/submissions/'.$title;
								$redirectTitle = Title::newFromText($submissionTitle);
								$redirectUrl = $redirectTitle->getLocalURL();
								$out->redirect($redirectUrl);
							} else {
								//error page
							}
				
						} elseif ($actionType==='bothauthorsub') {
						
							//the author specific values are not necessary
							$country = $request->getVal('country','');
							$affiliation = $request->getVal('affiliation','');
							$url = $request->getVal('url','');
							$errors = $this->mustValidateInputs($country, $affiliation, $url);
							if(count($errors))
							{
								//error page
							} else {
								$author = ConferenceAuthor::createFromScratch(null, null, $country, $affiliation, $url, $submission);
								if($author && $author->getAuthorId())
								{
									//now lets check if the submission was saved successfully or not
									$submissions = $author->getSubmissions();
									$key = 'conf-'.$conferenceId;
									$thisSubmission = $submissions[$key]['submissions'][0];
									if($thisSubmission && $thisSubmission->getId())
									{
										$submissionTitle = $conferenceTitle.'/authors/'.$user->getName().'/submissions/'.$title;
										$redirectTitle = Title::newFromText($submissionTitle);
										$redirectUrl = $redirectTitle->getLocalURL();
										$out->redirect($redirectUrl);	
									} else {
										//error page
									}
								} else {
									//error page
								}
							}
						
						} else {
							//error page
						}
					}
					
				}
					
			} else {
				//error page
			}
			
		} elseif ($action == 'processauthoredit') {
			
			$country = $request->getVal('country','');
			$affiliation = $request->getVal('affiliation','');
			$url = $request->getVal('url','');
			//we wont have to perform many of the checks as they will eventually happen within the API module for authoredit
			$params = new DerivativeRequest(
			$request, 
			array(
			'action'=>'authoredit',
			'country'=>$country,
			'affiliation'=>$affiliation,
			'url'=>$url)
			);
			//use try and catch block 
			$api = new ApiMain($params, true);
			$api->execute();
			$data = & $api->getResultData();
			if($data['done'])
			{
				//everything went fine 
				//now redirect user to the author page
				$authorTitle = $conferenceTitle.'/auhtors/'.$user->getName();
				$titleObj = Title::newFromText($authorTitle);
				$redirectUrl = $titleObj->getLocalURL();
				$out->redirect($redirectUrl);
			} elseif ($data['flag']==Conference::ERROR_MISSING) {
				//error page stating that the author with this username doesnt exist
			} elseif ($data['flag']==Conference::ERROR_EDIT) {
				//error page stating that some internal error occurred and ask user to re-do the edit process
				//error page should also contain a link to the author page
			}
			
		} elseif ($action == 'processsubedit') {
			
			$title = $request->getVal('title','');
			$track = $request->getVal('track','');
			$type = $request->getVal('type','');
			$length = $request->getVal('length','');
			$abstract = $request->getVal('abstract','');
			$slidesinfo = $request->getVal('slidesinfo','');
			$slotreq = $request->getVal('slotreq','');
			$params = new DerivativeRequest(
			$request,
			array('action'=>'subedit',
			'title'=>$title,
			'track'=>$track,
			'type'=>$type,
			'length'=>$length,
			'abstract'=>$abstract,
			'slidesinfo'=>$slidesinfo,
			'slotreq'=>$slotreq));
			//use try and catch block
			$api = new ApiMain($params, true);
			$api->execute();
			$data = & $api->getResultData();
			if($data['done'])
			{
				//everything went fine , redirect user to the updated submission page
				$submissionTitle = $conferenceTitle.'/authors/'.$user->getName().'/submissions/'.$title;
				$title = Title::newFromText($submissionTitle);
				$redirectUrl = $title->getFullURL();
				$out->redirect($redirectUrl);
				
			} elseif ($data['flag']==Conference::ERROR_EDIT) {
				// error page
			} elseif ($data['flag']==Conference::ERROR_MISSING) {
				//error page	
			}
		} elseif ($action == 'processauthordelete') {
			$params = new DerivativeRequest(
			$request,
			array(
			'action'=>'authordelete'));
			$api = new ApiMain($params, true);
			$api->execute();
			$data = & $api->getResultData();
			if($data['done'])
			{
				//redirect user to the user page
				$userPage = $user->getUserPage();	
				$userpageUrl = $userPage->getLocalURL();
				$out->redirect($userpageUrl);
			} elseif ($data['flag']==Conference::ERROR_DELETE) {
				//error page , ask user to delete again(there wont be any undelete in this case, and if in future 
				//u decide to add this as a functionality you would have to come up with a 
				//complex function of undeleting all the sub-author and submission wiki pages)
				
			} elseif ($data['flag']==Conference::ERROR_MISSING) {
				//error page stating that no author was found with this user account
			}
		} elseif ($action == 'processsubdelete') {
			$title = $request->getVal('title','');
			//we have already checked the validity of conference in $par
			$conference = $conferenceTitle;
			$params = new DerivativeRequest(
			$request,
			array(
			'title'=>$title,
			'conference'=>$conference));
			$api = new ApiMain($params);
			$api->execute();
			$data = & $api->getResultData();
			if($data['done'])
			{
				//everything went fine , refirect user to the author page and state that the process went okay
				$authorTitle = $conferenceTitle.'/authors/'.$user->getName();
				$title = Title::newFromText($authorTitle);
				$redirectUrl = $title->getLocalURL();
				$out->redirect($redirectUrl);
			} elseif ($data['flag']==Conference::ERROR_DELETE) {
				//error page
			} elseif ($data['flag']==Conference::ERROR_MISSING) {
				//error page
			}
			
			}else {
			//error page
		}
		//now we will fetch all the author details from the request
		//we wont have to do any more checks as createFromScratch() internally takes care of all the scenarios
		// we just need to validate the inputs
		}
	}
	private function processCreate()
	{
		//no need for any checks , only criteria for anyone to create a new author is that the user must be logged in, which we have already tested before
		$conferenceId = $this->conferenceId;
		$request = $this->getRequest();
		$user = $this->getUser();
		$conferenceTitle = $this->conferenceTitle;
		$actionType = $request->getVal('create',null);
		$title = $request->getVal('title',null);
		$output = array();
		if($title)
		{
			$titleObj = Title::newFromText($title);
			if(!$titleObj)
			{
				$output['flag']=INVALID_SUB_TITLE;
				return $output;
			}
			$type = $request->getVal('type','');
			//we still have to figure out how we are storing the track values
			$track = $request->getVal('track','');
			$abstract = $request->getVal('abstract','');
			$length = $request->getVal('length','');
			$slotreq = $request->getVal('slotreq','');
			$slidesinfo = $request->getVal('slidesinfo','');
			$errors = $this->mustValidateInputs($type,$track,$abstract,$length,$slotreq,$slidesinfo);
			if(count($errors))
			{
				$output['flag']=INVALID_PARAM_VALUE;
				$output['param']=$errors['param'];
				return $output;
			} 
			$submision = new AuthorSubmission(null,null,$title, $type, $abstract, $track, $length, $slidesinfo, $slotreq);
			if($actionType==='onlysub')
			{
				//title is the only value that must be passed , rest all of the values are optional
				$author = ConferenceAuthor::createFromScratch($conferenceId,$user->getId(),'','','',$submission);
				/*$submissions = $author->getSubmissions();
						$key = 'conf-'.$conferenceId;
						$thisSubmission = $submissions[$key]['submissions'][0];
						if($thisSubmission && $thisSubmission->getId())
						{
							$submissionTitle = $conferenceTitle.'/authors/'.$user->getName().'/submissions/'.$title;
							$redirectTitle = Title::newFromText($submissionTitle);
							$redirectUrl = $redirectTitle->getLocalURL();
							$out->redirect($redirectUrl);
						} else {
							//error page
						}*/
				
			} elseif ($actionType==='bothauthorsub') {
						
				//the author specific values are not necessary
				$country = $request->getVal('country','');
				$affiliation = $request->getVal('affiliation','');
				$url = $request->getVal('url','');
				$errors = $this->mustValidateInputs($country, $affiliation, $url);
				if(count($errors))
				{
					$output['flag']=INVALID_PARAM_VALUE;
					$output['param']=$errors['param'];
					return $output;
				}	
				$author = ConferenceAuthor::createFromScratch(null, null, $country, $affiliation, $url, $submission);
						
			} else {
				$output['flag']=MISSING_HIDDEN_PARAM_VALUE;
				$output['param']='create';
				return $output;		
			}
			
			
			if($author && $author->getAuthorId())
			{
				//now lets check if the submission was saved successfully or not
				$submissions = $author->getSubmissions();
				$key = 'conf-'.$conferenceId;
				$thisSubmission = $submissions[$key]['submissions'][0];
				if($thisSubmission && $thisSubmission->getId())
				{
					$submissionTitle = $conferenceTitle.'/authors/'.$user->getName().'/submissions/'.$title;
					$redirectTitle = Title::newFromText($submissionTitle);
					$redirectUrl = $redirectTitle->getLocalURL();
					//$out->redirect($redirectUrl);	
					$output['flag']=CREATE_SUCCESS;
					$output['redirect']=$redirectUrl;
					return $output;
				} else {
						$output['flag']=CREATE_OBJECT_FAIL;
						$output['object']='submission';
						return $output;
				}
			} else {
				$output['flag']=CREATE_OBJECT_FAIL;
				$ouput['object']='author';
				return $output;
			}
		} else {
			$output['flag']=MISSING_PARAM_VALUE;
			$output['param']='title';
			return $output;
		}
			
	}
	private function prepareSubEditViewTemplate()
	{
		$conferenceId = $this->conferenceId;		
		$user = $this->getUser();
		$request = $this->getRequest();
		$output = array();
		if(UserUtils::isSpeaker($user->getId()))
			{
				//now check if the user has a sub-author account
				$accountId = ConferenceAuthorUtils::getAuthorId($user->getId());
				if(ConferenceAuthorUtils::hasChildAuthor($accountId,$conferenceId))
				{
					$submissionTitle = $request->getVal('submission',null);
					if($submissionTitle)
					{
						$author = ConferenceAuthor::loadFromId($authorId);
						if( $author && $author->getAuthorId())
						{
							$confKey = 'conf-'.$conferenceId;
							$allSubmissions = $author->getSubmissions();
							$confSubmissions = $submissions[$confKey]['submissions'];
							foreach ($confSubmissions as $confSubmission)
							{
								if($confSubmission->getTitle()==$submissionTitle)
								{
									$thisSubmission = $confSubmission;
									break;
								}
							}
							//now set up the template
							$template = new AuthorRegisterTemplate();
							$template->set('showAuthor',false);
							$template->showSubmission('showSubmission',true);
							$titleObj = $this->getTitle();
							$queryUrl = 'action=processsubedit';
							$actionUrl = $titleObj->getLocalURL($queryUrl);
							$template->set('action',$actionUrl);
							$template->set('title',wfMsg('sub-edit-title'));
							$template->set('titleVal',$thisSubmission->getTitle());
							$template->set('type',wfMsg('sub-edit-type'));
							$template->set('typeVal',$thisSubmission->getType());
							$template->set('track',wfMsg('sub-edit-track'));
							$template->set('trackVal',$thisSubmission->getTrack());
							$template->set('abstract',wfMsg('sub-edit-abstract'));
							$template->set('abstractVal',$thisSubmission->getAbstract());
							$template->set('length',wfMsg('sub-edit-length'));
							$template->set('lengthVal',$thiSubmission->getLength());
							$template->set('slotreq',wfMsg('sub-edit-slotreq'));
							$template->set('slotreqVal',$thisSubmission->getSlotReq());
							$template->set('slidesinfo',wfMsg('sub-edit-slidesinfo'));
							$template->set('slidesinfoVal',$thisSubmission->getSlidesInfo());
							$template->set('submissionLegend',wfMsg('sub-edit-legend'));
							$template->set('minsmessage',wfMsg('sub-edit-minutes-msg'));
							$template->set('submit',wfMsg('sub-edit-submit'));
							$output['template']=$template;
							$output['flag']=LOAD_TEMPLATE_SUCCESS;
						} else {
							$output['flag']=LOAD_FROMID_FAIL;
						}
						
					} else {
						$output['flag']=MISSING_PARAM_VALUE;
					}
				} else {
					$output['flag']=NO_SUB_AUTHOR;
				}
			} else {
				$output['flag']=LOAD_OBJECT_ABSENT;
			}
			return $output;
	}
	private function prepareAuthorEditViewTemplate()
	{
		//do some checks
		// 1. parent author exists or not
		// 2. child author exists or not
		$output = array();
		$user = $this->getUser();
		$isParentPresent = UserUtils::isSpeaker($user->getId());
		if($isParentPresent)
		{
			
			$authorId = ConferenceAuthorUtils::getAuthorId($user->getId());
			$author = ConferenceAuthor::loadFromId($authorId);	
			if($author && $author->getAuthorId())
			{
				$country = $author->getCountry();
				$affiliation = $author->getAffiliation();
				$blogUrl = $author->getBlogUrl();
				$template= new AuthorRegisterTemplate();
				$template->set('showAuthor',true);
				$template->set('showSubmission',false);
				$template->set('showAuthorSubmit',true);
				$template->set('submit',wfMsg('author-edit-submit'));
				$titleObj = $this->getTitle();
				$queryUrl = 'action=processauthoredit';
				$actionUrl = $titleObj->getLocalURL($queryUrl);
				$template->set('action',$actionUrl);
				$template->set('authorLegend',wfMsg('author-edit-legend'));
				$template->set('country',wfMsg('author-edit-country'));
				$template->set('affiliation',wfMsg('author-edit-affiliation'));
				$template->set('url',wfMsg('author-edit-blogurl'));
				$template->set('countries',$this->getCountries($country));
				$template->set('affiliationVal',$affiliation);
				$template->set('urlVal',$blogUrl);
				$output['template'] = $template;
				$output['flag']=LOAD_TEMPLATE_SUCCESS;
			} else {
					$output['flag']=LOAD_FROMID_FAIL;
			}
		} else {
			$output['flag']=LOAD_OBJECT_ABSENT;
		}
		return $output;
	}
	private function prepareCreateViewTemplate()
	{
		$user = $this->getUser();
		$showAuthor = !UserUtils::isSpeaker($user->getId());
		$template = new AuthorRegisterTemplate();
		/* showAuthor will decide if author fields are to be put in the form or not*/
		$template->set('showAuthor', $showAuthor);
		$titleObj = $this->getTitle();
		$queryUrl = 'action=processcreate';
		$actionUrl = $titleObj->getLocalURL($queryUrl);
		$template->set('action', $actionUrl);
		if(!$showAuthor)
		{
				
			$template->set('create','onlysub');
				
		} else {
				
			$template->set('country',wfMsg('author-sub-reg-country'));
			$template->set('affiliation',wfMsg('author-sub-reg-affiliation'));
			$template->set('url',wfMsg('author-sub-reg-blogurl'));
			$template->set('countries',$this->getCountries());
			$template->set('showAuthorSubmit',false);
			$template->set('create','bothauthorsub');
				
			}
		$template->set('showSubmission',true);
		$template->set('authorLegend', wfMsg('author-create-legend'));
		$template->set('submissionLegend',wfMsg('sub-create-legend'));
		$template->set('title',wfMsg('author-sub-reg-title'));
		$template->set('type',wfMsg('author-sub-reg-type'));
		$template->set('track',wfMsg('author-sub-reg-track'));
		$template->set('minsmessage',wfMsg('author-sub-reg-minutes-msg'));
		$template->set('abstract',wfMsg('author-sub-reg-abstract'));
		$template->set('slotreq',wfMsg('author-sub-reg-slotreq'));
		$template->set('slidesinfo',wfMsg('author-sub-reg-slidesinfo'));
		$template->set('length',wfMsg('author-sub-reg-length'));
		$template->set('submit',wfMsg('author-sub-reg-submit'));
		return $template;
	}
	private function performTitleCheck($par)
	{
		$check = array();
		$check['flag']=VALID_CONF_TITLE;
		$title = Title::newFromText($par);
		if(!$title)
		{
			$check['flag'] = INVALID_CONF_TITLE;
		} elseif (!$title->exists()) {
			$check['flag']= CONF_NOT_EXISTS;
		}
		return $check; 
	}
	private function getCountries($default=null)
	{
		global $wgCountries;
		$selected = false;
		$html ='<select id="countries" name="country">';
		foreach ($wgCountries as $country)
		{
			if( !is_null($default) && $default === $country)
			{
				$selected = true;
			}
			$html.='<option' . ( $selected ? ' selected ' : '' ) .'>'.
						$country.
					'</option>';
		}
		$html.= '</select>';
		return $html;
		
	}
}