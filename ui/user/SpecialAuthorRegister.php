<?php
/**
 * 
 * @author chughakshay16@gmail.com
 * This class deals with the author registration process along with the submission of a proposal.
 * @todo - 1. create error case templates 2. remove test template(line 45)
 * 3. there is a big problem in this code, title parameter which we have for submission title should be changed from title to something else
 * 4. make two more actions for this class subdeleteview, authordeleteview
 * 5. I still have to figure out how I am storing values
 * 6. change the name of parameter 'title' to something else
 *
 */
class SpecialAuthorRegister extends SpecialPage
{
	//These constants are just for debuggin purposes
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
	const SUCCESS_EDIT =11;
	const SUCCESS_DELETE = 12;
	const DELETE_OBJECT_FAIL=13;
	const DELETE_OBJECT_ABSENT=14;
	const CREATE_SUCCESS =15;
	const CREATE_OBJ_FAIL = 16;
	/**
	 * 
	 * @var Int page_id of the conference page, which this author belongs to
	 */
	private $conferenceId;
	/**
	 * 
	 * @var String title of the conference page 
	 */
	private $conferenceTitle;
	/**
	 * 
	 * @param unknown_type $name
	 */
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
		/* test template - remove this when you are done with testing the UI in browsers*/
		/*if(true)
		{
			$out->setPageTitle('Submission Form');
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
		} else {}*/
			
		/* check if the user is logged in */
		if(!$user->isLoggedIn()){
		
			$out->addHTML($this->getMsgDivBlock('user-not-logged'));
			return ;	
							
		}
		
		/* if the $par value is not passed, load the default page with the submission links for all the running conferences */
		if(!$par)
		{
			
			$out->addHTML($this->getDefaultForm());
			return ;
			
		}
		
