<?php
/**
 * 
 * Enter description here ...
 * @author chughakshay16
 * @todo - still need to add JS and CSS modules
 *
 */
class SpecialConferenceSetup extends SpecialPage
{
	private $htmlText;
	public function __construct($name= 'ConferenceSetup' )
	{
		parent::__construct( $name );
	}
	public function execute($par)
	{
		/**
		 * 1. check for the user credentials and see if the user is allowed to see this page
		 * 2. and if user has the permission render the form 
		 * In our case we are only giving 'sysop' as the right to create a conference
		 * we could implement it in a different way
		 * like this :-
		 * $wgGroupPermissionArrays['sysop']['createConference']=true;
		 * $wgAvailableRights[]='createConference'
		 * and then call $user->isAllowed('createConference');
		 */
		global $wgCountries;
		$this->setHeaders();
		$titleObj = $this->getTitle();
		$queryUrl = 'action=create';
		$actionUrl = $titleObj->getLocalURL($queryUrl);
		$user = $this->getUser();
		$request = $this->getRequest();
		$action = $request->getVal('action');
		$out = $this->getOutput();
		$pageTitle = wfMsg('conference-setup');
		$out->setPageTitle($pageTitle);
		$this->htmlText='';
		if($user->isLoggedIn())
		{
			$groups = $user->getAllGroups();
			if(in_array('sysop', $groups))
			{
				if($action==='create')
				{
					$params = $this->extractRequestParams();
					//we should not be very much concerned about the types of params passed in the url as 
					//we are gonna be storing them as strings only
					$title = $request->getVal('titletext');
					$startDate = $request->getVal('sdvalue');
					$endDate = $request->getVal('edvalue');
					$capacity = $request->getVal('capvalue');
					$country = $request->getVal('country');
					$city = $request->getVal('city');
					$place = $request->getVal('place');
					$description = $request->getVal('description');
					$errors = $this->mustValidateInputs($title, $startDate, $endDate, $capacity, $country, $place, $city, $description);
					if(count($errors))
					{
						$this->writeHTMLMessage('invalid',$errors);
					} else {
						//all data seems fine , store the values in a wiki page
						$venue = $place.','.$city.','.$country;
						$startDate = str_replace('/','',$startDate);
						$endDate = str_replace('/','',$endDate);
						//startDate and endDate will be sent in mm/dd/yyyy format from client, if not they will be caught as an error in above if block
						$conference = Conference::createFromScratch($title, $venue, $capacity, $startDate, $endDate, $description);
						if($conference && $conference->getId())
						{
							//everything went okay , now redirect towards Special:Dashboard/$par
							//where $par is the title of the conference
							$conferenceSessionArray = array();
							$conferenceSessionArray['id']=$conference->getId();
							$conferenceSessionArray['title']=ConferenceUtils::getTitle($conference->getId());
							$request->setSessionData('conference', $conferenceSessionArray);
							$title = self::getSafeTitleFor('Dashboard',$conferenceSessionArray['title']);
							$url = $title->getLocalURL();
							//$out->addHTML($url);
							$out->redirect($url);
							
							
						} else {
							//something went wrong, notify the user to re-do the setup process
							$template = $this->getConferenceTemplate();
							$template->set('action',$actionUrl);
							$this->writeHTMLMessage('error');
							$template->set('errorMsg',$this->htmlText);
							$template->set('countries',$wgCountries);
							$out->addTemplate($template);
						}
					}
					
				} elseif (!$action || $action=''){
					//create the form and render it for the user
					$template = $this->getConferenceTemplate();
					$template->set('action',$actionUrl);
					$template->set('countries',$wgCountries);
					$out->addTemplate($template);
					$out->addModules('ext.conventionExtension.confsetup');
			
				}
			} else {
				$this->writeHTMLMessage('usercant');
			}
		} else {
			$htmlText = $this->writeHTMLMessage('notloggedin');
		}
		$out->addHTML($this->htmlText);
		
	}
	
	private function writeHTMLMessage($type='error', $errors=null)
	{
		if($type==='error')
		{
			
			$msg=wfMsg('cvext-setup-error');
			
		} elseif ($type==='usercant'){
			
			$msg = wfMsg('cvext-setup-user-cant');
			
		} elseif ($type==='notloggedin'){
			
			$msg = wfMsg('cvext-setup-notlogged');
			
		} elseif ($type==='invalid'){
			
			$msg = wfMsg('cvext-setup-invalid-data');
			$msg.='<br />';
			$msg.= $errors?$errors['msg']:'No Specific Details were found for the invalid data passed';
			
		} else {
			
			$msg='';
			
		}
		
		
		$html = '';
		$html.= '<div class="cvext-user-perm">'.
		'<p class="cvext-user-perm">'.$msg.'</p></div>';
		$this->htmlText.= $html;
		
	}
	private function mustValidateInputs($title,$startDate, $endDate, $capacity, $country, $place, $city,$description)
	{
		return array();
	}	
	private function extractRequestParams()
	{
	
	}
	private function getConferenceTemplate()
	{
		$template = new ConferenceSetupTemplate();
		$template->set('title',wfMsg('cvext-setup-title'));
		$template->set('startdate',wfMsg('cvext-setup-startdate'));
		$template->set('enddate',wfMsg('cvext-setup-enddate'));
		$template->set('description',wfMsg('cvext-setup-description'));
		$template->set('capacity',wfMsg('cvext-setup-capacity'));
		$template->set('country',wfMsg('cvext-setup-country'));
		$template->set('place',wfMsg('cvext-setup-place'));
		$template->set('city',wfMsg('cvext-setup-city'));
		$template->set('submit',wfMsg('cvext-setup-submit'));
		$template->set('heading',wfMsg('cvext-setup-heading'));
		return $template;
	}

}