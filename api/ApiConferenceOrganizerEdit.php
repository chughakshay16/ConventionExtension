<?php
/**
 *
 * @todo complete mustValidateInputs()
 * @author chughakshay16
 *Add the settings for the request parameters in the getAllowedParams() and remove the condition blocks from execute() method
 */
class ApiConferenceOrganizerEdit extends ApiBase
{
	public function __construct( $main, $action )
	{
		parent::__construct( $main, $action );
	}
	public function execute()
	{
		global $cvextParser;
		$params = $this->extractRequestParams();
		$request = $this->getRequest();
		$resultApi = $this->getResult();
		$user = $this->getUser();
		//still need to decide on what messages one should choose while throwing these errors
		if ( !$user->isLoggedIn() )
		{
			$this->dieUsageMsg( array( 'mustbeloggedin', 'Wiki' ) );
		}
		
		$groups = $user->getGroups();
		if ( !in_array( 'sysop', $groups ) )
		{
			$this->dieUsageMsg( array( 'badaccess-groups' ) );
		}
		
		$sessionData = $request->getSessionData( 'conference' );
		if ( !$sessionData )
		{
			$this->dieUsage( 'No conference details were found in the session object for this user', 'noconfinsession' );
		}
		if ( isset( $params['username'] ) )
		{
				
			$username = $params['username'];
				
			//now check if one of the values category and post are passed, if not throw the error
			//Note : here '' or NULL means the same i.e dont change their value
			//here we wont
			/*if(!isset($params['category']))
			 {
			$this->dieUsageMsg(array('missingparam',$params['category']));
			} elseif (!isset($params['post'])) {
			$this->dieUsageMsg(array('missingparam',$params['post']));
			} else {
			$category = $params['category'];
			$post = $params['post'];
			}*/
			if ( $params['category'] && $params['post'] )
			{
				$category = $params['category'];
				$post = $params['post'];
				$categoryto = $params['categoryto'] ? $params['categoryto'] : $params['category'];
				$postto = $params['postto'] ? $params['postto'] : $params['post'];
			} else {
				//throw an error
				if ( !$params['category'] )
				{
					$this->dieUsageMsg( array( 'missingparam', $params['category'] ) );
				} else {
					$this->dieUsageMsg( array( 'missingparam', $params['post'] ) );
				}
			}

		} else {
				
			$this->dieUsageMsg( array( 'missingparam', $params['username'] ) );
				
		}

		//before we start to edit the page, make sure that a new (cat,post) is passed along
		if ( $category == $categoryto && $postto == $post )
		{
			$result['msg'] = 'No edit needed. Details passed were same as before';
			$result['done'] = true;
			$result['noedit'] = true;
			$resultApi->addValue( null, $this->getModuleName(), $result );
			return ;
		}
		$conferenceId = $sessionData['id'];
		$conferenceTitle = $sessionData['title'];
		$editedUser = User::newFromName( $username, true );
		if ( $editedUser->getId() == 0 )
		{
				
			$this->dieUsageMsg( array( 'nosuchuser', $params['username'] ) );
				
		} elseif ( $editedUser === false ) {
				
			$this->dieUsageMsg( array( 'invaliduser', $params['username'] ) );
				
		}

		$isOrganizer = ConferenceOrganizerUtils::isOrganizerFromConference( $editedUser->getId(), $conferenceId );
		if( !isOrganizer )
		{
			$this->dieUsageMsg( array( 'nocreate-missing' ) );
		}

		$errors = $this->mustValidateInputs( $category, $post );
		if ( count( $errors ) )
		{
				
			//depending on the error
			//$this->dieUsageMsg(array('spamdetected',put the parameter due to which error was thrown))
				
		} else {
				
			$catpostOld = array( array( 'category' => $category, 'post' => $post ) );
			$catpostNew = array( array( 'category' => $categoryto, 'post' => $postto ) );
			$result = ConferenceOrganizer::performEdit( $conferenceId, $username, $catpostNew, $catpostOld );
			if ( $result['flag'] == Conference::SUCCESS_CODE )
			{
				/* modify the organizer page if it exists */
				$orgsPageTitle = Title::newFromText( $conferenceTitle . '/pages/Organizing Team' );
				if ( $orgsPageTitle->exists() )
				{
					/* parser settings */
					$popts = new ParserOptions;
					$popts->enableLimitReport();
					$popts->setTidy( true );
					//$wgParser->startExternalParse($orgsPageTitle, $popts , Parser::OT_PLAIN);
					$article = Article::newFromID( $orgsPageTitle->getArticleID() );
					$content = $article->fetchContent();
					/*$parserOutput = $article->getParserOutput();
					$sections = $parserOutput->getSections();*/
					/*$pst = $cvextParser->preSaveTransform($content, $orgsPageTitle, $user, $popts, true);
					$parserOutput = $cvextParser->parse($pst, $orgsPageTitle, $popts);*/
					$parserOutput = $this->loadParser( $content, $orgsPageTitle, $user, $popts, true );
					$sections = $parserOutput->getSections();
					$isNewCat = ConferenceOrganizerUtils::isNewCategory( $sections, $categoryto );
					$organizer = ConferenceOrganizer::newFromUser( $editedUser, $conferenceTitle );
					$postIndex = ConferenceOrganizerUtils::getPostIndexForUser( $post, $category, $sections, $editedUser, $content );
					if ( !postIndex )
					{
						//error - debug this (ideally it should never happen)
						$resultApi->addValue( null, $this->getModuleName(), $result );
					}
					
					$newContent = $cvextParser->replaceSection( $content, $postIndex['index'], '' );
					/* although preSaveTransform() wont be needed in an ideal situation, but used here just in case someone
					 * edits the page in an unexpected manner */
					/*$pst = $cvextParser->preSaveTransform($newContent, $orgsPageTitle, $user, $popts, true);
					$parserOutput = $cvextParser->parse($pst, $orgsPageTitle, $popts);*/
					$parserOutput = $this->loadParser( $newContent, $orgsPageTitle, $user, $popts, true );
					$modifiedSections = $parserOutput->getSections();

					if ( $isNewCat )
					{
						/* since we have already removed the post section above, we should check if it was the last post section
						 * and if it was then we should delete the category section as well */
						if ( isset( $postIndex['last'] ) && $postIndex['last'] )
						{
							$oldCatIndex = ConferenceOrganizerUtils::getCategoryIndex( $category, $modifiedSections );
							$newContent = $cvextParser->replaceSection( $newContent, $oldCatIndex, '' );
							/*$pst = $cvextParser->preSaveTransform($newContent, $orgsPageTitle, $user, $popts, true);
							 $parserOutput = $cvextParser->parse($pst, $orgsPageTitle, $popts);*/
							$parserOutput = $this->loadParser( $newContent, $orgsPageTitle, $user, $popts, true );
							$modifiedSections = $parserOutput->getSections();
						}
						$catIndex = ConferenceOrganizerUtils::getNewCategoryIndex( $categoryto, $modifiedSections );
						$prevCatText = $cvextParser->getSection( $newContent, $catIndex['index'] );
						$newCatPostText = "==" . $categoryto . "==\n";
						$newCatPostText .= $organizer->getPostWikiText( $editedUser, $postto );
						if ( $catIndex['position'] == 'before' )
						{
							$toInsert = $newCatPostText . $prevCatText;
						} else {
							$toInsert = $prevCatText . "\n" . $newCatPostText;
						}
						$latestContent = $cvextParser->replaceSection( $newContent, $catIndex['index'], $toInsert );
						
					} else {
						/* category already exists, remove the old post section and add a new one */
						$insertPostAt = ConferenceOrganizerUtils::getNewPostIndex( $modifiedSections, $postto, $categoryto );
						$prevPostText = $cvextParser->getSection( $newContent, $insertPostAt['index'] );
						$newPostText = $organizer->getPostWikiText( $editedUser, $postto );
						if ( $insertPostAt['position'] == 'before' )
						{
							$toInsert = $newPostText . $prevPostText;
						} else {
							$toInsert = $prevPostText . "\n" . $newPostText;
						}
						$latestContent = $cvextParser->replaceSection( $newContent, $insertPostAt['index'], $toInsert );
					}
					$orgWikiPage = WikiPage::factory( $orgsPageTitle );
					$status = $orgWikiPage->doEdit( $latestContent, 'Organizer info has been edited in Organizing Team page', EDIT_UPDATE );
					if ( !$status->value['revision'] )
					{
						/* error ==  rollback */
					}				
				}
			}
			$resultApi->addValue( null, $this->getModuleName(), $result );
		}


	}
	private function loadParser($text, $title, $user, $popts, $clearState)
	{
		global $cvextParser;
		$pst = $cvextParser->preSaveTransform( $text, $title, $user, $popts, $clearState );
		$parserOutput = $cvextParser->parse( $pst, $title, $popts );
		return $parserOutput;
	}
	public function mustValidateInputs( $category, $post )
	{
		// dont throw any error for null values
	}
	public function isWriteMode()
	{
		return true;
	}
	public function mustBePosted()
	{
		return true;
	}
	public function getAllowedParams()
	{
		return array(
				'username'		=>null,
				'category'		=>null,
				'post'			=>null,
				'categoryto'	=>null,
				'postto'		=>null
		);
	}
	public function getParamDescription()
	{
		return array(
				'username'		=> 'Username  of the organizer',
				'category'		=> 'Category which the organizer belongs to',
				'post'			=> 'The role which has been assigned to the organizer',
				'categoryto'	=> 'New category for the organizer',
				'postto'		=> 'New post for the organizer'
		);
	}
	public function getDescription()
	{
		return 'Edit Organizer Details';
	}
	public function getPossibleErrors()
	{
		$user = $this->getUser();
		return array_merge( parent::getPossibleErrors(), array(
				array( 'mustbeloggedin', 'Wiki'),
				array( 'badaccess-groups' ),
				array( 'missingparam', 'category' ),
				array( 'missingparam', 'post' ),
				array( 'missingparam', 'username' ),
				array( 'nosuchuser', 'username' ),
				array( 'invaliduser', 'username' ),
				array( 'nocreate-missing' )
		));
	}
	public function getExamples()
	{

	}
	public function getVersion()
	{
		return __CLASS__ . ': $Id$';
	}
}