		/* check the validity of $par */
		$title = Title::newFromText($par);
		$template = new ErrorTemplate();
		$template->set('error',true);
		if(!$title)	/* if the title is invalid */
		{
				
			$html =$this->getMsgDivBlock('par-invalid');
			$html .= $this->getDefaultForm();
			$out->addHTML($html);
			return ;		
			
			  
		} elseif (!$title->exists()) /* title doesnt exist in the database */{
			
			 $html =$this->getMsgDivBlock('auth-reg-noconf');
			 $html .= $this->getDefaultForm();
			 $out->addHTML($html);
			 return ;
			 
		} else {	
			
				//here we could have also used ConferenceUtils::getConferenceId($title->getDBKey()) to get the conference id
				$this->conferenceId = $title->getArticleID();
				$this->conferenceTitle = $title->getDBKey();
				
				/* now we will deal with different values of action parameter */ 
				$type = $request->getVal('action',null);
				
				/* 'createview' will create a proposal submission form for the author */
				if($type == 'createview') 
				{
					
					$template = $this->prepareCreateViewTemplate();
						
				} elseif ($type == 'editauthorview') /* 'editauthorview' will load the form for editing the details of author */{
					
					$output = $this->prepareAuthorEditViewTemplate();
					$template = $output['template'];

					
				} elseif ($type == 'editsubview') {
					
						$output = $this->prepareSubEditViewTemplate();
					$template = $output['template'];
					
						
				} elseif ($type == 'processcreate') {
					
					$output = $this->processCreate();
					if($output['flag']==self::CREATE_SUCCESS)
					{
						$redirectUrl = $output['redirect'];
						$out->redirect($redirectUrl);
						return;
					} else {
						$template = $output['template'];
					}
					
				} elseif ($type == 'processauthoredit') {
					
					$output = $this->processAuthorEdit();
					if($output['flag']==self::SUCCESS_EDIT) 
					{
						$redirectUrl = $output['redirect'];
						$out->redirect($redirectUrl);
						return ;
					} else {
						$template = $output['template'];
					}
					
				} elseif ($type == 'processsubedit') {
					
					$output = $this->processSubEdit();
					if($output['flag']==self::SUCCESS_EDIT) 
					{
						$redirectUrl = $output['redirect'];
						$out->redirect($redirectUrl);
						return ;
					} else {
						$template = $output['template'];
					} 
					
			
				} elseif ($type == 'processauthordelete') {
					
					$output = $this->processAuthorDelete();
					if($output['flag']==self::SUCCESS_DELETE) 
					{
						$redirectUrl = $output['redirect'];
						$out->redirect($redirectUrl);
						return ;
					} else {
						$template = $output['template'];
					}
					
				} elseif ($type == 'processsubdelete') {
					
					$output = $this->processSubDelete();
					if($output['flag']==self::SUCCESS_DELETE) 
					{
						$redirectUrl = $output['redirect'];
						$out->redirect($redirectUrl);
						return ;
					} else {
						$template = $output['template'];
					}
					
				} else {
					
					//no-action template
					$template = new ErrorTemplate('');
					
				}
			$out->addModules('ext.conventionExtension.authorregister');
			$out->addTemplate($template);
		}
		
	}
	private function getMsgDivBlock($msgCode)
	{
		$html ='';
		$html.= Xml::openElement('div').
					Xml::openElement('p')
						.wfMsg($msgCode)
					.Xml::closeElement('p')
				.Xml::closeElement('div');
		return $html;
		
	}
	private function getLink($title=null,$action=null,$query=null)
	{
		$linkUrl=null;
		if($title && $action && $query)
		{
			$linkTitle = SpecialPage::getTitleFor('AuthorRegister', $title);
			$linkUrl = $linkTitle->getFullURL('action='.$action.$query);
		} elseif ($title && $action) {
			$linkTitle = SpecialPage::getTitleFor('AuthorRegister', $title);
			$linkUrl = $linkTitle->getFullURL('action='.$action);
		}else {
			//we just want the link to default page
			$linkTitle = Title::makeTitle(NS_SPECIAL,'AuthorRegister');
			$linkUrl = $linkTitle->getFullURL();
		}
		return $linkUrl;
		
	}	
	/* change the conf-para-intro when there is no link found or when there is only one conference */
	private function getDefaultForm()
	{
		//first of all get the list of conference titles
		$rows = ConferenceUtils::getConferenceTitles();
		$html='';
		if($rows)
		{
			//here rows is the result set
			$html =	Xml::element('p',array('id'=>'confpara','class'=>'conf-para'),wfMsg('conf-para-intro'));
			$html.= Xml::openElement('table',array('class'=>'conf-links','id'=>'conflinks'))
			.Xml::openElement('tbody');
			foreach ($rows as $row)
			{
				//capital or small first letter wont matter as eventually we will call Title::newFromText()
				//this title value will have the first letter capitalised as it is fetched from the database
				$title = $row->page_title;
				$title = Title::makeTitle(NS_MAIN, $title);
				$titleUrl = $title->getFullURL();
				//here linkUrl is of the form http://localhost/play/index.php?title=Special:AuthorRegister/<par>&action=createview
				$html.= Xml::openElement('tr'). 
					Xml::openElement('td').
					Xml::openElement('a',array('href'=>$titleUrl)).
					$title.
					Xml::closeElement('a').
					Xml::closeElement('td').
					Xml::openElement('td').
					Xml::openElement('a',array('href'=>$this->getLink($title->getDBKey(),'createview'))).
					wfMsg('default-submit').
					Xml::closeElement('td').
					Xml::closeElement('tr');				
			}
			$html.= Xml::closeElement('tbody');
			$html.=Xml::closeElement('table');
			
		} else {
			//if no conferences exist
			$html.= Xml::element('p',array('id'=>'confpara','class'=>'conf-para'),wfMsg('auth-reg-noconf-para'));
		}
		return $html;
		
	}
	private function processSubDelete()
	{
		$request = $this->getRequest();
		$user = $this->getUser();
		//here title is the right most part of the title in url
		$title = $request->getVal('submission','');
		//we have already checked the validity of conference in $par
		$conferenceTitle = $this->conferenceTitle;
		$conference = $this->loadConferenceTag();
		$params = new DerivativeRequest(
		$request,
		array(
		'action'=>'subdelete',		
		'title'=>$title,
		'conference'=>$conference['title']));
		$api = new ApiMain($params, true);
		$api->execute();
		$data = & $api->getResultData();
		$result = $data['subdelete'];
		if($result['done'])
		{
			
			//everything went fine , refirect user to the author page and state that the process went okay
			$authorTitle = 'authors/'.$user->getName();
			$title = Title::newFromText($authorTitle);
			$redirectUrl = $title->getLocalURL();
			//$out->redirect($redirectUrl);
			$output['flag']=self::SUCCESS_DELETE;
			$output['redirect']=$redirectUrl;
			
		} elseif ($result['flag']==Conference::ERROR_DELETE) {
			
			$template = new ErrorTemplate();
			$template->set('errorMsg',wfMsg('error-del','submission'));
			$subtitle = $conferenceTitle.'/authors/'.$user->getName().'/submissions/'.$title;
			$subpage=Title::makeTitle(NS_MAIN,$subtitle)->getFullURL();
			$title = $this->getTitle();
			$url = $title->getFullURL();
			$link = array('url'=>$url,'name'=>$title,'subpage'=>$subpage);
			$template->set('linkto',$link);
			$template->set('deleteMsg',wfMsg('sub-delete'));
			$output['template']= $template;
			$output['flag']=self::DELETE_OBJECT_FAIL;
			
		} elseif ($result['flag']==Conference::ERROR_MISSING) {
			
			$authorId = ConferenceAuthorUtils::getAuthorId($user->getId());
			$author = ConferenceAuthor::loadFromId($authorId);
			$template = $this->getTemplateWithSubmissionLinks($author);
			$template->set('errorMsg',wfMsg('sub-absent'));
			$output['template']=$template;
			$output['flag']=self::DELETE_OBJECT_MISSING;
			
		}
		return $output;
	}
	private function processAuthorDelete()
	{
		$request = $this->getRequest();
		$user = $this->getUser();
		$params = new DerivativeRequest(
		$request,
		array(
		'action'=>'authordelete'));
		$api = new ApiMain($params, true);
		$api->execute();
		$data = & $api->getResultData();
		$result = $data['authordelete'];
		if($result['done'])
		{
			
			//redirect user to the user page
			$userPage = $user->getUserPage();
			$userpageUrl = $userPage->getLocalURL();
			$output['flag']=self::SUCCESS_DELETE;
			$output['redirect']=$userPageUrl;
			
		} elseif ($result['flag']==Conference::ERROR_DELETE) {
			
			//error page , ask user to delete again(there wont be any undelete in this case, and if in future 
			//u decide to add this as a functionality you would have to come up with a 
			//complex function of undeleting all the sub-author and submission wiki pages)
			$template = new ErrorTemplate();
			$template->set('errorMsg',wfMsg('error-del','author'));
			$title = $this->getTitle();
			$url = $title->getFullURL();
			$link = array('url'=>$url,'name'=>$user->getName(),'userpage'=>$user->getUserPage()->getFullURL());
			$template->set('linkto',$link);
			$template->set('deleteMsg',wfMsg('auth-delete'));
			$output['template']= $template;
			$output['flag']=self::DELETE_OBJECT_FAIL;
			
				
		} elseif ($result['flag']==Conference::ERROR_MISSING) {
				
			//error page stating that no author was found with this user account
				$template = new ErrorTemplate();
				$template->set('errorMsg','auth-absent');
				$template->set('errorHtml',$this->getDefaultForm());
				$output['flag']=self::DELETE_OBJECT_ABSENT;
		}
		return $output;
	}
	private function processSubEdit()
	{
		$output = array();
		$request = $this->getRequest();
		$user = $this->getUser();
		$title = $request->getVal('subtitle','');
		$track = $request->getVal('track','');
		$type = $request->getVal('type','');
		$length = $request->getVal('length','');
		$abstract = $request->getVal('abstract','');
		$slidesinfo = $request->getVal('slidesinfo','');
		$slotreq = $request->getVal('slotreq','');
		/* ugly hack to get around ApiAuthorSubmissionEdit */
		$conference = $this->loadConferenceTag();
		$params = new DerivativeRequest(
		$request,
		array('action'=>'subedit',
		'title'=>$title,
		'track'=>$track,
		'type'=>$type,
		'length'=>$length,
		'abstract'=>$abstract,
		'slidesinfo'=>$slidesinfo,
		'slotreq'=>$slotreq,
		'conference'=>$conference['title']));
		//use try and catch block
		$api = new ApiMain($params, true);
		$api->execute();
		$data = & $api->getResultData();
		$result = $data['subedit'];
		if($result['done'])
		{
			//everything went fine , redirect user to the updated submission page
			$submissionTitle = $this->conferenceTitle.'/authors/'.$user->getName().'/submissions/'.$title;
			$title = Title::newFromText($submissionTitle);
			$redirectUrl = $title->getLocalURL();
			//$out->redirect($redirectUrl);
			$output['flag']=self::SUCCESS_EDIT;
			$output['redirect']=$redirectUrl;	
		} elseif ($result['flag']==Conference::ERROR_EDIT) {
			
			$output['flag']=self::LOAD_FROMID_FAIL;
			$outputOld=$this->prepareSubEditViewTemplate();
			//here we are assuming that prepareAuthorEditView will only give us a template which is an instance of AuthorRegisterTemplate
			$template = $outputOld['template'];
			$template->set('titleVal',$title);
			$template->set('typeVal',$type);
			$template->set('trackVal',$track);
			$template->set('abstractVal',$abstract);
			$template->set('lengthVal',$length);
			$template->set('slotreqVal',$slotreq);
			$template->set('slidesinfoVal',$slidesinfo);
			//just make sure that we dont override the error parameters in the template object
			//although the values set here would be the same as in prepareSubEditViewTemplate() ~ LOAD_FROMID_FAIL error
			if(!$template->haveData('error'))
			{
				$template->set('error',true);
				$template->set('errorMsg',wfMsg('load-fromid-fail','submission'));
			}
			$output['template']=$template;
			
			
		} elseif ($result['flag']==Conference::ERROR_MISSING) {
			
			$output['flag']=self::LOAD_OBJECT_ABSENT;	
			$user = $this->getUser();
			$authorId = ConferenceAuthorUtils::getAuthorId($user->getId());
			$author = ConferenceAuthor::loadFromId($authorId);
			$template = $this->getTemplateWithSubmissionLinks($author);
			$template->set('errorMsg',wfMsg('sub-absent'));
			$output['template']=$template;
			
		}
		return $output;
	}
	private function loadConferenceTag()
	{
		$article = Article::newFromID($this->conferenceId);
		$content = $article->fetchContent();
		preg_match_all('/<conference title="(.*)" venue="(.*)" capacity="(.*)" startDate="(.*)" endDate="(.*)" description="(.*)" cvext-type="(.*)" \/>/',
		$content,$matches);
		$conference = array('title'=>$matches[1][0], 'venue'=>$matches[2][0], 'capacity'=>$matches[3][0], 'startDate'=>$matches[4][0], 'endDate'=>$matches[5][0], 'description'=>$matches[6][0]);
		return $conference;
	}
	private function processAuthorEdit()
	{
		$output = array();
		$user = $this->getUser();
		$request = $this->getRequest();
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
		$result = $data['authoredit'];
		if($result['done'])
		{
			
			//everything went fine 
			//now redirect user to the author page
			//$authorTitle = $conferenceTitle.'/authors/'.$user->getName();
			$authorTitle = 'authors/'.$user->getName();
			$titleObj = Title::newFromText($authorTitle);
			$redirectUrl = $titleObj->getLocalURL();
			$output['flag']=self::SUCCESS_EDIT;
			$output['redirect']=$redirectUrl;
			
		} elseif ($result['flag']==Conference::ERROR_MISSING) {
			
			$output['flag'] = self::LOAD_OBJECT_ABSENT;
			$template = new ErrorTemplate();
			$template->set('errorMsg',wfMsg('auth-absent'));
			$template->set('errorHtml',$this->getDefaultForm());
			$output['template']=$template;
			
		} elseif ($result['flag']==Conference::ERROR_EDIT) {
	
			$output['flag']=self::LOAD_FROMID_FAIL;
			//here we are assuming that prepareAuthorEditView will only give us a template which is an instance of AuthorRegisterTemplate
			$outputOld= $this->prepareAuthorEditViewTemplate();
			$template = $outputOld['template'];
			//just make sure that we dont override the error parameters in the template object
			//although the values set here would be the same as in prepareSubEditViewTemplate() ~ LOAD_FROMID_FAIL error
			if(!$template->haveData('error'))
			{
				$template->set('error',true);
				$template->set('errorMsg',wfMsg('load-fromid-fail','author'));
			}
			$template->set('countryVal',$country);
			$template->set('affiliationVal',$affiliation);
			$template->set('urlVal',$url);	
			$output['template']=$template;
			
		}
		return $output;
	}
	private function processCreate()
	{
		//no need for any checks , only criteria for anyone to create a new author is that the user must be logged in, 
		//which we have already checked before
		$conferenceId = $this->conferenceId;
		$request = $this->getRequest();
		$user = $this->getUser();
		$conferenceTitle = $this->conferenceTitle;
		
		$actionType = $request->getVal('create',null);/* hidden input element */
		$title = $request->getVal('subtitle',null);
		$output = array();
		$type = $request->getVal('type','');
		//we still have to figure out how we are storing the track values
		$track = $request->getVal('track','');
		$abstract = $request->getVal('abstract','');
		$length = $request->getVal('length','');
		$slotreq = $request->getVal('slotreq','');
		$slidesinfo = $request->getVal('slidesinfo','');
		$country = $request->getVal('country','');
		$affiliation = $request->getVal('affiliation','');
		$url = $request->getVal('url','');
		// these are just the puppet objects
		$submission = new AuthorSubmission(null, null, $title, $type, $abstract, $track, $length, $slidesinfo, $slotreq);
		$tempAuthor = new ConferenceAuthor(null,null,null,$country,$affiliation, $url,null);
		/* if the title of submission is not passed dont go an further */
		if($title)
		{
			/* validity of title input */
			$titleObj = Title::newFromText($title);
			if(!$titleObj)
			{
				
				$output['flag']=self::INVALID_SUB_TITLE;
				$error=array('info'=>'invalid-sub-title','par'=>'');
				$output['template'] = $this->prepareCreateViewTemplate(array('submission'=>$submission,'author'=>$tempAuthor),$error);
				return $output;
				
			}
			
			/* validating the other inputs */
			$errors = $this->mustValidateInputs($type,$track,$abstract,$length,$slotreq,$slidesinfo);
			if(count($errors))
			{
				
				$output['flag']=self::INVALID_PARAM_VALUE;
				$error = array('info'=>'invalid-par-value','par'=>$errors['param']);
				$output['template'] = $this->prepareCreateViewTemplate(array('submission'=>$submission,'author'=>$tempAuthor),$error);
				return $output;
				
			} 
			
		
			if($actionType==='onlysub')
			{
				
				//title is the only value that must be passed , rest all of the values are optional
				$author = ConferenceAuthor::createFromScratch($conferenceId,$user->getId(),'','','',$submission);
				$obj= array('submission'=>$submission);
				
			} elseif ($actionType==='bothauthorsub') {
						
				$errors = $this->mustValidateInputs($country, $affiliation, $url);
				if(count($errors))
				{
					
					$output['flag']=self::INVALID_PARAM_VALUE;
					$output['template'] = $this->prepareCreateViewTemplate(array('submission'=>$submission,'author'=>$tempAuthor),
							array('info'=>'invalid-par-value','par'=>$errors['param']));
					return $output;
					
				}	
				$author = ConferenceAuthor::createFromScratch($this->conferenceId, $user->getId(), $country, $affiliation, $url, $submission);
				$obj = array('author'=>$author,'submission'=>$submission);
						
			} else {
				
				$output['flag']=self::MISSING_HIDDEN_PARAM_VALUE;
				$output['param']='create';
				$template->set('errorMsg',wfMsg('missing-hidden-par-value','create'));
				$error = array('info'=>'missing-hidden','par'=>'');				
				$output['template']= $this->prepareCreateViewTemplate(array('submission'=>$submission,'author'=>$tempAuthor),$error);
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
					$output['flag']=self::CREATE_SUCCESS;
					$output['redirect']=$redirectUrl;
					return $output;
					
				} else {
						
						$output['flag']=self::CREATE_OBJECT_FAIL; 
						$error=array('par'=>'submission','info'=>'create-obj-fail');
						$output['template']=$this->prepareCreateViewTemplate($obj,$error);
						$output['object']='submission';
						return $output;
						
				}
			} else {
				
				$output['flag']=self::CREATE_OBJECT_FAIL;
				$error=array('par'=>'author','info'=>'create-obj-fail');
				$output['template']=$this->prepareCreateViewTemplate($obj,$error);
				$ouput['object']='author';
				return $output;
				
			}
		} else {
			
			$output['flag']=self::MISSING_PARAM_VALUE;
			$output['template']= $this->prepareCreateViewTemplate(array('submission'=>$submission,'author'=>$tempAuthor) ,
					array('info'=>'missing-param','par'=>'title'));
			return $output;
			
		}
	}
	private function getPreloadedSubTemplate()
	{
		
		$template = new AuthorRegisterTemplate();
		$template->set('title',wfMsg('sub-edit-title'));
		$template->set('submissionLegend',wfMsg('sub-edit-legend'));
		$template->set('minsmessage',wfMsg('sub-edit-minutes-msg'));
		$template->set('submit',wfMsg('sub-edit-submit'));
		$template->set('slidesinfo',wfMsg('sub-edit-slidesinfo'));
		$template->set('slotreq',wfMsg('sub-edit-slotreq'));
		$template->set('length',wfMsg('sub-edit-length'));
		$template->set('abstract',wfMsg('sub-edit-abstract'));
		$template->set('track',wfMsg('sub-edit-track'));
		$template->set('type',wfMsg('sub-edit-type'));
		return $template;
		
	}
	private function prepareSubEditViewTemplate()
	{
		
		$conferenceId = $this->conferenceId;		
		$user = $this->getUser();
		$request = $this->getRequest();
		$output = array();
		if(UserUtils::isSpeaker($user->getId()))
			{
				$authorId = ConferenceAuthorUtils::getAuthorId($user->getId());
				$author = ConferenceAuthor::loadFromId($authorId);
				//now check if the user has a sub-author account
				
				if(ConferenceAuthorUtils::hasChildAuthor($authorId,$conferenceId, false))
				{
					$submissionTitle = $request->getVal('submission',null);
					if($submissionTitle)
					{
						
						$template = new AuthorRegisterTemplate();
						$template->set('showAuthor',false);
						$template->set('showSubmission',true);
						/*$titleObj = $this->getTitle();
						$queryUrl = 'action=processsubedit';
						$actionUrl = $titleObj->getLocalURL($queryUrl);*/
						$actionUrl = $this->getLink($this->conferenceTitle,'processsubedit');
						$template->set('action',$actionUrl);
						$template->set('titlelbl',wfMsg('sub-edit-title'));
						$template->set('submissionLegend',wfMsg('sub-edit-legend'));
						$template->set('minsmessage',wfMsg('sub-edit-minutes-msg'));
						$template->set('submit',wfMsg('sub-edit-submit'));
						$template->set('slidesinfo',wfMsg('sub-edit-slidesinfo'));
						$template->set('slotreq',wfMsg('sub-edit-slotreq'));
						$template->set('length',wfMsg('sub-edit-length'));
						$template->set('abstract',wfMsg('sub-edit-abstract'));
						$template->set('track',wfMsg('sub-edit-track'));
						$template->set('type',wfMsg('sub-edit-type'));
						if( $author && $author->getAuthorId())
						{
							$confKey = 'conf-'.$conferenceId;
							$allSubmissions = $author->getSubmissions();
							$confSubmissions = $allSubmissions[$confKey]['submissions'];
							foreach ($confSubmissions as $confSubmission)
							{
								if($confSubmission->getTitle()==$submissionTitle)
								{
									$thisSubmission = $confSubmission;
									break;
								}
							}
							//now set up the template
							if(!isset($thisSubmission))
							{
								
								$output['flag']=self::LOAD_OBJECT_ABSENT;
								$template = $this->getTemplateWithSubmissionLinks($author);
								$template->set('errorMsg',wfMsg('sub-absent'));
								$output['template']= $template;
								return $output;
								
							}
							$template->set('titleVal',$thisSubmission->getTitle());
							$template->set('typeVal',$thisSubmission->getType());
							$template->set('trackVal',$thisSubmission->getTrack());
							$template->set('abstractVal',$thisSubmission->getAbstract());
							$template->set('lengthVal',$thisSubmission->getLength());
							$template->set('slotreqVal',$thisSubmission->getSlotReq());
							$template->set('slidesinfoVal',$thisSubmission->getSlidesInfo());
							$template->set('error',false);
							$output['template']=$template;
							$output['flag']=self::LOAD_TEMPLATE_SUCCESS;
						} else {
							
							$output['flag']=self::LOAD_FROMID_FAIL;
							$template->set('error',true);
							$template->set('errorMsg',wfMsg('load-fromid-fail','author'));
							$output['template']=$template;
							
						}
						
					} else {
						
						$output['flag']=self::MISSING_PARAM_VALUE;
						$template = $this->getTemplateWithSubmissionLinks($author,self::MISSING_PARAM_VALUE);
						$template->set('errorMsg',wfMsg('missing-param','submission'));
						$output['template']= $template;
						
					}
				} else {
					
					$output['flag']=self::NO_SUB_AUTHOR; 
					//find the other submissions for this author
					//first just check if author even has any sub-author pages
					$template = $this->getTemplateWithSubmissionLinks($author);
					if(!$template->haveData('submissionsNolinkMsg'))
					{
						$template->set('errorMsg',wfMsg('sub-author-absent'));
					}
					$output['template']= $template;
					
				}
			} else {
				
				$output['flag']=self::LOAD_OBJECT_ABSENT;
				$template = new ErrorTemplate();
				$template->set('errorMsg',wfMsg('auth-absent'));
				/* same as in editauthorviewtemplate */
				$template->set('errorHtml',$this->getDefaultForm());
				$output['template']= $template;
				
			}
			return $output;
	}
	private function getTemplateWithSubmissionLinks($author,$flag=null)
	{
		$template = new ErrorTemplate();
		$hasSubmissions = ConferenceAuthorUtils::hasSubmissions($author->getAuthorId());
		if($hasSubmissions)
		{
			$links = $this->getSubmissionLinks($author);
			$template->set('linksto',$links);
			$template->set('submissionsLinkMsg',wfMsg('submissions-link'));
			if(!$flag)
			{
				$template->set('createOneMsg',wfMsg('create-one'));
				$template->set('createMsg',wfMsg('create'));
				$linkUrl = Title::makeTitle(NS_MAIN,$this->conferenceTitle)->getFullURL();
				$template->set('createLink',$linkUrl);
			}
			$template->set('subEdit',wfMsg('sub-edit'));
			$template->set('subDelete',wfMsg('sub-delete'));
				
		} else {
			$template->set('submissionsNolinkMsg',wfMsg('submissions-nolink'));
			$template->set('errorHtml',$this->getDefaultForm());
		}
		return $template;
	}
	private function getSubmissionLinks($author)
	{
		//$author = ConferenceAuthor::loadFromId($authorId);
		$otherSubmissions = $author->getSubmissions();
		$links = array();
		if($otherSubmissions)
		{
			foreach ($otherSubmissions as $key=>$value)
			{
				//$links = array('title'=>$value['conf'],'url'=>)
				foreach ($value['submissions'] as $confSubmission)
				{
					$title = $confSubmission->getDBKey();
					$links[] = array('name'=>$title,'url'=>$confSubmission->getURL(),
							'edit'=>$this->getLink($value['conf'],'subeditview','submission='.$confSubmission->getTitle()),
							'delete'=>$this->getLink($value['conf'],'subdeleteview','submission='.$confSubmission->getTitle()));
				}
			}
				
		
		} else {
				
			return null;
				
		}
		return $links;
	}
	private function prepareAuthorEditViewTemplate($error=null)
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
			$template= new AuthorRegisterTemplate();
			$template->set('showAuthor',true);
			$template->set('showSubmission',false);
			$template->set('showAuthorSubmit',true);
			$template->set('submit',wfMsg('author-edit-submit'));
			/*$titleObj = $this->getTitle();
			$queryUrl = 'action=processauthoredit';
			$actionUrl = $titleObj->getLocalURL($queryUrl);*/
			$actionUrl = $this->getLink($this->conferenceTitle,'processauthoredit');
			$template->set('action',$actionUrl);
			$template->set('authorLegend',wfMsg('author-edit-legend'));
			$template->set('country',wfMsg('author-edit-country'));
			$template->set('affiliation',wfMsg('author-edit-affiliation'));
			$template->set('url',wfMsg('author-edit-blogurl'));
			if($author && $author->getAuthorId())
			{
				$country = $author->getCountry();
				$affiliation = $author->getAffiliation();
				$blogUrl = $author->getBlogUrl();
				$template->set('countries',$this->getCountries($country));
				$template->set('affiliationVal',$affiliation);
				$template->set('urlVal',$blogUrl);
				$template->set('error',false);
				$output['template'] = $template;
				$output['flag']=self::LOAD_TEMPLATE_SUCCESS;
			} else {
					$output['flag']=self::LOAD_FROMID_FAIL;
					$template->set('error',true);
					$template->set('errorMsg',wfMsg('load-fromid-fail','author'));
					$output['template']=$template;
			}
		} else {
			$output['flag']=self::LOAD_OBJECT_ABSENT;
			$template = new ErrorTemplate();
			$template->set('errorMsg',wfMsg('auth-absent'));
			$template->set('errorHtml',$this->getDefaultForm());
			$output['template']= $template;
		}
		return $output;
	}
	/**
	 * @todo - complete this function
	 */
	private function getAuthorPageLink()
	{
		
	}
	private function prepareCreateViewTemplate($obj=null,$error=null)
	{
		$user = $this->getUser();
		$showAuthor = !UserUtils::isSpeaker($user->getId());
		$template = new AuthorRegisterTemplate();
		/* showAuthor will decide if author fields are to be put in the form or not*/
		$template->set('showAuthor', $showAuthor);
		//$titleObj = $this->getTitle();
		//$queryUrl = 'action=processcreate';
		//$title = SpecialPage::getTitleFor('AuthorRegister');
		$actionUrl = $this->getLink($this->conferenceTitle,'processcreate');
		//$actionUrl = $title->getLocalURL('type=processcreate');
		$template->set('action', $actionUrl);
		$t = $this->getTitle();
		//$template->set('title',$t->getPrefixedText());
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
		if($obj)
		{
			$thisSubmission = $obj['submission'];
			if(isset($obj['author']))
			{
				$author= $obj['author'];
				$template->set('countryVal',$author->getCountry());
				$template->set('affiliationVal',$author->getAffiliation());
				$template->set('urlVal',$author->getBlogUrl());
				
			} 
			$template->set('titleVal',$thisSubmission->getTitle());
			$template->set('typeVal',$thisSubmission->getType());
			$template->set('trackVal',$thisSubmission->getTrack());
			$template->set('abstractVal',$thisSubmission->getAbstract());
			$template->set('lengthVal',$thisSubmission->getLength());
			$template->set('slotreqVal',$thisSubmission->getSlotReq());
			$template->set('slidesinfoVal',$thisSubmission->getSlidesInfo());
		}
		$template->set('showSubmission',true);
		$template->set('authorLegend', wfMsg('author-create-legend'));
		$template->set('submissionLegend',wfMsg('sub-create-legend'));
		$template->set('titlelbl',wfMsg('author-sub-reg-title'));
		$template->set('type',wfMsg('author-sub-reg-type'));
		$template->set('track',wfMsg('author-sub-reg-track'));
		$template->set('minsmessage',wfMsg('author-sub-reg-minutes-msg'));
		$template->set('abstract',wfMsg('author-sub-reg-abstract'));
		$template->set('slotreq',wfMsg('author-sub-reg-slotreq'));
		$template->set('slidesinfo',wfMsg('author-sub-reg-slidesinfo'));
		$template->set('length',wfMsg('author-sub-reg-length'));
		$template->set('submit',wfMsg('author-sub-reg-submit'));
		
		if($error)
		{
			$template->set('error',true);
			$template->set('errorMsg',wfMsg($error['info'],$error['par']));
		}
		return $template;
	}
	/* remove this function */
	private function performTitleCheck($par)
	{
		$check = array();
		$check['flag']=VALID_CONF_TITLE;
		$title = Title::newFromText($par);
		if(!$title)
		{
			$check['flag'] = self::INVALID_CONF_TITLE;
		} elseif (!$title->exists()) {
			$check['flag']= self::CONF_NOT_EXISTS;
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
			$selected = false;
		}
		$html.= '</select>';
		return $html;
		
	}
	private function mustValidateInputs($type=null,$track=null,$abstract=null,$length=null,$slotreq=null,$slidesinfo=null)
	{
		
	}
}