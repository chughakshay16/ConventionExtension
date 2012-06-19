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
	private $countries = array("Unspecified",
		"Afghanistan",
		"Albania",
		"Algeria",
		"Andorra",
		"Angola",
		"Antigua and Barbuda",
		"Argentina",
		"Armenia",
		"Australia",
		"Austria",
		"Azerbaijan",
		"Bahamas",
		"Bahrain",
		"Bangladesh",
		"Barbados",
		"Belgium",
		"Belize",
		"Belarus",
		"Benin",
		"Bhutan",
		"Bolivia",
		"Bosnia and Herzegovina",
		"Botswana",
		"Brazil",
		"Brunei",
		"Bulgaria",
		"Burkina Faso",
		"Burma",
		"Burundi",
		"Cambodia",
		"Cameroon",
		"Canada",
		"Cape Verde",
		"Central African Republic",
		"Chad",
		"Chile",
		"China (People's Republic)",
		"Colombia",
		"Comoros",
		"Costa Rica",
		"Côte d'Ivoire",
		"Croatia",
		"Cuba",
		"Cyprus",
		"Czech Republic",
		"Democratic Republic of the Congo",
		"Denmark",
		"Djibouti",
		"Dominica",
		"Dominican Republic",
		"East Timor",
		"Ecuador",
		"Egypt",
		"El Salvador",
		"Equatorial Guinea",
		"Eritrea",
		"Estonia",
		"Ethiopia",
		"Federated States of Micronesia",
		"Fiji",
		"Finland",
		"France",
		"Gabon",
		"Gambia",
		"Georgia",
		"Germany",
		"Ghana",
		"Greece",
		"Grenada",
		"Guatemala",
		"Guinea",
		"Guinea-Bissau",
		"Guyana",
		"Haiti",
		"Honduras",
		"Hungary",
		"Iceland",
		"India",
		"Indonesia",
		"Iran",
		"Iraq",
		"Ireland",
		"Israel",
		"Italy",
		"Jamaica",
		"Japan",
		"Jordan",
		"Kazakhstan",
		"Kenya",
		"Kiribati",
		"Kuwait",
		"Kyrgyzstan",
		"Laos",
		"Latvia",
		"Lebanon",
		"Lesotho",
		"Liberia",
		"Libya",
		"Liechtenstein",
		"Lithuania",
		"Luxembourg",
		"Macedonia",
		"Madagascar",
		"Malawi",
		"Malaysia",
		"Maldives",
		"Mali",
		"Malta",
		"Marshall Islands",
		"Mauritania",
		"Mauritius",
		"Mexico",
		"Moldova",
		"Monaco",
		"Mongolia",
		"Montenegro",
		"Morocco",
		"Mozambique",
		"Namibia",
		"Nauru",
		"Nepal",
		"Netherlands",
		"New Zealand",
		"Nicaragua",
		"Niger",
		"Nigeria",
		"North Korea",
		"Norway",
		"Oman",
		"Pakistan",
		"Palau",
		"Panama",
		"Papua New Guinea",
		"Paraguay",
		"Peru",
		"Philippines",
		"Poland",
		"Portugal",
		"Qatar",
		"Republic of Congo",
		"Romania",
		"Russia",
		"Rwanda",
		"Saint Kitts and Nevis",
		"Saint Lucia",
		"Saint Vincent and the Grenadines",
		"Samoa",
		"San Marino",
		"São Tomé and Príncipe",
		"Saudi Arabia",
		"Senegal",
		"Serbia",
		"Seychelles",
		"Sierra Leone",
		"Singapore",
		"Slovakia",
		"Slovenia",
		"Solomon Islands",
		"Somalia",
		"South Africa",
		"South Korea",
		"Spain",
		"Sri Lanka",
		"Sudan",
		"Suriname",
		"Swaziland",
		"Sweden",
		"Switzerland",
		"Syria",
		"Thailand",
		"Tajikistan",
		"Tanzania",
		"Togo",
		"Tonga",
		"Trinidad and Tobago",
		"Tunisia",
		"Turkey",
		"Turkmenistan",
		"Tuvalu",
		"Uganda",
		"Ukraine",
		"United Arab Emirates",
		"United Kingdom",
		"United States of America",
		"Uruguay",
		"Uzbekistan",
		"Vanuatu",
		"Vatican",
		"Venezuela",
		"Vietnam",
		"Yemen",
		"Zambia",
		"Zimbabwe",
		"Taiwan (Republic of China)",
		"Hong Kong",
		"Macao",
		"Palestinian Territories",
		"Other");
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
						$venue = $country."-".$city."-".$place;
						//startDate and endDate will be sent in ddMMyyyy format from client, if not they will be caught as an error in above if block
						$conference = Conference::createFromScratch($title, $venue, $capacity, $startDate, $endDate, $description);
						if($conference && $conference->getId())
						{
							//everything went okay , now redirect towards Special:Dashboard/$par
							//where $par is the title of the conference
							$conferenceSessionArray = array();
							$conferenceSessionArray['id']=$confernece->getId();
							$conferenceSessionArray['title']=ConferenceUtils::getTitle($conference->getId());
							$request->setSessionData('conference', $conferenceSessionArray);
							$title = self::getSafeTitleFor('Dashboard',$conferenceSessionArray['title']);
							$url = $title->getLocalURL();
							$out->addHTML($url);
							$out->redirect($url);
							
							
						} else {
							//something went wrong, notify the user to re-do the setup process
							$template = $this->getConferenceTemplate();
							$template->set('action',$actionUrl);
							$this->writeHTMLMessage('error');
							$template->set('errorMsg',$this->htmlText);
							$template->set('countries',$this->countries);
							$out->addTemplate($template);
						}
					}
					
				} elseif (!$action || $action=''){
					//create the form and render it for the user
					$template = $this->getConferenceTemplate();
					$template->set('action',$actionUrl);
					$template->set('countries',$this->countries);
					$out->addTemplate($template);
			
				}
			} else {
				$this->writeHTMLMessage('usercant');
			}
		} else {
			$htmlText = $this->writeHTMLMessage('notloggedin');
		}
		$out->addModules('ext.conventionExtension.confsetup');
		$out->addHTML($this->htmlText);
		
	}
	
	private function writeHTMLMessage($type='error', $errors=null)
	{
		if($type==='error')
		{
			
			$msg=wfMsg('setup-error');
			
		} elseif ($type==='usercant'){
			
			$msg = wfMsg('setup-user-cant');
			
		} elseif ($type==='notloggedin'){
			
			$msg = wfMsg('setup-notlogged');
			
		} elseif ($type==='invalid'){
			
			$msg = wfMsg('setup-invalid-data');
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
	
	}	
	private function extractRequestParams()
	{
	
	}
	private function getConferenceTemplate()
	{
		$template = new ConferenceSetupTemplate();
		$template->set('title',wfMsg('setup-title'));
		$template->set('startdate',wfMsg('setup-startdate'));
		$template->set('enddate',wfMsg('setup-enddate'));
		$template->set('description',wfMsg('setup-description'));
		$template->set('capacity',wfMsg('setup-capacity'));
		$template->set('country',wfMsg('setup-country'));
		$template->set('place',wfMsg('setup-place'));
		$template->set('city',wfMsg('setup-city'));
		$template->set('submit',wfMsg('setup-submit'));
		$template->set('heading',wfMsg('setup-heading'));
		return $template;
	}

}