<?php
/**
 * @author chughakshay16
 *
 */
class ConferenceScheduleTable 
{
	private $domTable;
	/**
	 * 
	 * @var Array
	 */
	public $timeRows;
	public $locationColumns;
	public $domText;
	public function __construct()
	{
		$this->timeRows = array();
		$this->domText = '';
		$this->domTable = null;	
	}
	public static function createFromText($text)
	{
		$table = new self();
		$table->domText = $text;
		$document = new DOMDocument();
		$document->loadHTML($text);
		/* <table> is the root element of this document */
		# set the locationColumns property
		$trNodeList = $document->getElementsByTagName('tr');
		$trLocation = $trNodeList->item(1);
		$locations = $trLocation->getElementsByTagName('td');
		$locationColumns = array();
		for($i = 0; $i< $locations->length ; $i++)
		{
			if($i!= 0)
			{
				$locationColumns[] = $locations->item($i)->textContent;
			}
		}
		$table->locationColumns = $locationColumns;
		$timeRows = array();
		for($j =2; $j < $trNodeList->length ; $j++)
		{
			$tr = $trNodeList->item($j);
			$temp = $tr->getElementsByTagName('td');
			$timeslot = $temp->item(0)->textContent;
			$eventCells = array();
			for ($k = 0; $k < $temp->length ; $k++)
			{
				if($k != 0)
				{
					$eventCells[] = $temp->item($k)->textContent;
				}
			}
			//$timeRows[$timeslot] = $eventCells;
			$timeRows[] = array('timeslot'=>$timeslot , 'elements' => $eventCells);
		}
		$table->timeRows = $timeRows;
		$table->domTable = $document;
		return $table;
	}
	public function addColumn($locationName)
	{
		$document = $this->domTable;
		$rows = $document->getElementsByTagName('tr');
		$row = $rows->item(1);
		$column = $document->createElement('td');
		$column->appendChild(new DOMText($locationName));
		$column->setAttribute('class','loc-cell');
		$row->appendChild($column);
		if(count($this->timeRows))
		{
			for($i = 2; $i < $rows->length; $i++)
			{
				$tempRow = $rows->item($i);
				$tempElem = $document->createElement('td');
				$tempElem->appendChild(new DOMText(''));
				$tempElem->setAttribute('class','event-cell');
				$tempRow->appendChild($tempElem);
			}
			foreach ($this->timeRows as $index=>$value)
			{
				$value['elements'][] = '';
				$this->timeRows[$index]['elements'] = $value['elements'];
			}
		}
		$this->locationColumns[] = $locationName;
		$dateRow = $rows->item(0);
		$dateRowTd = $dateRow->firstChild;
		$dateRowTd->setAttribute('colspan', count($this->locationColumns) + 1);
		$this->domText = $document->saveHTML($document->getElementsByTagName('table')->item(0));
		
		
	}
	public function editColumn($oldLocationName, $newLocationName)
	{
		$document = $this->domTable;
		$row = $document->getElementsByTagName('tr')->item(1);
		$tdElements = $row->getElementsByTagName('td');
		for($i = 0; $i < $tdElements->length; $i++)
		{
			if($i!=0)
			{
				$td = $tdElements->item($i);
				if($td->textContent == $oldLocationName)
				{
					$child = $td->firstChild;
					$newChild = new DOMText($newLocationName);
					if($td->hasChildNodes())
					{
						$td->removeChild($child);
					}
					$td->appendChild($newChild);
					$index = array_search($oldLocationName, $this->locationColumns);
					$this->locationColumns[$index] = $newLocationName;
					$this->domText = $document->saveHTML($document->getElementsByTagName('table')->item(0));
					break;					
				}
			}
		}
	}
	public function removeColumn($locationName)
	{
		$document = $this->domTable;
		$columnIndex = array_search($locationName, $this->locationColumns);
		$trElements = $document->getElementsByTagName('tr');
		$eventText = '';
		for($i = 2; $i < $trElements->length; $i++)
		{
			$row = $trElements->item($i);
			$remove = $row->getElementsByTagName('td')->item($columnIndex+1);
			$eventText = $remove->textContent;
			$row->removeChild($remove);
			if(!$row->hasChildNodes())
			{
				$document->removeChild($row);
			}
		}
		foreach ($this->timeRows as $index => $value)
		{
			array_splice($value['elements'], $columnIndex, 1);
			$this->timeRows[$index]['elements'] = $value['elements'];
		}
		array_splice($this->locationColumns, $columnIndex, 1);
		$locationRow = $trElements->item(1);
		$tdElements = $locationRow->getElementsByTagName('td');
		foreach ($tdElements as $td)
		{
			if($td->textContent == $locationName)
			{
				$locationRow->removeChild($td);
				break;
			}
		}
		$dateRow = $trElements->item(0);
		$dateRowTd = $dateRow->firstChild;
		$dateRowTd->setAttribute('colspan', count($this->locationColumns) + 1);	
		$this->domText = $document->saveHTML($document->getElementsByTagName('table')->item(0));
	}
	public function addRow($timeslot)
	{
		$document = $this->domTable;
		$index_after = -1;
		foreach ($this->timeRows as $index => $value)
		{
			$slot = $value['timeslot'];
			$startingtime = substr($slot, 0, 4);
			$startingtime = $this->getFormattedTime($startingtime);
			$endingtime = substr($slot, 5, 4);
			$endingtime = $this->getFormattedTime($endingtime);
			/* we assume that there wont be any conflicts */
			$st = substr($timeslot, 0, 4);
			$st = $this->getFormattedTime($st);
			/*$et = substr($timeslot, 5, 4);
			$et = $this->getFormattedTime($et);*/
			if($st > $endingtime)
			{
				$index_after = $index;
				continue;
				
			} elseif ($st == $endingtime) {
				
				$index_after = $index;
				break;
				
			} elseif ($st < $startingtime) {
				
				break;
				
			} else {
				/* conflict in timeslots, ideally it should not happen */
				return ;
			}
		}
		/* create an empty row with first td element as timeslot */
		$newRow = $document->createElement('tr');
		$newRow->setAttribute('class','event-row');
		$firstTd = $document->createElement('td');
		$textNode = new DOMText($timeslot);
		$firstTd->appendChild($textNode);
		$firstTd->setAttribute('class','timeslot-cell');
		$newRow->appendChild($firstTd);
		$elements = array();
		for($i = 0; $i < count($this->locationColumns); $i++)
		{
			$td = $document->createElement('td');
			$textNODE = new DOMText('');
			$td->setAttribute('class','event-cell');
			$td->appendChild($textNODE);
			$newRow->appendChild($td);
			$elements[] = '';
		}
		$newTimeRow = array();
		$newTimeRow[] = array('timeslot'=>$timeslot, 'elements'=>$elements);
		array_splice($this->timeRows, $index_after+1, 0, $newTimeRow);
		$beforeRow = $document->getElementsByTagName('tr')->item($index_after+3);
		$table = $document->getElementsByTagName('table')->item(0);
		if($beforeRow)
		{
			$table->insertBefore($newRow, $beforeRow);
		} else {
			/* it means newRow will be the last row */
			$table->appendChild($newRow);
			
		}
		
		$this->domText = $document->saveHTML($table);
	}
	private function getFormattedTime($time)
	{
		$time = str_split($time, 2);
		$time = implode(':', $time);
		$time = new DateTime($time);
		return $time;
	}
	public function addCell($eventTitle, $timeslot, $locationName)
	{
		$document = $this->domTable;
		$rowIndex = -1;
		$columnIndex = array_search($locationName, $this->locationColumns);
		foreach ($this->timeRows as $index=>$value)
		{
			if($value['timeslot'] == $timeslot)
			{
				$elements = $value['elements'];
				$elements[$columnIndex] = $eventTitle;
				/* now add the new element */
				$tr = $document->getElementsByTagName('tr')->item($index + 2);
				$td = $tr->getElementsByTagName('td')->item($columnIndex + 1);
				$oldChild = $td->firstChild;
				$newChild = new DOMText($eventTitle);
				if($td->hasChildNodes())
				{
					$td->removeChild($oldChild);
				}
				$td->appendChild($newChild);
				$this->timeRows[$index]['elements'] = $elements;
				
			}
		}
		$this->domText = $document->saveHTML($document->getElementsByTagName('table')->item(0));
	}
	public function removeCell($timeslot, $locationName, $preserveRow = false)
	{
		$document = $this->domTable;
		$columnIndex = array_search($locationName, $this->locationColumns);
		foreach ($this->timeRows as $index => $value)
		{
			if($value['timeslot'] == $timeslot)
			{
				$newTextNode = new DOMText('');
				$trRow = $document->getElementsByTagName('tr')->item($index + 2);
				$tdCell = $trRow->getElementsByTagName('td')->item($columnIndex + 1);
				$oldChild = $tdCell->firstChild;
				if($tdCell->hasChildNodes())
				{
					$tdCell->removeChild($oldChild);
				}
				$tdCell->appendChild($newTextNode);
				$elements = $this->timeRows[$index]['elements'];
				$elements[$columnIndex] = '';
				if(!$preserveRow)
				{
					$tempString = implode('',$elements);
					if(strlen($tempString) == 0)
					{
						array_splice($this->timeRows, $index, 1);
						$table = $document->getElementsByTagName('table')->item(0);
						$table->removeChild($trRow);
						break;
					}
				}
				$this->timeRows[$index]['elements'] = $elements;			
			}	
		}
		$this->domText = $document->saveHTML($document->getElementsByTagName('table')->item(0));
	}
	public function getDomTable()
	{
		return $this->domTable;
	}
	public function setDomTable($dom)
	{
		$this->domTable = $dom;
	}
	
}