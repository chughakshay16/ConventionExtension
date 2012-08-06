<?php
/**
 *
 * @todo complete mustValidateInputs()
 * @author chughakshay16
 *
 */
class ApiConferenceOrganizerAdd extends ApiBase
{
	public function __construct( $main, $action )
	{
		parent::__construct( $main, $action );
	}
	public function execute()
	{
		global $cvextParser;
		$params = $this->extractRequestParams();
		$user = $this->getUser();
		$request = $this->getRequest();

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
		$username = $params['username'];
		$category = $params['category'];
		$post = $params['post'];

		$addedUser = User::newFromName( $username, true );
		if ( $addedUser->getId() == 0 )
		{
				
			$this->dieUsageMsg( array( 'nosuchuser', $params['username'] ) );
				
		} elseif ( $addedUser === false ) {
				
			$this->dieUsageMsg( array( 'invaliduser', $params['username'] ) );
				
		}
		$errors = $this->mustValidateInputs( $category, $post );
		if ( count( $errors ) )
		{
			//depending on the error
			//$this->dieUsageMsg(array('spamdetected',put the parameter due to which error was thrown))
		}
		$conferenceId = $sessionData['id'];
		$conferenceTitle = $sessionData['title'];
		$catpost = array( array( 'category' => $category, 'post' => $post ) );
		$organizer = ConferenceOrganizer::createFromScratch( $conferenceId, $addedUser->getId(), $catpost );
		$resultApi = $this->getResult();
		if ( $organizer && $organizer->getOrganizerId() )
		{
			
			/* modify the organizer page if it exists */
			$orgsPageTitle = Title::newFromText( $conferenceTitle . '/pages/Organizing Team' );
			if ( $orgsPageTitle->exists() )
			{
				/* parser settings */
				$popts = new ParserOptions;
				$popts->enableLimitReport();
				$popts->setTidy( true );
				$article = Article::newFromID( $orgsPageTitle->getArticleID() );
				$content = $article->fetchContent();
				$pst = $cvextParser->preSaveTransform( $content, $orgsPageTitle, $user, $popts, true );
				//$extParser->startExternalParse($orgsPageTitle, $popts , Parser::OT_PLAIN);
				$parserOutput = $cvextParser->parse( $pst, $orgsPageTitle, $popts );
				/*$parserOutput = $article->getParserOutput();*/
				$sections = $parserOutput->getSections();
				$isNewCat = ConferenceOrganizerUtils::isNewCategory( $sections, $category );
				if ( $isNewCat )
				{
					/* add new category section */
					$categoryIndex = ConferenceOrganizerUtils::getNewCategoryIndex( $category, $sections );
					$prevCategoryText = $cvextParser->getSection( $content, $categoryIndex['index'] );
					$newCatPostText = "==" . $category . "== \n";
					$newCatPostText .= $organizer->getPostWikiText( $addedUser, $post );
					if ( isset( $categoryIndex['position'] ) && $categoryIndex['position'] == 'after' )
					{
						$prevCategoryText .= "\n" . $newCatPostText;
					} else {
						$prevCategoryText = $newCatPostText . $prevCategoryText;
					}
					$newContent = isset( $categoryIndex['position'] ) ? $cvextParser->replaceSection( $content, $categoryIndex['index'], $prevCategoryText ) : $content . "\n" . $prevCategoryText;
			
				} else {
					/* modify the existing category section */
					/* one thing to note here, category heading will always have atleast one sub heading(post) */
					$postIndex = ConferenceOrganizerUtils::getNewPostIndex( $sections, $post, $category );
					$prevPostText = $cvextParser->getSection( $content, $postIndex['index'] );
					$newPostText = $organizer->getPostWikiText( $addedUser, $post );
					if ( $postIndex['position'] == 'before' )
					{
						$prevPostText = $newPostText . $prevPostText;
					} else {
						$prevPostText .= "\n" . $newPostText;
					}
					$newContent = $cvextParser->replaceSection( $content, $postIndex['index'], $prevPostText );
				}
					
				$orgsWikiPage = WikiPage::factory( $orgsPageTitle );
				$status = $orgsWikiPage->doEdit( $newContent, 'New organizer added in the Organizing Team page', EDIT_UPDATE );
				if ( !$status->value['revision'] )
				{
					/* error == rollback*/
				}
			}
			
			//$orgurl = Title::makeTitle(NS_MAIN, $conferenceId.'/organizers/'.$user->getName())->getFullURL();
			$result['done'] = true;
			$result['msg'] = 'The organizer was successfully added';
			$result['id'] = $organizer->getOrganizerId();
			$result['username'] = $addedUser->getName();
			$result['category'] = $category;
			$result['post'] = $post;
			$result['userpage'] = $user->getUserPage()->getFullURL();
			$class = '';
			if( !$user->getUserPage()->exists() )
			{
				$class = 'new';
			}
			$result['userpageclass'] = $class;
			$resultApi->addValue( null, $this->getModuleName(), $result );
		} else {
			$result['done'] = false;
			$result['msg'] = 'The organizer could not be added. Try again.';
			$resultApi->addValue( null, $this->getModuleName(), $result );
		}
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
				'username'	=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true),
				'category'	=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true),
				'post'		=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true)
		);
	}
	public function getParamDescription()
	{
		return array(
				'username'	=>'Username  of the organizer',
				'category'	=>'Category which the organizer belongs to',
				'post'		=>'The role which has been assigned to the organizer'
		);
	}
	public function getDescription()
	{
		return 'Add Organizer Details';
	}
	public function getPossibleErrors()
	{
		return array_merge( parent::getPossibleErrors(), array(
				array( 'mustbeloggedin', 'Wiki' ),
				array( 'badaccess-groups' ),
				array( 'missingparam', 'username' ),
				array( 'missingparam', 'category' ),
				array( 'missingparam', 'post' ),
				array( 'nosuchuser', 'username' ),
				array( 'invaliduser', 'username' ),
				array( 'createonly-exists' )
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