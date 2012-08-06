<?php
class ConferenceHooks
{
	public static function onParserFirstCallInit(Parser &$parser)
	{
		$parser->setHook('conference','Conference::render');
		$parser->setHook('page','ConferencePage::render');
		$parser->setHook('author','ConferenceAuthor::render');
		$parser->setHook('author-sub','ConferenceAuthor::renderSub');
		$parser->setHook('submission','AuthorSubmission::render');
		$parser->setHook('event','ConferenceEvent::render');
		$parser->setHook('organizer','ConferenceOrganizer::render');
		$parser->setHook('applicant','ConferenceApplicant::render');
		$parser->setHook('passport-info','ConferencePassportInfo::render');
		$parser->setHook('registration','ConferenceRegistration::render');
		$parser->setHook('location','EventLocation::render');
		$parser->setHook('account', 'ConferenceAccount::render');
		$parser->setHook('account-sub','ConferenceAccount::renderSub');
		$parser->setHook('registration-event', 'ConferenceRegistration::renderSub');
		$parser->setFunctionHook('cvext-page-link', 'ConferenceHooks::handleParserLinkFunction');
		return true;
	}
	public static function handleParserLinkFunction($parser,$arg1, $arg2, $arg3)
	{
		
		$arg1 = $arg1 ? $arg1 : 'article';
		if(!$arg2)
		{
			return '';
		}
		$parser->disableCache();
		$pageType = $arg2;
		$alternateText = $arg3 ? $arg3 : '';
		
		# load the conference details
		$pageTitle = $parser->getTitle();
		$page = ConferencePage::loadFromId($pageTitle->getArticleID());
		$conferenceId = $page->getConferenceId();
		$conferenceTitle = Title::newFromID($conferenceId)->getDBkey();
		
		# generate the link for the page
		if($arg1 == 'article')
		{
			$pageLink = Title::newFromText($conferenceTitle.'/pages/'.$pageType)->getPrefixedDBKey();
		} elseif ($arg1 == 'special') {
			$pageLink = SpecialPage::getTitleFor('AuthorRegister', $conferenceTitle)->getFullURL('action=createview');
		}
		
		$output = "<span class=\"plainlinks\">[".$pageLink." ".$alternateText."]</span>";
		return array($output, 'noparse' => false);
		
	}
	public static function beforeTemplateDisplay(OutputPage &$out, Skin &$skin)
	{
		global $wgExtensionAssetsPath;
		/* add a check for template schedule pages */
		$out->addExtensionStyle($wgExtensionAssetsPath.'/ConventionExtension/resources/conference.schedule/conference.schedule.css');
		return true;
	}
	public static function modifySidebar($skin, &$bar)
	{
		global $wgModifySidebar;
		if($wgModifySidebar)
		{
			
			$title = $skin->getTitle();
			$article = Article::newFromTitle($title, $skin->getContext());
			$pageProps = $article->getParserOutput()->getProperties();
			$propNames = array_keys($pageProps);
			/* check what sort of page is it */
			$properties = array(
					'cvext-type',
					'cvext-page-conf',
					'cvext-organizer-conf',
					'cvext-location-conf',
					'cvext-account-conf',
					'cvext-author-conf',
					'cvext-submission-author',
					'cvext-registration-account',
					'cvext-registration-parent');
			if($propNames && count($propNames))
			{
				foreach ($propNames as $propName)
				{
					if($propName == 'cvext-type' && $pageProps['cvext-type'] == 'conference')
					{
							
						$conferenceTitle = $title->getDBKey();
						$conferenceId = $title->getArticleID();
						break;
							
					} elseif (($key = array_search($propName, $properties)) !== false && $key > 0 && $key < 6) {
							
						$conferenceId = $pageProps[$propName];
						break;
							
					} elseif (in_array($propName, array_slice($properties, 7))) {
							
						/* registration or registration sub page */
						if($propName == 'cvext-registration-account')
						{
				
							$subAccountId = $pageProps[$propName];
				
						} else {
				
							$parentRegistrationId = $pageProps[$propName];
							$subAccountId = ConferenceAccountUtils::getConferenceIdFromParentReg($parentRegistrationId);
				
						}
						$conferenceId = ConferenceAccountUtils::getConferenceTitleFromSubAccount($subAccountId);
						break;
							
					} elseif ($propName == 'cvext-submission-author') {
							
						$subAuthorId = $pageProps[$propName];
						$conferenceId = ConferenceAuthorUtils::getConferenceTitleFromSubAuthor($subAuthorId);
						break;
							
					} else {
						return true;
					}
				}
				if(!isset($conferenceTitle))
				{
					$conferenceTitle = ConferenceUtils::getTitle($conferenceId);
				}	
				$conference = ConferenceUtils::loadFromConferenceTag($conferenceId);
				$baseTitleText = $conferenceTitle.'/pages/';
				$venue = $conference['venue'];
				$venueArray = explode(',', $venue);
				$city = $venueArray[1];
				$toolbox = $bar['TOOLBOX'];
				$search = $bar['SEARCH'];
				$languages = $bar['LANGUAGES'];
				unset($bar['SEARCH']);
				unset($bar['LANGUAGES']);
				unset($bar['TOOLBOX']);
				/* add the local information portal*/
				$pages = array('localinfo'=>array(array('msg'=>'addressbook','name'=>'Address Book'),
									array('msg'=>'aboutcity','name'=>'About City'),
									array('msg'=>'venue','name'=>'Venue'),
									array('msg'=>'accommodation','name'=>'Accommodation'),
									array('msg'=>'transportation','name'=>'Transportation'),
									array('msg'=>'touristopts','name'=>'Tourist Options'),
									array('msg'=>'visas','name'=>'Visas')),
						'questions'=>array(array('msg'=>'faq','name'=>'FAQ'),
									array('msg'=>'infodesk','name'=>'Information Desk'),
									array('msg'=>'contactpage','name'=>'Contact Page')),
						'aboutus'=>array(array('msg'=>'attendees','name'=>'Attendees'),
									array('msg'=>'volunteers','name'=>'Volunteers'),
									array('msg'=>'orgteam','name'=>'Organizing Team')));
				foreach ($pages as $category => $links)
				{
					$sidebar = array();
					foreach($links as $link)
					{
						$name = $link['name'];
						$title = Title::newFromText($baseTitleText.$name);
						$msg = $name == 'About City' ? wfMsg('cvext-'.$link['msg'], $city) : wfMsg('cvext-'.$link['msg']); 
						if($title->exists())
						{
							$sidebar[] = array('text'=>$msg,
									'href'=>$title->getFullURL(),
									'id'=>'t-'.$link['msg'],
									'active'=>false);
						}
					}
					if(count($sidebar))
					{
						$bar['cvext-'.$category] = $sidebar;
					}
				}
				/*$localInfoArray = array();
				 $localInfoArray[] = array('text'	=> wfMsg('cvext-addressbook'),
				 		'href'		=> Title::newFromText($baseTitleText.'addressbook')->getFullURL(),
				 		'id' 		=> 't-addressbook',
				 		'active'	=> false);
				$localInfoArray[] = array('text'	=> wfMsg('cvext-aboutcity', $city),
						'href'		=> Title::newFromText($baseTitleText.'aboutcity')->getFullURL(),
						'id'		=> 't-aboutcity',
						'active'	=> false);
				$localInfoArray[] = array('text'	=> wfMsg('cvext-venue'),
						'href'		=> $href,
						'id'		=> 't-venue',
						'active'	=> false);
				$localInfoArray[] = array('text'	=> wfMsg('cvext-accommodation'),
						'href'		=> $href,
						'id'		=> 't-accomdtn',
						'active'	=> false);
				$localInfoArray[] = array('text'	=> wfMsg('cvext-transportation'),
						'href'		=> $href,
						'id'		=> 't-transportation',
						'active'	=> false);
				$localInfoArray[] = array('text'	=> wfMsg('cvext-touristopts'),
						'href'		=> $href,
						'id'		=> 't-touristopts',
						'active'	=> false);
				$localInfoArray[] = array('text'	=> wfMsg('cvext-visas'),
						'href'		=> $href,
						'id'		=> 't-visas',
						'active'	=> false);
				$bar['cvext-localinfo'] = $localInfoArray;
					
				/* questions portal */
				/*$questionsArray = array();
				 $questionsArray[] = array('text'	=> wfMsg('cvext-faq'),
				 		'href'		=> $href,
				 		'id'		=> 't-faq',
				 		'active'	=> false);
				$questionsArray[] = array('text'	=> wfMsg('cvext-infodesk'),
						'href'		=> $href,
						'id'		=> 't-infodesk',
						'active'	=> false);
				$questionsArray[] = array('text'	=> wfMsg('cvext-contactpage'),
						'href'		=> $href,
						'id'		=> 't-contact-page',
						'active'	=> false);
				$bar['cvext-questions'] = $questionsArray;
					
				/* About Us portal */
				/*$abutUsArray = array();
				 $aboutUsArray[] = array('text'		=> wfMsg('cvext-attendees'),
				 		'href'		=> $href,
				 		'id'		=> 't-attendees',
				 		'active'	=> false);
				$aboutUsArray[] = array('text'		=> wfMsg('cvext-volunteers'),
						'href'		=> $href,
						'id'		=> 't-volunteers',
						'active'	=> false);
				$aboutUsArray[] = array('text'		=> wfMsg('cvext-orgteam'),
						'href'		=> $href,
						'id'		=> 't-orgteam',
						'active'	=> false);
				$bar['cvext-aboutus'] = $aboutUsArray;*/
				$bar['SEARCH'] = $search;
				$bar['LANGUAGES'] = $languages;
				$bar['TOOLBOX'] = $toolbox;
			}
			
		}
		return true;
	}
	public static function assignMagicWords(&$parser, &$cache, &$magicWordId, &$ret)
	{
		$title = $parser->getTitle();
		$pageId = $title->getArticleID();
		$page = ConferencePage::loadFromId($pageId);
		$conferenceId = $page->getConferenceId();
		$conferenceTag = ConferenceUtils::loadFromConferenceTag($conferenceId);
		$venue = $conferenceTag['venue'];
		$venueArray = explode(',', $venue);
		$place = $venueArray[0];
		$city = $venueArray[1];
		$country = $venueArray[2];
		switch ($magicWordId){
			case 'conf-name':
				$ret = $conferenceTag['title'];
				break;
			case 'conf-venue':
				$ret = $venue;
				break;
			case 'conf-city':
				$ret = $city;
				break;
			case 'conf-place':
				$ret = $place;
				break;
			case 'conf-country':
				$ret = $country;
				break;
			case 'conf-capacity':
				$ret = $conferenceTag['capacity'];
				break;
			case 'conf-description':
				$ret = $conferenceTag['description'];
				break;
			default :
				break;							
		}
		return true;
	}
	public static function declareMagicIds(&$customVariableIds)
	{
		$customVariableIds[] = 'conf-name';
		$customVariableIds[] = 'conf-venue';
		$customVariableIds[] = 'conf-city';
		$customVariableIds[] = 'conf-place';
		$customVariableIds[] = 'conf-country';
		$customVariableIds[] = 'conf-capacity';
		$customVariableIds[] = 'conf-description';
		return true;
	}
}