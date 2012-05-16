<?php
class ConferenceTest extends MediaWikiTestCase
{
	protected $conference;
	public function setUp()
	{
		$this->conference=Conference::createFromScratch($title, $venue, $capacity, $startDate, $endDate, $description);
		$conferenceId=$this->conference->getId();
		//create four users and get their ids and store them in $userIds
		$organizersInfo=array(array('id'=>$userIds[0],'cat'=>'','post'=>''),
		array('id'=>$userIds[1],'cat'=>'','post'=>''),
		array('id'=>$userIds[2],'cat'=>'','post'=>''),
		array('id'=>$userIds[3],'cat'=>'','post'=>''));
		$organizers=array();
		foreach ($organizersInfo as $organizer)
		{
			$organizers[]=ConferenceOrganizer::createFromScratch($conferenceId, $organizer['id'], $organizer['cat'], $organizer['post']);
		}
		//now create a set of pages for the conference
		$pagesType=array('Welcome Page','Contact Us','Submissions','Schedule');
		$pages=array();
		foreach($pagesType as $type)
		{
			$pages[]=ConferencePage::createFromScratch($conferenceId, $type);
		}
		//now add some new locations
		$locationsInfo=array(array("209","Its a big room","resource/images/img209.png"),
		array("210","Its also a big room","resource/images/img210.png"),
		array("211","Its not a very big room","resource/images/img212/png"));
		$locations=array();
		foreach ($locationsInfo as $location)
		{
			$locations[]=EventLocation::createFromScratch($location[0], $location[1], $location[2]);
		}
		$eventsInfo=array(array('location'=>$locations[0].getLocationId(),'startTime'=>'','endTime'=>'','day'=>'','topic'=>'','group'=>''),
		array('location'=>$locations[1].getLocationId(),'startTime'=>'','endTime'=>'','day'=>'','topic'=>'','group'=>''),
		array('location'=>$locations[2].getLocationId(),'startTime'=>'','endTime'=>'','day'=>'','topic'=>'','group'=>''));
		$events=array();
		foreach ($eventsInfo as $event)
		{
			$events[]=ConferenceEvent::createFromScratch($conferenceId, $event['location'], $event['startTime'], 
			$event['endTime'], $event['day'], $event['topic'], $event['group']);
		}
		$this->conference->setPages($pages);
		$this->conference->setEvents($events);
		$this->conference->setOrganizers($organizers);
		
		
	}
	public function tearDown()
	{
		$this->conference=null;
	}
	/**
	 * @dataProvider provideData
	 */
	public function testConferenceData($expected)
	{
		$conferenceLoad=Conference::loadFromId($this->conference->getId());
		$this->assertEquals($conferenceLoad->getTitle(),$expected['conference']['title'],'Title doesnt match');
		$this->assertEquals($conferenceLoad->getDescription(),$expected['conference']['description'],'Description doesnt match');
		$this->assertEquals($conferenceLoad->getCapacity(),$expected['conference']['capacity'],'Capacity doesnt match');
		$this->assertEquals($conferenceLoad->getStartDate(),$expected['conference']['startDate'],'Start Date doesnt match');
		$this->assertEquals($conferenceLoad->getEndDate(),$expected['conference']['endDate'],'End Date doesnt match');
		$this->assertEquals($conferenceLoad->getVenue(),$expected['conference']['venue'],'Venue doesnt match');
		return $conferenceLoad;
		
	}
	/**
	 * @depends testConferenceData
	 * @dataProvider provideData
	 */
	public function testEventsData($expected,$conferenceLoad)
	{
		$events=$conferenceLoad->getEvents();
		$i=0;
		foreach($events as $event)
		{
			$this->assertEquals($event->getStartTime(),$expected['event'][$i]['startDate'],'The startDate for event number $i doesnt match');
			$this->assertEquals($event->getEndTime(),$expected['event'][$i]['endDate'],'The endDate for event number $i doesnt match');
			$this->assertEquals($event->getDay(),$expected['event'][$i]['day'],'The day for event number $i doesnt match');
			$this->assertEquals($event->getTopic(),$expected['event'][$i]['topic'],'The topic for event number $i doesnt match');
			$this->assertEquals($event->getStartTime(),$expected['event'][$i]['group'],'The group for event number $i doesnt match');
			$i++;
			
		}
		return $events;
	}
	/**
	 * @depends testEventsData
	 * @dataProvider provideData
	 */
	public function testLocationsData($expected,$events)
	{
		/*$locations=array();
		foreach($events as $event)
		{
			$locations[]=EventLocation::loadFromId($event->getLocationId());
		}*/
		$i=0;
		foreach($events as $event)
		{
			$location=EventLocation::loadFromId($event->getLocationId());
			$this->assertEquals($location->getRoomNo(),$expected['location'][$i]['roomNo'],'Room no doesnt match for location number $i');
			$this->assertEquals($location->getDescription(),$expected['location'][$i]['description'],'Room no doesnt match for location number $i');
			$this->assertEquals($location->getUrl(),$expected['location'][$i]['url'],'Room no doesnt match for location number $i');
			$i++;
		}
	}
	/**
	 * @depends testConferenceData
	 * @dataProvider provideData
	 */
	public function testPagesData($expected,$conferenceLoad)
	{
		$pages=$conferenceLoad->getPages();
		$i=0;
		foreach ($pages as $page)
		{
			$this->assertEquals($page->getType(),$expected['page'][$i],'Type doesnt match for page number $i');
			$i++;
		}
	}
	/**
	 * @depends testConferenceData
	 * @dataProvider provideData
	 */
	public function testOrganizersData($expected,$conferenceLoad)
	{
		$organizers=$conferenceLoad->getOrganizers();
		$i=0;
		foreach ($organizers as $organizer)
		{
			$this->assertEquals($organizer->getCategory(),$expected['category'][$i]);
			$this->assertEquals($organizer->getPost(),$expected['post'][$i]);
			$i++;
		}
	}
	public function dataProvider()
	{
		//we are only returning one set of data
		return array(array(
		'conference'=>array('title'=>'','venue'=>'','capacity'=>'','startDate'=>'','endDate'=>'','description'=>''),
		'category'=>array('','','',''),
		'post'=>array('','','',''),
		'page'=>array('Welcome Page','Contact Us','Submissions ','Schedule'),
		'location'=>array(array('roomNo'=>"209",'description'=>"Its a big room",'url'=>"resource/images/img209.png"),
		array('roomNo'=>"210",'description'=>"Its also a big room",'url'=>"resource/images/img210.png"),
		array('roomNo'=>"211",'description'=>"Its not a very big room",'url'=>"resource/images/img212/png")),
		'event'=>array(array('startTime'=>'','endTime'=>'','day'=>'','topic'=>'','group'=>''),
		array('startTime'=>'','endTime'=>'','day'=>'','topic'=>'','group'=>''),
		array('startTime'=>'','endTime'=>'','day'=>'','topic'=>'','group'=>'')),
		));
	}
	
}