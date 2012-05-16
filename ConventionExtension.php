<?php
if ( !defined( 'MEDIAWIKI' ) ) { 
	if ( !defined( 'MEDIAWIKI' ) ) {
    	echo <<<EOT
To install the Convention extension, put the following line in your 
LocalSettings.php file: 
require_once( "\$IP/extensions/ConventionExtension/ConventionExtension.php" );
EOT;
    	exit( 1 );
	}
}

$wgExtensionCredits[ 'other' ][] = array(
	'path' => __FILE__,
	'name' => 'Convention Extension',
	'author' =>'Akshay Chugh', 
	'url' => 'https://www.mediawiki.org/wiki/Extension:Example', 
	'description' => 'An extension to convert a wiki into a conference management system',
	'version'  => 1.0,
);
$wgCurrentDir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
$wgAutoloadClasses['Conference']=$wgCurrentDir.'model/Conference.php';
$wgAutoloadClasses['ConferenceAuthor']=$wgCurrentDir.'model/ConferenceAuthor.php';
$wgAutoloadClasses['ConferenceEvent']=$wgCurrentDir.'model/ConferenceEvent.php';
$wgAutoloadClasses['ConferenceOrganizer']=$wgCurrentDir.'model/ConferenceOrganizer.php';
$wgAutoloadClasses['ConferencePage']=$wgCurrentDir.'model/ConferencePage.php';
$wgAutoloadClasses['ConferenceAccount']=$wgCurrentDir.'model/ConferenceAccount.php';
$wgAutoloadClasses['EventLocation']=$wgCurrentDir.'model/EventLocation.php';
$wgAutoloadClasses['ConferenceRegistration']=$wgCurrentDir.'model/ConferenceRegistration.php';
$wgAutoloadClasses['ConferencePassportInfo']=$wgCurrentDir.'model/ConferencePassportInfo.php';
$wgAutoloadClasses['AuthorSubmission']=$wgCurrentDir.'model/AuthorSubmission.php';
$wgAutoloadClasses['CommonHooks']=$wgCurrentDir.'ConferenceHooks.php';
$wgHooks['ParserFirstCallInit'][]='ConferenceHooks::onParserFirstCallInit';