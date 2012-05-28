<?php
/**
 * 
 * @group Database
 * 
 *
 */
class ConferenceTest extends MediaWikiTestCase
{
	public function testSimple()
	{
		$conference=Conference::createFromScratch('title','venue','capacity','startDate','endDate','description');
		//$id=$conference->getId();
		//$conferenceLoad=Conference::loadFromId($id);
		//$this->assertEquals($conferenceLoad->getTitle(),'title','title doesnt match');
		//$this->assertEquals($conferenceLoad->getVenue(),'venue','venue doesnt match');
		//$this->assertEquals($conferenceLoad->getCapacity(),'capacity','capacity doesnt match');
		//$this->assertEquals($conferenceLoad->getDescription(),'description','description doesnt match');
		$this->assertEquals($conference->getTitle(),'title','The title doesnt match');
		$dbr=wfGetDB(DB_SLAVE);
		$result=$dbr->select('page_props',
		'*',
		array('pp_propname'=>'cvext-type','pp_value'=>'conference'),
		__METHOD__);
		$num=$dbr->numRows($result);
		$this->assertEquals($num,1,'The number of rows is different');
		return $conference->getId();
		
	}
	/**
	 * 
	 * @depends testSimple
	 */
	public function testLocation($cid)
	{
		$location= EventLocation::createFromScratch($cid, 'roomNo', 'description', 'url');
		$locationLoad=EventLocation::loadFromId($location->getLocationId());
		$this->assertEquals($locationLoad->getRoomNo(),'roomNo','Room no doesnt match');
		$this->assertEquals($locationLoad->getDescription(),'description','Description doesnt match');
		$this->assertEquals($locationLoad->getImageUrl(),'url','url doesnt match');
		$data=array('conf'=>$cid,'location'=>$location);
		return $data;
	}
	/**
	 * 
	 * Enter description here ...
	 * @depends testLocation
	 */
	public function testEvent($data)
	{
		$event=ConferenceEvent::createFromScratch($data['conf'], $data['location'], 'startTime', 'endTime', 'day', 'topic', 'group');
		$eventLoad=ConferenceEvent::loadFromId($event->getEventId());
		$this->assertEquals($eventLoad->getStartTime(),'startTime','Starttime doesnt match');
		$this->assertEquals($eventLoad->getEndTime(),'endTime','EndTime doesnt match');
		$this->assertEquals($eventLoad->getDay(),'day','Day doesnt match');
		$this->assertEquals($eventLoad->getTopic(),'topic','Group doesnt match');
		$this->assertEquals($eventLoad->getGroup(),'group','group doesnt match');
	}
	/**
	 * 
	 * @depends testSimple
	 */
	public function testPage($cid)
	{
		$page=ConferencePage::createFromScratch($cid, 'Welcome Page',false);
		$pageLoad=ConferencePage::loadFromId($page->getId());
		$this->assertEquals($pageLoad->getConferenceId(),$cid,'Conference id doesnt match');
		$this->assertEquals($pageLoad->getType(),'Welcome Page','Type doesnt match');
	}
	/**
	 * 
	 * 
	 * @depends testSimple
	 */
	public function testOrganizer($cid)
	{
		$organizer=ConferenceOrganizer::createFromScratch($cid,1, array(array('cat'=>'cat','post'=>'post')));
		$organizerLoad=ConferenceOrganizer::loadFromId($organizer->getOrganizerId());
		$this->assertEquals($organizerLoad->getUserId(),1,'User id doesnt match');
		$this->assertEquals($organizerLoad->getConferenceId(),$cid,'Conference id doesnt match');
		$catpost=$organizerLoad->getCategoryPostCombination();
		$this->assertEquals($catpost[0]['cat'],'cat','Category doesnt match');
		$this->assertEquals($catpost[0]['post'],'post','Post doesnt match');
	}
	public function testAccountAndPassport()
	{
			$passportInfo=new ConferencePassportInfo(null,'pno',null,'iby','vu','plc','dob','ctry');
			$account=ConferenceAccount::createFromScratch(1,'male', 'fn', 'ln',$passportInfo);
			$accountLoad=ConferenceAccount::loadFromId($account->getAccountId());
			//$this->assertEquals($accountLoad->getConferenceId(),$cid,'Conference Id doesnt match');
			$this->assertEquals($accountLoad->getFirstName(),'fn','FirstName doesnt match');
			$this->assertEquals($accountLoad->getLastName(),'ln','LastName doesnt match');
			$this->assertEquals($accountLoad->getGender(),'male','Gender doesnt match');
			$this->assertEquals($accountLoad->getPassportInfo()->getPassportNo(),'pno','Passport no doesnt match');
			$this->assertEquals($accountLoad->getPassportInfo()->getIssuedBy(),'iby','IssuedBy doesnt match');
			$this->assertEquals($accountLoad->getPassportInfo()->getValidUntil(),'vu','Valid Until doesnt match');
			$this->assertEquals($accountLoad->getPassportInfo()->getPlace(),'plc','Place doesnt match');
			$this->assertEquals($accountLoad->getPassportInfo()->getDOB(),'dob','DOB doesnt match');
			$this->assertEquals($accountLoad->getPassportInfo()->getCountry(),'ctry','Country doesnt match');
	}
	/**
	 * 
	 * 
	 * @depends testSimple
	 */
	public function testAuthorAndSubmission($cid)
	{
		$submission=new AuthorSubmission(null, null, 'title', 'type', 'abstract', 'track', 'length', 'slidesInfo', 'slotReq');
		$author=ConferenceAuthor::createFromScratch($cid, 1, 'ctry', 'affiliation', 'url',$submission);
		$authorLoad=ConferenceAuthor::loadFromId($author->getAuthorId());
		$this->assertEquals($authorLoad->getCountry(),'ctry','Country doesnt match');
		$this->assertEquals($authorLoad->getAffiliation(),'affiliation','Affiliation doesnt match');
		$this->assertEquals($authorLoad->getBlogUrl(),'url','URL doesnt match');
		$submissions=$authorLoad->getSubmissions();
		$this->assertEquals($submissions[0]->getTitle(),'title','title doesnt match');
		$this->assertEquals($submissions[0]->getType(),'type','type doesnt match');
		$this->assertEquals($submissions[0]->getAbstract(),'abstract','abstract doesnt match');
		$this->assertEquals($submissions[0]->getTrack(),'track','track doesnt match');
		$this->assertEquals($submissions[0]->getLength(),'length','length doesnt match');
		$this->assertEquals($submissions[0]->getSlidesInfo(),'slidesInfo','slidesInfo doesnt match');
		$this->assertEquals($submissions[0]->getSlotReq(),'slotReq','slotReq doesnt match');
	}
}