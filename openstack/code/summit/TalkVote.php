<?php
/**
 * Copyright 2014 Openstack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
class TalkVote extends DataObject {

	static $db = array(
		'VoteValue' => 'Int',
		'Note' => 'Text',
		'IP' => 'Varchar'
	);
	
	static $has_one = array(
		'Voter' => 'Voter',
		'Talk' => 'Talk'
	);
	
	static $singular_name = 'Vote';
	static $plural_name = 'Votes';


	function PresentationTitle() {
		$presentation = SpeakerSubmission::get()->byID($this->SpeakerSubmissionID);
		return $presentation->PresentationTitle;
	}
	
}