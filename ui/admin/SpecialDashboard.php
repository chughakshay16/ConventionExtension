<?php
class SpecialDashboard extends SpecialPage
{
	private $pageTypes = array(
	'Welcome Page',
	'Submissions Page',
	'Contact Us Page',
	'Registration Page',
	'Organizers Page',
	'Schedule Page',
	);
	private $conference;
	private $locations;
	private $preloadedGroups = array(
	'All',
	'Organizers',
	'Registered Users');
	public function __construct($name='Dashboard')
	{
		parent::__construct($name);
	}
	public function execute($par)
	{
		/**
		 * we are going to take care of nine possible scenarios in which a user can access this page
		 * 1. it is re-directed from ConferenceSetup page
		 * 2. user types Special:Dashboard/<conference-title> (not logged in)
		 * 3. user types Special:Dashboard/<conference-title> (logged in but doesnt have rights)
		 * 4. user types Special:Dashboard/<conference-title> (logged in , and has rights)
		 * 5. user types Special:Dashboard/<conference-title> (logged in , has rights and is an organizer)
		 * 6. user types Special:Dashboard/<conference-title> (logged in , has rights and is not an organizer)
		 * 7. user types Special:Dashboard/<conference-title> (logged in , doesnt have rights and is an organizer)
		 * 8. user types Special:Dashboard/<conference-title> (logged in , doesnt have rights and is not an organizer)
		 * 9. $par parameter is not specified in the url
		 * if a user is logged then a session must be initialized in Setup.php, but it may not be necessary that the conference session data is
		 * still there
		 */
		$this->setHeaders();
		$this->outputHeader();
		$user = $this->getUser();
		$out = $this->getOutput();
		$request = $this->getRequest();
		
		
		if(!$par)
		{
			
			$out->addHTML($this->noParValuePresent());
			
		} else {
			
			//check for the validity of $par
			$title = Title::newFromText($par);
			if(!$title)
			{
				
				$out->addHTML($this->invalidParValue());
				
			} elseif (!$title->exists()) {
				
				//title doesnt exist
				$out->addHTML($this->titleNotExists());
				
			} else {
				//valid title and exists
				
			
				if(!$user->isLoggedIn())
				{
					$out->addHTML($this->userNotLogged());
				
				} else {
					$groups = $user->getAllGroups();
					if(session_id()=='')
					{
						//this step is not necessary because if a cookie is passed along in the request then the session must have started in Setup.php
						wfSetupSession();
					}
					if(in_array('sysop',$groups))
					{
			
						//scenario 4, 5 and scenario 6 are dealt with the same logic
						//user must see the dashboard
						$sessionData = $request->getSessionData('conference');
						if(!isset($sessionData))
						{
							//load the data from the database and set it in the session object
							
							$conferenceId = ConferenceUtils::getConferenceId($title->getDBkey());
							if($conferenceId!==false)
							{
								$conferenceSessionArray['id']= $conferenceId;
								$conferenceSessionArray['title']=$title->getDBkey();
								$request->setSessionData('conference',$conferenceSessionArray);
							}
							
						}
						$conferenceSessionArray = $request->getSessionData('conference');
						$conferenceId = $conferenceSessionArray['id'];
						$conferenceTitle = $conferenceSessionArray['title'];
						$this->conference = Conference::loadFromId($conferenceId);
						$out->addModules('ext.conventionExtension.dashboard');
						$out->addHTML($this->createDashboard());
						
			
					} elseif (UserUtils::isOrganizer($user->getId())) {
			
						//scenario 7
						$organizerRights = array();
						$out->addHTML(true,$organizerRights);
			
					} else {
			
						$out->addHTML($this->userNoRights());
			
					}
				
			}
			}
		}
	}
	private function userNoRights()
	{
		$html ='';
		$html.= '<p>'.
					wfMsg('dash-user-norights').
				'</p>';	
		return $html;
	
	}
	private function userNotLogged()
	{
		$html ='';
		$html.= '<p>'.
					wfMsg('dash-user-notlogged').
				'</p>';	
		return $html;				
	}
	private function titleNotExists()
	{
		$html ='';
		$html.='<p>'.
					wfMsg('dash-no-conference').
				'</p>';
		return $html;			
	}
	private function invalidParValue()
	{
		$html ='';
		$html.='<p>'.
					wfMsg('dash-invalid-par').
				'</p>';
		return $html;			
	}
	private function noParValuePresent()
	{
		$html = '';
		$html.= '<p>'.
					wfMsg('dash-nopar-msg').' index.php/Special:Dashboard/$par where $par is the valid title of the conference'.
				'</p>';
		return $html;				
	}
	private function createDashboard($forOrganizer = false , $organizerRights = array())
	{
		$html = '';
		$html.=/* Xml::openElement('form',array('id'=>'cvext-dash-form','class'=>'visualClear','method'=>'get','action'=>'')).*/
			/*Xml::openElement('ul',array('id'=>'dashtoc')).
				'<li class="selected">'.
					'<a id="dashtab-pages" href="#cvext-dash-pages">'.
						wfMsg('dash-pages').
					'</a></li>'.
				'<li class="">'.
					'<a id="dashtab-orgs" href="#cvext-dash-orgs">'.
						wfMsg('dash-organizers').
					'</a></li>'.
				'<li class="">'.
					'<a id="dashtab-accs" href="#cvext-dash-accs">'.
						wfMsg('dash-accounts').
					'</a></li>'.
				'<li class="">'.
					'<a id="dashtab-athrs" href="#cvext-dash-athrs">'.
						wfMsg('dash-authors').
					'</a></li>'.
				'<li class="">'.
					'<a id="dashtab-evts" href="#cvext-dash-evts">'.
						wfMsg('dash-events').
					'</a></li>'.
				'<li class="">'.
					'<a id="dashtab-locs" href="#cvext-dash-locs">'.
						wfMsg('dash-locations').
					'</a></li>'.
			Xml::closeElement('ul').*/
			Xml::openElement('div',array('id'=>'dashboard')).
				/*'<div class="dashsection">'.
				'</div>'.*/
				'<fieldset id="cvext-dashsection-pages">'.
					'<legend>'.
						wfMsg('dash-pages').
					'</legend>'.
					'<fieldset>'.
						'<legend>'.
							wfMsg('dash-create').' | '.wfMsg('dash-edit').' | '.wfMsg('dash-delete').' Pages'.
						'</legend>'.
						'<table><tbody>'.
							$this->getPageTypes().
						'</tbody></table>'.
						'<p>'.
							wfMsg('dash-pages-msg').
							'<a href="#">'.wfMsg('dash-pages-link').
							'</a>'.
				 		'</p>'.
				 	'</fieldset>'.
				'</fieldset>'.
				'<fieldset id="cvext-dashsection-orgs">'.
					'<legend>'.
							wfMsg('dash-organizers').
					'</legend>'.
					'<fieldset>'.
						'<legend>'.
							wfMsg('dash-org-add').
						'</legend>'.
						'<table><tbody>'.
							'<tr><td class="mw-label">'.
								'<label for="username">'.
									wfMsg('dash-org-username').
								'</label</td>'.
								'<td class="mw-input">'.
									'<input id="username" type="text" size="20" name="username" />'.
							'</td></tr>'.
							'<tr><td class="mw-label">'.
								'<label for="category">'.
									wfMsg('dash-org-category').
								'</label</td>'.
								'<td class="mw-input">'.
									'<input id="category" type="text" size="20" name="category" />'.
							'</td></tr>'.
							'<tr><td class="mw-label">'.
								'<label for="post">'.
									wfMsg('dash-org-post').
								'</label</td>'.
								'<td class="mw-input">'.
									'<input id="post" type="text" size="20" name="post" />'.
							'</td></tr>'.	
							'<tr><td></td>'.
									'<td class="mw-submit">'.
										'<input type="submit" value="'.wfMsg('dash-org-submit').'" />'.
							'</td></tr>'.
						'</tbody></table>'.
					'</fieldset>'.
					'<fieldset>'.
						'<legend>'.
							wfMsg('dash-org-editdelete').
						'</legend>'.	
						'<table class="cvext-tbl-res"><tbody>'.
							'<tr class="cvext-org-head">'.
								'<td>'.wfMsg('dash-org-username').'</td>'.
								'<td>'.wfMsg('dash-org-category').'</td>'.
								'<td>'.wfMsg('dash-org-post').'</td>'.
							'</tr>'.
							$this->getOrganizers().
						'</tbody></table>'.			
					'</fieldset>'.
				'</fieldset>'.
				'<fieldset id="cvext-dashsection-accts">'.
					'<legend>'.
						wfMsg('dash-accounts').
					'</legend>'.
					'<fieldset>'.
						'<legend>'.
							wfMsg('dash-accts-list').
						'</legend>'.
						'<table class="cvext-tbl-res"><tbody>'.
							$this->getAccounts().
						'</tbody></table>'.		
					'</fieldset>'.
				'</fieldset>'.
				'<fieldset id="cvext-dashsection-athrs">'.
					'<legend>'.
						wfMsg('dash-authors').
					'</legend>'.
					'<fieldset>'.
						'<legend>'.
							wfMsg('dash-athrs-list').
						'</legend>'.
						'<table class="cvext-tbl-res">'.
							'<tbody>'.
								$this->getAuthors().
							'</tbody>'.
						'</table>'.	
					'</fieldset>'.				
				'</fieldset>'.
				'<fieldset id="cvext-dashsection-evts">'.
					'<legend>'.
						wfMsg('dash-events').
					'</legend>'.
					'<fieldset>'.
						'<legend>'.
							wfMsg('dash-evts-create').
						'</legend>'.
						'<table>'.
							'<tbody>'.
								'<tr>'.
									'<td class="mw-label" >'.
										'<label for="topic">'.
											wfMsg('dash-evts-topic').' : '.
										'</label>'.
									'</td>'.
									'<td class="mw-input" >'.
										'<input type="text" id="topic" size="20" name="topic" />'.
									'</td>'.				
								'</tr>'.
								'<tr>'.
									'<td class="mw-label" >'.
										'<label for="group">'.
											wfMsg('dash-evts-group').' : '.
										'</label>'.
									'</td>'.
									'<td class="mw-input" >'.
										$this->getGroupsForEvents().
									'</td>'.				
								'</tr>'.
								'<tr>'.
									'<td class="mw-label" >'.
										'<label for="day">'.
											wfMsg('dash-evts-day').' : '.
										'</label>'.
									'</td>'.
									'<td class="mw-input" >'.
										$this->getDaysForEvents().
									'</td>'.				
								'</tr>'.
								'<tr>'.
									'<td class="mw-label" >'.
										'<label for="starttime">'.
											wfMsg('dash-evts-starttime').' : '.
										'</label>'.
									'</td>'.
									'<td class="mw-input" >'.
										'<input type="text" id="starttime" size="20" name="starttime" />'.
									'</td>'.				
								'</tr>'.
								'<tr>'.
									'<td class="mw-label" >'.
										'<label for="endtime">'.
											wfMsg('dash-evts-endtime').' : '.
										'</label>'.
									'</td>'.
									'<td class="mw-input" >'.
										'<input type="text" id="endtime" size="20" name="endtime" />'.
									'</td>'.				
								'</tr>'.
								'<tr>'.
									'<td class="mw-label" >'.
										'<label for="location">'.
											wfMsg('dash-evts-location').' : '.
										'</label>'.
									'</td>'.
									'<td class="mw-input" >'.
										$this->getLocationsForEvents().
									'</td>'.				
								'</tr>'.
								'<tr>'.
									'<td>'.
									'</td>'.
									'<td class="mw-submit">'.
										'<input type="submit" value="'.wfMsg('dash-evts-submit').'" />'.
									'</td>'.			
								'</tr>'.																	
							'</tbody>'.
						'</table>'.		
					'</fieldset>'.
					'<fieldset>'.
						'<legend>'.
							wfMsg('dash-evts-editdelete').
						'</legend>'.	
						'<table class="cvext-tbl-res"><tbody>'.
							'<tr class="cvext-res-head">'.
								'<td>'.wfMsg('dash-evts-topic').'</td>'.
								'<td>'.wfMsg('dash-evts-starttime').'</td>'.
								'<td>'.wfMsg('dash-evts-endtime').'</td>'.
								'<td>'.wfMsg('dash-evts-day').'</td>'.
							 	'<td>'.wfMsg('dash-evts-group').'</td>'.
								'<td>'.wfMsg('dash-evts-location').'</td>'.
							'</tr>'.
							$this->getEvents().
						'</tbody></table>'.			
					'</fieldset>'.										
				'</fieldset>'.
				'<fieldset id="cvext-dashsection-lcts">'.
					'<legend>'.
						wfMsg('dash-locations').
					'</legend>'.
					'<fieldset>'.
						'<legend>'.
							wfMsg('dash-lcts-add').
						'</legend>'.
						'<table>'.
							'<tbody>'.
								'<tr>'.
									'<td class="mw-label" >'.
										'<label for="roomno">'.
											wfMsg('dash-lcts-roomno').' : '.
										'</label>'.
									'</td>'.
									'<td class="mw-input" >'.
										'<input type="text" id="roomno" size="20" name="roomno" />'.
									'</td>'.				
								'</tr>'.
								'<tr>'.
									'<td class="mw-label" >'.
										'<label for="description">'.
											wfMsg('dash-lcts-description').' : '.
										'</label>'.
									'</td>'.
									'<td class="mw-input" >'.
										'<input type="text" id="description" size="20" name="description" />'.
									'</td>'.				
								'</tr>'.
								'<tr>'.
									'<td class="mw-label" >'.
										'<label for="imageurl">'.
											wfMsg('dash-lcts-url').' : '.
										'</label>'.
									'</td>'.
									'<td class="mw-input" >'.
										'<input type="text" id="imageurl" size="20" name="url" />'.
									'</td>'.				
								'</tr>'.
								'<tr>'.
									'<td>'.
									'</td>'.
									'<td class="mw-submit">'.
										'<input type="submit" value="'.wfMsg('dash-lcts-submit').'" />'.
									'</td>'.			
								'</tr>'.																	
							'</tbody>'.
						'</table>'.	
					'</fieldset>'.
					'<fieldset>'.
						'<legend>'.
							wfMsg('dash-lcts-editdelete').
						'</legend>'.
						'<table class="cvext-tbl-res"><tbody>'.
							'<tr class="cvext-res-head">'.
								'<td>'.wfMsg('dash-lcts-roomno').'</td>'.
								'<td>'.wfMsg('dash-lcts-description').'</td>'.
								'<td>'.wfMsg('dash-lcts-url').'</td>'.
							'</tr>'.
							$this->getLocations().
						'</tbody></table>'.							
					'</fieldset>'.															
				'</fieldset>'.																																															
				Xml::closeElement('div').
				Xml::closeElement('form');
			return $html;											
																						
									
		
	}
	private function getPageTypes()
	{	
		$html = '';
		$pages = $this->conference->getPages(); 
		$exists = false;
		foreach ($this->pageTypes as $page)
		{
			foreach ($pages as $pg)
			{
				if($pg->getType()==$page)
				{
					$exists = true;
				}
			}
			
			$html.= '<tr class="cvext-res"><td>'.$page.'</td>'.
			'<td>'.($exists ? '<span class="absent">'.wfMsg('dash-create').'</span> | '.
			'<a href="#edit">'.wfMsg('dash-edit').'</a>'.
			' | '.
			'<a href="#delete">'.wfMsg('dash-delete').'</a>' : '<a class="page" href="#add">'.wfMsg('dash-create').'</a> | '.
			'<span class="absent">'.wfMsg('dash-edit').'</span> | <span class="absent">'.wfMsg('dash-delete').'</span>').'</td></tr>';
		}	
		return $html;	
	}
	private function getOrganizers()
	{
		$html = '';
		$organizers = $this->conference->getOrganizers();
		foreach ($organizers as $organizer)
		{
			$userId = $organizer->getUserId();
			$user = User::newFromId($userId);
			$userPage = $user->getUserPage();
			$classRed = true;
			if($userPage->exists())
			{
				$classRed = false;
				$url = $userPage->getFullURL();
			}
			
			$html.= '<tr class="cvext-res">'.
						'<td>'.
							'<a href="'. $url .'"' . $classRed ? 'class="new" >' : ' >' .$user->getName() . '</a>'.
						'</td>'.
						'<td>'.
							$organizer->getCategory().
						'</td>'.
						'<td>'.
							$organizer->getPost().
						'</td>'.
						'<td>'.
							'<a href="#edit">'.wfMsg('dash-edit').'</a> | <a href="#delete">'.wfMsg('dash-delete').'</a>'.
						'</td>'.
					'</tr>';						
		}
		return $html;
	}
	private function getEvents()
	{
		$events = $this->conference->getEvents();
		$html='';
		foreach ($events as $event)
		{
			$html.='<tr class="cvext-res">'.
						'<td>'.
							$event->getTopic().
						'</td>'.
						'<td>'.
							$event->getStartTime().' hrs'.
						'</td>'.
						'<td>'.
							$event->getEndTime().' hrs'.
						'</td>'.
						'<td>'.
							$this->printDate($this->parseDate($event->getDay())).
						'</td>'.
						'<td>'.
							$event->getGroup().
						'</td>'.
						'<td>'.
							'<a href="#">'.wfMsg('dash-edit').'</a> | <a href="#">'.wfMsg('dash-delete').'</a>'.
						'</td>'.										
					'</tr>';
		}
		return $html;
	}
	private function getLocations()
	{
		$html ='';
		foreach ($this->locations as $location)
		{
			$html.= '<tr class="cvext-res">'.
						'<td>'.
							$location->getRoomNo().
						'</td>'.
						'<td>'.
							$location->getDescription().
						'</td>'.
						'<td>'.
							$location->getImageUrl().
						'</td>'.
						'<td>'.
							'<a href="#">'.wfMsg('dash-edit').'</a> | <a href="#">'.wfMsg('dash-delete').'</a>'.
						'</td>'.						
					'</tr>';
		}
		return $html;
	}
	private function getAccounts()
	{
		$html ='';
		$accounts = $this->conference->getAccounts();
		foreach ($accounts as $account)
		{
			$userId = $account->getUserId();
			$user = User::newFromId($userId);
			$userPage = $user->getUserPage();
			$classRed = true;
			$url = Title::makeTitle(NS_USER, $user->getName());
			if($userPage->exists())
			{
				$classRed = false;
				$url = $userPage->getFullURL();
			}
			$passportInfo = $account->getPassportInfo();
			$registrations = $account->getRegistrations();
			foreach ($registrations as $registration)
			{
				if($registration['conf']==$this->conference->getId())
				{
					$registrationUsed = $registration['registration'];
				}
			}
			
			$html.= '<tr class="cvext-res">'.
						'<td'.
							'<a href="' . $url . '"' . $classRed ? ' class="new" >' : ' >' .$user->getName().'</a>'.
							'<div>'.
								'<table>'.
									'<tbody>'.
										'<tr>'.
											'<td>'.
												wfMsg('dash-accts-generalinfo').
											'</td>'.
										'</tr>'.
										'<tr>'.
											'<td>'.
												'<div>'.
													'<table>'.
														'<tbody>'.
															'<tr>'.
																'<td>'.
																	wfMsg('dash-accts-gender').' : '.$account->getGender().
																'</td>'.
															'</tr>'.
															'<tr>'.
																'<td>'.
																	wfMsg('dash-accts-firstname').' : '.$account->getFirstName().
																'</td>'.	
															'</tr>'.
															'<tr>'.
																'<td>'.
																	wfMsg('dash-accts-lastname').' : '.$accounts->getLastName().
																'</td>'.
															'</tr>'.								
														'</tbody>'.
													'</table>'.
												'</div>'.
											'</td>'.	
										'</tr>'.				
									'</tbody>'.
								'</table>'.
								'<table>'.
									'<tbody>'.
										'<tr>'.
											'<td>'.
												wfMsg('dash-accts-passportinfo').
											'</td>'.
										'</tr>'.
										'<tr>'.
											'<td>'.
												'<div>'.
													'<table>'.
														'<tbody>'.
															'<tr>'.
																'<td>'.
																	wfMsg('dash-accts-pno').' : '.$passportInfo->getPassportNo().
																'</td>'.
															'</tr>'.
															'<tr>'.
																'<td>'.
																	wfMsg('dash-accts-iby').' : '.$passportInfo->getIssuedBy().
																'</td>'.
															'</tr>'.
															'<tr>'.
																'<td>'.
																	wfMsg('dash-accts-vu').' : '.$this->printDate($this->parseDate($passportInfo->getValidUntil())).
																'</td>'.
															'</tr>'.
															'<tr>'.
																'<td>'.
																	wfMsg('dash-accts-place').' : '.$passportInfo->getPlace().
																'</td>'.
															'</tr>'.
															'<tr>'.
																'<td>'.
																	wfMsg('dash-accts-dob').' : '.$this->printDate($this->parseDate($passportInfo->getDOB())).
																'</td>'.
															'</tr>'.
															'<tr>'.
																'<td>'.
																	wfMsg('dash-accts-country').' : '.$passportInfo->getCountry().
																'</td>'.
															'</tr>'.										
														'</tbody>'.
													'</table>'.
												'</div>'.
											'</td>'.
										'</tr>'.										
									'</tbody>'.
								'</table>'.
								'<table>'.
									'<tbody>'.
										'<tr>'.
											'<td>'.
												wfMsg('dash-accts-reginfo').
											'</td>'.
										'</tr>'.
										'<tr>'.
											'<td>'.
												'<div>'.
													'<table>'.
														'<tbody>'.
															'<tr>'.
																'<td>'.
																	wfMsg('dash-accts-regtype').' : '.$registrationUsed->getType().
																'</td>'.
															'</tr>'.
															'<tr>'.
																'<td>'.
																	wfMsg('dash-accts-regdiet').' : '.$registrationUsed->getDietaryRestr().
																'</td>'.
															'</tr>'.
															'<tr>'.
																'<td>'.
																	wfMsg('dash-accts-regotherdiet').' : '.$registrationUsed->getOtherDietOpts().
																'</td>'.
															'</tr>'.
															'<tr>'.
																'<td>'.
																	wfMsg('dash-accts-regother').' : '.$registrationUsed->getOtherOpts().
																'</td>'.
															'</tr>'.
															'<tr>'.
																'<td>'.
																	wfMsg('dash-accts-regbadge').' : '.$registrationUsed->getBadgeInfo().
																'</td>'.
															'</tr>'.
															'<tr>'.
																'<td>'.
																	wfMsg('dash-accts-regevents').' : '.$this->getEventLinks($registrationUsed).
																'</td>'.
															'</tr>'.										
														'</tbody>'.
													'</table>'.
												'</div>'.
											'</td>'.
										'</tr>'.		
									'</tbody>'.								
								'</table>'.																		
							'</div>'.
						'</td>'.
					'</tr>';													
		}
		return $html;
	}
	private function parseDate($date)
	{
		//returns an associative array with day, date, month , year
		//like $arr['day'], $arr['date'], $arr['month'], $arr['year']
		$monthDay = substr($date,0,2);
		if(substr($monthDay,0,1)=='0')
		{
			$monthDay = substr($monthDay, 1, 1);
		}
		$month = substr($date, 2, 2);
		if(substr($month,0,1)=='0')
		{
			$month = substr($month, 1, 1);
		}
		$year = substr($date, 4,4);
		$parsedDate['day']=date('l',mktime(0,0,0,$month,$monthDay,$year));
		$parsedDate['date']=$monthDay;
		$parsedDate['month']=$this->getMonth($month);
		$parsedDate['year']=$year;
		return $parsedDate;		
	}
	private function getMonth($index)
	{
		
		$index = $index - 1;
		$months= array(
		'Jan',
		'Feb',
		'Mar',
		'Apr',
		'May',
		'Jun',
		'Jul',
		'Aug',
		'Sep',
		'Oct',
		'Nov',
		'Dec');
		return $months[$index];
	}
	private function getEventLinks($registration)
	{
		$html ='';
		$events = $registration->getEvents();
		foreach ($events as $event)
		{
			$eventId = $event->getEventId();
			$title = Title::newFromID($eventId);
			$url = $title->getFullURL();
			$html.='<a href="'.$url.'">'.$event->getTopic().'</a> ';
		}
		return $html;
	}
	private function getAuthors()
	{
		$html='';
		$authors = $this->conference->getAuthors(); 
		//in a dashboard we will only display submissions for the given conference
		foreach ($authors as $author)
		{
			$submissions = $author->getSubmissions();
			$key ='conf-'.$this->conference->getId();
			$actualSubmissions = $submissions[$key]['submissions'];
			$userId = $author->getUserId();
			$user = User::newFromId($userId);
			$userPage = $user->getUserPage();
			$classRed = true;
			$url = Title::makeTitle(NS_USER, $user->getName());
			if($userPage->exists())
			{
				$classRed = false;
				$url = $userPage->getFullURL();
			}
			$html.= '<tr class="cvext-res">'.
					'<td>'.
						'<a href="' .$url .'"'.( $classRed ? ' class="new" >' : ' >' ). $user->getName() . '</a>'.
						'<div>'.
							'<table>'.
								'<tbody>'.
									'<tr>'.
										'<td>'.
											wfMsg('dash-athrs-geninfo').
										'</td>'.
									'</tr>'.
									'<tr>'.
										'<td>'.
											'<div>'.
												'<table>'.
													'<tbody>'.
														'<tr>'.
															'<td>'.
																wfMsg('dash-athrs-country').' : '.$author->getCountry().
															'</td>'.
														'</tr>'.
														'<tr>'.
															'<td>'.
																wfMsg('dash-athrs-affiliation').' : '.$author->getAffiliation().
															'</td>'.
														'</tr>'.
														'<tr>'.
															'<td>'.
																wfMsg('dash-athrs-url').' : '.$author->getBlogUrl().
															'</td>'.
														'</tr>'.				
													'</tbody>'.
												'</table>'.
											'</div>'.
										'</td>'.	
									'</tr>'.				
								'</tbody>'.
							'</table>'.
							'<table>'.
								'<tbody>'.
									'<tr>'.
										'<td>'.
											wfMsg('dash-athrs-submissions').
										'</td>'.
									'</tr>'.
									'<tr>'.
										'<td>'.
											'</div>'.
												'<table>'.
													'<tbody>'.
														$this->getSubmissionUrls($actualSubmissions).
													'</tbody>'.
												'</table>'.
											'</div>'.
										'</td>'.	
									'</tr>'.				
								'</tbody>'.
							'</table>'.									
						'</div>'.
					'</td>'.
				'</tr>';
			
		}
		return $html;
		
	}
	private function getSubmissionUrls($submissions)
	{
		$html ='';
		foreach ($submissions as $submission)
		{
			$subId = $submission->getId();
			$title = Title::newFromID($subId);
			$url = $title->getFullURL();
			$html.= '<tr>'.
						'<td>'.
							'<a href="'.$url.'" >'.$submission->getTitle().'</a>'.
						'</td>'.
					'</tr>';
		}
		return $html;
	}
	private function getGroupsForEvents()
	{
		$html = '<select id="group" name="group">';
		foreach ($this->preloadedGroups as $group)
		{
			$html.= '<option>'.
						$group.
					'</option>';		
		}
		$html.= '</select>';
		return $html;	
	}
	private function printDate($date)
	{
		return $date['month'].' '.$date['date'].', '.$date['month'];
	}
	private function getDaysForEvents()
	{
		$startDate = $this->parseDate($this->conference->getStartDate());
		$endDate = $this->parseDate($this->conference->getEndDate());
		//calculate the days between startdate and enddate
		//I assume we have it in an array called $days['date'], $days['month'], $days['year']
		$html ='<select id="day" name="day" >';
		foreach ($days as $day)
		{
			$html.= '<option>'.
						$day['month'].' '.$day['date'].', '.$day['year'].
					'</option>';
		}
		$html.= '</select>';
		return $html;
	}
	private function getLocationsForEvents()
	{
		$this->locations = Conference::getLocations($this->conference->getId());
		$html ='<select id="location name="location" >';
		foreach ($this->locations as $location)
		{
			$html.= '<option>'.
						$location->getRoomNo().
					'</option>';
		}
		$html.= '</select>';
		return $html;
	}
}