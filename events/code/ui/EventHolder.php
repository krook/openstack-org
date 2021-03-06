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
/**
 * Defines the JobsHolder page type
 */
class EventHolder extends Page {
   private static$db = array(
   );

   private static $has_one = array(
   );
 
   static $allowed_children = array('EventPage');
   /** static $icon = "icon/path"; */
      
}
/**
 * Class EventHolder_Controller
 */
class EventHolder_Controller extends Page_Controller {

	private static $allowed_actions = array (
		'AjaxFutureEvents',
		'AjaxFutureSummits',
		'AjaxPastSummits',
	);


	function init() {
	    parent::init();
		Requirements::css('events/css/events.css');
		Requirements::javascript('events/js/events.js');
	}
	
	function RandomEventImage(){ 
		$image = Image::get()->filter(array('ClassName:not' => 'Folder'))->where("ParentID = (SELECT ID FROM File WHERE ClassName = 'Folder'
		AND Name = 'EventImages')")->sort('RAND()')->first();
		return $image;
	}

    function RssEvents($limit = 7)
    {
        $feed = new RestfulService('https://groups.openstack.org/events-upcoming.xml', 7200);

        $feedXML = $feed->request()->getBody();

        // Extract items from feed
        $result = $feed->getValues($feedXML, 'channel', 'item');

        foreach ($result as $item) {
            $item->pubDate = date("D, M jS Y", strtotime($item->pubDate));
            $DOM = new DOMDocument;
            $DOM->loadHTML(html_entity_decode($item->description));
            $span_tags = $DOM->getElementsByTagName('span');
            foreach ($span_tags as $tag) {
                if ($tag->getAttribute('property') == 'schema:startDate') {
                    $item->startDate = $tag->getAttribute('content');
                } else if ($tag->getAttribute('property') == 'schema:endDate') {
                    $item->endDate = $tag->getAttribute('content');
                }
            }
            $div_tags = $DOM->getElementsByTagName('div');
            foreach ($div_tags as $tag) {
                if ($tag->getAttribute('property') == 'schema:location') {
                    $item->location = $tag->nodeValue;
                }
            }
        }

        return $result->limit($limit, 0);
    }
	
	function PastEvents($num = 4) {
		return EventPage::get()->filter(array('EventEndDate:LessThanOrEqual'=> date('Y-m-d') , 'IsSummit'=>1))->sort('EventEndDate')->limit($num);
	}

	function FutureEvents($num, $filter = '') {
        $rss_events = $this->RssEvents($num);
        $events_array = new ArrayList();

        $filter_array = array('EventEndDate:GreaterThanOrEqual'=> date('Y-m-d'));
        if ($filter != 'all' && $filter != '') {
            $filter_array['EventCategory'] = $filter;
        }
        $pulled_events = EventPage::get()->filter($filter_array)->sort('EventStartDate','ASC')->limit($num)->toArray();
        $events_array->merge($pulled_events);

        if ($filter == 'Meetups' || $filter == 'all' || $filter == '') {
            foreach ($rss_events as $item) {
                $event_main_info = new EventMainInfo(html_entity_decode($item->title),$item->link,'Details','Meetups');
                $event_start_date = DateTime::createFromFormat(DateTime::ISO8601, $item->startDate);
                $event_end_date = DateTime::createFromFormat(DateTime::ISO8601, $item->endDate);
                $event_duration = new EventDuration($event_start_date,$event_end_date);
                $event = new EventPage();
                $event->registerMainInfo($event_main_info);
                $event->registerDuration($event_duration);
                $event->registerLocation($item->location);
                $events_array->push($event);
            }
        }

		return $events_array->sort('EventStartDate', 'ASC')->limit($num,0)->toArray();
	}

    function PastSummits($num) {
	    return EventPage::get()->filter(array('EventEndDate:LessThanOrEqual'=> date('Y-m-d') , 'IsSummit'=>1))->sort('EventEndDate','DESC')->limit($num);
    }

    function FutureSummits($num) {
	    return EventPage::get()->filter(array('EventEndDate:GreaterThanOrEqual'=> date('Y-m-d') , 'IsSummit'=>1))->sort('EventStartDate','ASC')->limit($num);
    }

    public function getEvents($num = 4, $type, $filter = '') {
        $output = '';

        switch ($type) {
            case 'future_events':
                $events = $this->FutureEvents($num,$filter);
                break;
            case 'future_summits':
                $events = $this->FutureSummits($num);
                break;
            case 'past_summits':
                $events = $this->PastSummits($num);
                break;
        }

        if ($events) {
            foreach ($events as $key => $event) {
                $first = ($key == 0);
                $data = array('IsEmpty'=>0,'IsFirst'=>$first);

                $output .= $event->renderWith('EventHolder_event', $data);
            }
        } else {
            $data = array('IsEmpty'=>1);
            $event = new EventPage();
            $output .= $event->renderWith('EventHolder_event', $data);
        }

        return $output;
    }

    function AjaxFutureEvents() {
        $filter = $_POST['filter'];
        $event_controller = new EventHolder_Controller();
        return $event_controller->getEvents(100,'future_events',$filter);
    }

    function AjaxFutureSummits() {
        return $this->getEvents(5,'future_summits');
    }

    function AjaxPastSummits() {
        return $this->getEvents(5,'past_summits');
    }

	function PostEventLink(){
		$page = EventRegistrationRequestPage::get()->first();
		if($page){
			return $page->getAbsoluteLiveLink(false);
		}
		return '#';
	}
}