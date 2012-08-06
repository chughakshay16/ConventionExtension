<?php
class ConferenceSchedule
{
	/**
	 * 
	 * @var ConferenceScheduleTable
	 */
	private $mTable;
	private $mArticle;
	public static function loadFromName($name)
	{
		$templateTitle = Title::makeTitle(NS_TEMPLATE, $name);
		$article = Article::newFromTitle($templateTitle, RequestContext::getMain());
		$content = $article->fetchContent();
		/* the only thing present in the content will be the table */
		$schedule = new self();
		$table = ConferenceScheduleTable::createFromText($content);
		$schedule->setTable($table);
		$schedule->setArticle($article);
		return $schedule;
	}
	public static function createNew($conferenceTitle, $day)
	{
		/* we are assuming that its a valid conference title */
		$name = $conferenceTitle.'/'.$day;
		$title = Title::makeTitle(NS_TEMPLATE, $name);
		$page = WikiPage::factory($title);
		/* create a new page in database */
		$baseTableTemplate = Xml::openElement('table', array('class'=>'wikitable'))
								.Xml::openElement('tr', array('id'=>'date-head'))
									.Xml::openElement('td')
										.$day
									.Xml::closeElement('td')
								.Xml::closeElement('tr')
								.Xml::openElement('tr', array('id'=>'loc-head'))
									.Xml::openElement('td', array('id'=>'loc-empty'))
									.Xml::closeElement('td')
								.Xml::closeElement('tr')
							.Xml::closeElement('table');
		$status = $page->doEdit($baseTableTemplate, EDIT_NEW, 'Creating a new schedule template for the conference');
		if($status->value['revision'])
		{
			return true;
		} else {
			return false;
		}
		
	}
	public function addLocation($location)
	{
		/* check if the location is already present */
		$present = in_array($location->getRoomNo(), $this->mTable->locationColumns);
		if(!$present)
		{
			$this->mTable->addColumn($location->getRoomNo());
			$status = $this->pushEdit($this->mTable->domText, 'Added new Location in the schedule template');
			return $status;
		}
		return true;
	}
	public function editLocation($locationOld, $locationNew)
	{
		/* check if there is a change in room no or not */
		$isChanged = !in_array($locationNew->getRoomNo(), $this->mTable->locationColumns);
		if($isChanged)
		{
			$this->mTable->editColumn($locationOld->getRoomNo(), $locationNew->getRoomNo());
			$status = $this->pushEdit($this->mTable->domText, 'Modified old location name with the new one');
			return $status;
		}
		return true;
	}
	public function deleteLocation($location)
	{
		/* this function assumes that there is no event associated with a location */
		$present = in_array($location->getRoomNo(), $this->mTable->locationColumns);
		if($present)
		{
			$this->mTable->removeColumn($location->getRoomNo());
			$status = $this->pushEdit($this->mTable->domText, 'Deleted location from the schedule template');
			return $status;
		}
		return true;
			
	}
	public function addEvent($event)
	{
		/* check for the time slots */
		$startTime = $event->getStartTime();
		$endTime = $event->getEndTime();
		$timeslot = $startTime.'-'.$endTime;
		//$isNew = in_array($timeslot, array_keys($this->mTable->timeRows)) || count($this->mTable->timeRows) == 0;
		$isNew = true;
		foreach ($this->mTable->timeRows as $index=>$value)
		{
			if($value['timeslot'] == $timeslot)
			{
				$isNew = false;
				break;
			}
		}
		if($isNew)
		{
			$this->mTable->addRow($timeslot); /* it creates an empty row with no cells */
			
		}
		$this->mTable->addCell($event->getTopic(), $timeslot, $event->getLocation()->getRoomNo());
		$status = $this->pushEdit($this->mTable->domText, 'Added new event in the schedule template');
		return $status;
	}
	public function editEvent($oldEvent, $newEvent, $onlyLocationChange = false)
	{
		
		/* check for the event */
		$ost = $oldEvent->getStartTime();
		$oet = $oldEvent->getEndTime();
		$nst = $newEvent->getStartTime();
		$net = $newEvent->getEndTime();
		$oldTS = $ost.'-'.$oet;
		$newTS = $nst.'-'.$net;
		$og = $oldEvent->getGroup();
		$ng = $newEvent->getGroup();
		$ot = $oldEvent->getTopic();
		$nt = $newEvent->getTopic();
		$ol = $oldEvent->getLocation()->getRoomNo();
		$nl = $newEvent->getLocation()->getRoomNo();
		//$isPresent = in_array($timeslot, array_keys($this->mTable->timeRows));
		/**
		 * What all changes can we expect in the process of editing an event ?
		 * 1. change in group - url will change
		 * 2. change in starting time - url + timeslot changes
		 * 3. change in ending time - url + timeslot changes
		 * 4. change in day - url + template changes (this function wont handle this case )
		 * 5. change in topic - url + event cell changes
		 * 6. change in location - event column changes
		 * 7. others + location
		 * timeslot changes may involve adding a new row altogether
		 */
		# all the possible combinations
		if($oldTS != $newTS)
		{
			foreach ($this->mTable->timeRows as $index => $value)
			{
				if($value['timeslot'] == $newTS)
				{
					$found = true;
				}
			}
			/* remove the old cell */
			$this->mTable->removeCell($oldTS, $ol);
			if(!isset($found))
			{
				$this->mTable->addRow($newTS);
			}
		} else {
			$this->mTable->removeCell($oldTS, $ol, true);
		}
		$this->mTable->addCell($nt, $newTS, $nl);
		$status = $this->pushEdit($this->mTable->domText, 'Event was edited in the schedule template');
		return $status;
		
	}
	public function deleteEvent($event)
	{
		$startTime = $event->getStartTime();
		$endTime = $event->getEndTime();
		$timeslot = $startTime.'-'.$endTime;
		$isPresent = false;
		foreach ($this->mTable->timeRows as $index => $value)
		{
			if($value['timeslot'] == $timeslot)
			{
				$isPresent = true;
				break;
			}
		}
		if($isPresent)
		{
			$this->mTable->removeCell($timeslot, $event->getLocation()->getRoomNo());
			$this->pushEdit($this->mTable->domText, 'Event was deleted from the schedule template');
		} else {
			//throw some error
		}
	}
	private function pushEdit($text , $comment ='Modified the schedule template')
	{
		$article = $this->mArticle;
		$status = $article->doEdit($text, $comment, EDIT_UPDATE);
		return $status;
		
	}
	public function getTable()
	{
		return $this->mTable;
	}
	public function setTable($table)
	{
		$this->mTable = $table;
	}
	public function getArticle()
	{
		return $this->mArticle;
	}
	public function setArticle($article)
	{
		$this->mArticle = $article;
	}
}