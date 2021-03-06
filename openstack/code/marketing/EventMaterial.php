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
class EventMaterial extends DataObject {

	private static $db = array(
		'Name' => 'Text',
		'SortOrder' => 'Int'
	);

	private static $default_sort = 'SortOrder';

    private static $has_one = array(
			'Attachment' => 'File',
			'MarketingPage' => 'MarketingPage'
	);
	
	function getCMSFields(){

		$fields = new FieldList();
		$attach = new CustomUploadField('Attachment','File');
		$attach->setFolderName('marketing/event_material');
		$attach->setAllowedExtensions(array('doc','docx','txt','rtf','xls','xlsx','pages', 'ppt','pptx','pps','csv', 'html','htm','xhtml', 'xml','pdf','ai','key'));

		$fields->push(new TextField('Name'));
		$fields->push($attach);

		return $fields;
	}
	
	function getValidator()	{
	    $validator= new FileRequiredFields(array('Name'));
        $validator->setRequiredFileFields(array("Attachment"));
        return $validator;
	}
	
	public function getFileURL(){
		$file = $this->Attachment();
		if ($file->exists()) {
			return $file->getURL();
		}
	}
	
	public function getFileName(){
		$file = $this->Attachment();
		if ($file->exists()) {
			return $file->Name;
		}
		return 'n/a';
	}
	
	public function getIcon(){
		$icon = new Iconifier();
		return $icon->getIcon($this->Attachment()->Filename);
	}

}