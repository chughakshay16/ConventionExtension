<?php
class CommonUtils
{
	public static $months = array(
				'Jan',
				'Feb',
				'Mar',
				'Apr',
				'May',
				'Jun',
				'Jul',
				'Aug',
				'Sep',
				'Oct',
				'Nov',
				'Dec');
	
	public static function parseDate( $date /* mmddyyyy*/ )
	{
		//returns an associative array with day, date, month , year
		//like $arr['day'], $arr['date'], $arr['month'], $arr['year']
		$month = substr( $date, 0, 2 );
		if ( substr( $month, 0, 1 ) == '0' )
		{
			$month = substr( $month, 1, 1 );
		}
		$monthDay = substr( $date, 2, 2 );
		if ( substr( $monthDay, 0, 1 ) == '0' )
		{
			$monthDay = substr( $monthDay, 1, 1 );
		}
		$year = substr( $date, 4, 4 );
		$parsedDate['day'] = date( 'l', mktime( 0, 0, 0, $month, $monthDay, $year ) );
		$parsedDate['date'] = $monthDay;
		$index = $month - 1;
		$parsedDate['month'] = self::$months[$index];
		$parsedDate['year'] = $year;
		return $parsedDate;
	}
	public static function getAllConferenceDays( $startDate/* mmddyyyy */, $endDate /* mmddyyyy*/ )
	{
		
		$sd = DateTime::createFromFormat( 'mdY', $startDate );
		$ed = DateTime::createFromFormat( 'mdY', $endDate );
		$diff = date_diff( $sd, $ed );
		$diffDays = $diff->d;
		$days = array();
		for ( $i = 0; $i <= $diffDays; $i++ )
		{
			$days[] = date( 'M d, Y', mktime( 0, 0, 0, substr( $startDate, 0, 2 ), substr( $startDate, 2, 2 ) + $i, substr( $startDate, 4, 4 ) ) );
		}	
		return $days;
	}
}