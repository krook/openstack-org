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
class CategoryChangeForm extends Form
{

	function __construct($controller, $name)
	{

        $SummitCategories = SummitCategory::get()->filter('SummitID',4);
        $SummitCategoriesField = new DropdownField("CategoryID", "New Track", $SummitCategories->map("ID", "Name"));
        $TalkIDField = new HiddenField("ID","ID");

		$fields = new FieldList(
            $SummitCategoriesField,
            $TalkIDField
		);

		$submitButton = new FormAction('doSubmitChange', 'Suggest Change');
		$submitButton->addExtraClass('btn btn-default btn-sm');

		$actions = new FieldList(
			$submitButton
		);

		parent::__construct($controller, $name, $fields, $actions);
	}

	function forTemplate()
	{
		return $this->renderWith(array(
			$this->class,
			'Form'
		));
	}

}