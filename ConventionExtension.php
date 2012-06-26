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
$wgAutoloadClasses['ConferenceUtils']=$wgCurrentDir.'utils/ConferenceUtils.php';
$wgAutoloadClasses['ConferenceAuthorUtils']=$wgCurrentDir.'/utils/ConferenceAuthorUtils.php';
$wgAutoloadClasses['ConferenceAccountUtils']=$wgCurrentDir.'/utils/ConferenceAccountUtils.php';
$wgAutoloadClasses['UserUtils']=$wgCurrentDir.'utils/UserUtils.php';
$wgAutoloadClasses['ConferenceOrganizerUtils']=$wgCurrentDir.'utils/ConferenceOrganizerUtils.php';
$wgAutoloadClasses['ConferenceEventUtils']=$wgCurrentDir.'utils/ConferenceEventUtils.php';
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
$wgAutoloadClasses['ConferenceHooks']=$wgCurrentDir.'ConferenceHooks.php';
$wgAutoloadClasses['SpecialSample']=$wgCurrentDir.'sample/SpecialSample.php';
$wgSpecialPages['SpecialSample']='SpecialSample';
$wgAutoloadClasses['SpecialDashboard']=$wgCurrentDir.'ui/admin/SpecialDashboard.php';
$wgSpecialPages['Dashboard']='SpecialDashboard';
$wgAutoloadClasses['SpecialConferenceSetup']=$wgCurrentDir.'ui/admin/SpecialConferenceSetup.php';
$wgSpecialPages['ConferenceSetup']='SpecialConferenceSetup';
$wgAutoloadClasses['ConferenceSetupTemplate']=$wgCurrentDir.'templates/ConferenceSetupTemplate.php';
$wgExtensionMessagesFiles['ConferenceSetup']=$wgCurrentDir.'ConventionExtension.i18n.php';
$wgAutoloadClasses['SpecialAuthorRegister']=$wgCurrentDir.'ui/user/SpecialAuthorRegister.php';
$wgSpecialPages['AuthorRegister']= 'SpecialAuthorRegister';
$wgAutoloadClasses['AuthorRegisterTemplate']=$wgCurrentDir.'templates/AuthorRegisterTemplate.php';
$wgAutoloadClasses['ApiConferenceAuthorEdit']=$wgCurrentDir.'api/ApiConferenceAuthor.php';
$wgAPIModules['authoredit']='ApiConferenceAuthorEdit';
$wgAutoloadClasses['ApiAuthorSubmissionEdit']=$wgCurrentDir.'api/ApiConferenceAuthor.php';
$wgAPIModules['subedit']='ApiAuthorSubmissionEdit';
$wgAutoloadClasses['ApiConferenceAuthorDelete']=$wgCurrentDir.'api/ApiConferenceAuthor.php';
$wgAPIModules['authordelete']='ApiConferenceAuthorDelete';
$wgAutoloadClassses['ApiAuthorSubmissionDelete']=$wgCurrentDir.'api/ApiConferenceAuthor.php';
$wgAPIModules['subdelete']='ApiAuthorSubmissionDelete';
$wgResourceModules['ext.conventionExtension.confsetup']=array(
	'scripts'			=>'conference.setup.js',
	'dependencies'		=>'jquery.ui.datepicker',
	'styles'			=>'conference.setup.css',
	'localBasePath'		=>$wgCurrentDir.'resources/conference.setup',
	'remoteExtPath'		=>'ConventionExtension/resources/conference.setup');
$wgResourceModules['ext.conventionExtension.authorregister']=array(
	'scripts'			=>'conference.author.register.js',
	'styles'			=>'conference.author.register.css',
	'localBasePath'		=>$wgCurrentDir.'resources/conference.author.register',
	'remoteBasePath'	=>'ConventionExtension/resources/conference.author.register'
);
$wgResourceModules['ext.conventionExtension.dashboard']=array(
	'scripts'			=>'conference.dashboard.js',
	'styles'			=>'conference.dashboard.css',
	'localBasePath'		=>$wgCurrentDir.'resources/conference.dashboard',
	'remoteBasePath'	=>'ConventionExtension/resources/conference.dashboard'
);

//$wgSpecialPageGroups['SpecialSample'] = 'other';
$wgHooks['ParserFirstCallInit'][]='ConferenceHooks::onParserFirstCallInit';
$wgHooks['UnitTestsList'][] = 'registerUnitTests';
function registerUnitTests( &$files ) {

        global $wgCurrentDir;
        $files[] = $wgCurrentDir . 'tests/ConferenceTest.php';
        return true;
}
$wgCountries = array("Unspecified",
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