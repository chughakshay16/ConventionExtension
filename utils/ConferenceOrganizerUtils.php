<?php
class ConferenceOrganizerUtils
{
	public static function isOrganizerFromConference( $uid, $cid )
	{
		//get the page_id
		$dbr = wfGetDB( DB_SLAVE );
		$result = $dbr->select( "page_props",
		"*",
		array( 'pp_propname' => 'cvext-organizer-user', 'pp_value' => $uid ),
		__METHOD__,
		array() );
		#the user-id is already present as an organizer, just need to check if its for the same conference or not
		foreach ( $result as $row )
		{
			$resultRow = $dbr->selectRow( "page_props",
			array( 'pp_value' ),
			array( "pp_page" => $row->pp_page, 'pp_propname' => 'cvext-organizer-conf' ),
			__METHOD__,
			array() );
			if ( $resultRow->pp_value == $cid )
			{
				return true;
			}
		}
		return false;
		
		
	}
	public static function getNewCategoryIndex( $category, $sections )
	{
		global $wgCategoryPostTree;
		$catsWithPriority = array();
		foreach ( $wgCategoryPostTree as $val )
		{
			$catsWithPriority[] = $val['category'];
		}
		$indexObj = array();
		if ( count( $sections ) )
		{
			foreach ( $sections as $index => $value )
			{
				if ( $value['toclevel'] == 1 )
				{
					$catValue = $value['line'];
					$catValueIndex = (( $cvi = array_search( $catValue, $catsWithPriority )) !== false ) ? $cvi : count( $catsWithPriority );
					$catIndex = (( $ci = array_search( $category, $catsWithPriority )) !== false ) ? $ci : count( $catsWithPriority );
					if ( $catValueIndex > $catIndex || $catValueIndex == $catIndex )
					{
						$indexObj['position'] = 'before';
						$indexObj['index'] = $sections[$index]['index'];
						return $indexObj;
					} elseif ( $catValueIndex < $catIndex ) {
						continue;
					}
						
				}
			}
			$indexObj['position'] = 'after';
			$indexObj['index'] = $sections[$index]['index'];
			return $indexObj;
		}
		$indexObj['index'] = 1;
		return $indexObj;
	}	
	public static function isNewCategory( $sections, $category )
	{
		if ( $sections && count( $sections ) )
		{
			foreach ( $sections as $value )
			{
				if ( $value['line'] == $category )
				{
					return false;
				}
			}
		}
		return true;
			
	}
	/*
	 * returns the index of the section to which new post section will be prepended or appended
	 * this function rides on a very big assumption that category heading will atleast have one sub-heading(post), which in our case will always be true
	 */
	public static function getNewPostIndex( $sections, $post, $category )
	{
		$indexObj = array();
		global $wgCategoryPostTree;
		foreach ( $wgCategoryPostTree as $index => $catpost )
		{
			if ( $catpost['category'] == $category )
			{
				$posts = isset( $catpost['posts'] ) && is_array( $catpost['posts'] ) ? $catpost['posts'] : array();
			}
		}
		$posts = isset( $posts ) ? $posts : array();
		foreach ( $sections as $index => $value )
		{
			if ( $value['line'] == $category )
			{
				$markerIndex = $index;
				continue;
			} elseif ( isset( $markerIndex ) ) {
				
				$postValue = $value['line'];
				$postValueIndex = array_search( $postValue, $posts );
				$postIndex = ( $pi = array_search( $post, $posts ) !== false ) ? $pi : count( $posts );
				if ( $postValueIndex === false || $postValueIndex > $postIndex || $postValueIndex == $postIndex )
				{
					$indexObj['position'] = 'before';
					$indexObj['index'] = $sections[$index]['index'];
					return $indexObj;
				} elseif ( $postValueIndex < $postIndex ) {
					if ( isset( $sections[$index + 1] ) && $sections[$index + 1]['toclevel'] == 1 )
					{
						$indexObj['position'] = 'after';
						$indexObj['index'] = $sections[$index]['index'];
						return $indexObj;
					}
					continue;
				}
			}
		}
	}
	/*
	 * we only call this function during an edit phase meaning the post is bound to be present in the sections array
	 */
	public static function getPostIndexForUser( $post, $category, $sections, $user, $content )
	{
		global $cvextParser;
		if ( !$cvextParser->getOptions() )
		{
			$popts = new ParserOptions;
			$popts->enableLimitReport();
			$popts->setTidy(true);
			$cvextParser->startExternalParse( $orgsPageTitle, $popts , Parser::OT_PLAIN );
		}
		$indexObj = array();
		foreach ( $sections as $index => $value )
		{
			if ( $value['line'] == $category )
			{
				$markerIndex = $index;
				continue;
			} elseif ( isset( $markerIndex ) ) {
				if ( $value['line'] == $post )
				{
					$postText = $cvextParser->getSection( $content, $sections[$index]['index'] );
					preg_match_all( "/\[\[User:(.*)\|(.*)\]\]/", $postText, $matches );
					if ( $matches[1][0] == $user->getUserPage()->getDBKey() )
					{
						if ( ( isset( $sections[$index + 1] ) && $sections[$index + 1]['toclevel'] == 1 && 
								$markerIndex == $index - 1 ) || ( $markerIndex == $index - 1 && count( $sections ) == $index + 1 ) )
						{
							$indexObj['last'] = true;
						}
						$indexObj['index'] = $sections[$index]['index'];
						return $indexObj;
					}
				}
				continue;
			}
		}
		return false;
	}
	public static function getCategoryIndex( $category, $sections )
	{
		if ( $sections && count( $sections ) )
		{
			foreach ( $sections as $index => $value )
			{
				if ( $value['line'] == $category )
				{
					return $sections[$index]['index'];
				}
			}
		}
		return false;
	}
	public static function getPrevNextIndex( $post, $category )
	{
		global $wgCategoryPostTree;
		foreach ( $wgCategoryPostTree as $index => $value )
		{
			if ( $value['category'] == $category )
			{
				foreach ( $value['posts'] as $postIndex => $postValue )
				{
					if ( $post == $postValue )
					{
						$prevIndex = $postIndex != 0 ? $postIndex - 1 : 0;
						$nextIndex = $postIndex + 1 == count( $value['posts'] ) ? $postIndex : $postIndex + 1;
						return array( 'prev' => $value[$prevIndex], 'next' => $value[$nextIndex] );
					}
				}
			}
		}
		return false;
	}
}		