<?php
class CommonHooks
{
	public static function onParserFirstCallInit(Parser &$parser)
	{
		$parser->setHook('conference','Conference::render');
		$parser->setHook('conference-page','ConferencePage::render');
		$parser->setHook('speaker','ConferenceAuthor::render');
		$parser->setHook('submission','AuthorSubmission::render');
		$parser->setHook('event','ConferenceEvent::render');
		$parser->setHook('organizer','ConferenceOrganizer::render');
		$parser->setHook('applicant','ConferenceApplicant::render');
		$parser->setHook('passport-info','ConferencePassportInfo::render');
		$parser->setHook('registration','ConferenceRegistration::render');
		$parser->setHook('location','EventLocation::render');
		return true;
	}
}