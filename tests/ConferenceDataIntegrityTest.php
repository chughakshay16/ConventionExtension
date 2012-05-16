<?php
class ConferenceDataIntegrityTest extends MediaWikiTestCase
{
	protected $conferenceIds;
	protected $dbr;
	public function setUp()
	{
		//get all the conference ids present in the database
		//$dbr=wfGetDB(DB_SLAVE);
		$result=$this->dbr->select("page_props",
		array("pp_page","pp_propname","pp_value"),
		array('pp_propname'=>"type",'pp_value'=>'conference'),
		__METHOD__,
		array());
		$this->conferenceIds=array();
		foreach ($result as $row)
		{
			$this->conferenceIds[]=$row->pp_page;
		}
	}
	public function tearDown()
	{
		$this->conferenceIds=null;
	}
	public function testEvents()
	{
		//we are gonna check for the consistency of event wiki pages present in a database
		$resultOne=$this->dbr->select("page_props",
		'*',
		array('pp_propname'=>'event-conf'),
		__METHOD__,
		array());
		$resultTwo=$this->dbr->select("page_props",
		'*',
		array('pp_propname'=>'location',
		__METHOD__,
		array()));
		//$resultOne and $resultTwo should have equal number of rows
		$this->assertEquals($this->dbr->numRows($resultOne),$this->dbr->numRows($resultTwo),"count(event-conf) != count(location)");
		if($this->dbr->numRows($resultOne)==$this->dbr->numRows($resultTwo))
		{
			foreach ($resultOne as $row)
			{
				$this->assertContains($row->pp_value,$this->conferenceIds,'pp_value of event-conf doesnt match with any of the existing conference ids');
			}
		}
		return $resultTwo;
		
	}
	/**
	 * 
	 * @depends testEvents
	 */
	public function testLocations($result)
	{
		//it may be true that for some of the locations events are not set
		foreach ($result as $row)
		{
			
		}
	}
	public function testPages()
	{
	
	}
	public function testOrganizers()
	{
	
	}
	public function testAccounts(){
	
	}
	public function testApplicants()
	{
	
	}
	public function testRegistrations()
	{
	
	}
	public function testPassport()
	{
	
	}
	
}