<?php
class ConferenceHooks
{
	public static function onParserFirstCallInit(Parser &$parser)
	{
		$parser->setHook('conference','Conference::render');
		$parser->setHook('conference-page','ConferencePage::render');
		$parser->setHook('author','ConferenceAuthor::render');
		$parser->setHook('author-sub','ConferenceAuthor::renderSub');
		$parser->setHook('submission','AuthorSubmission::render');
		$parser->setHook('event','ConferenceEvent::render');
		$parser->setHook('organizer','ConferenceOrganizer::render');
		$parser->setHook('applicant','ConferenceApplicant::render');
		$parser->setHook('passport-info','ConferencePassportInfo::render');
		$parser->setHook('registration','ConferenceRegistration::render');
		$parser->setHook('location','EventLocation::render');
		$parser->setHook('account', 'ConferenceAccount::render');
		$parser->setHook('account-sub','ConferenceAccount::renderSub');
		$parser->setHook('registration-event', 'ConferenceRegistration::renderSub');
		return true;
	}
}