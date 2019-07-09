<?php

class PageVisitor extends Page
{

    public function __construct($url){
        parent::__construct($url);
        $this->_defaultPage = "see_all_events";
    }

    //adds a complement before using parent
    public function getPage(){
        global $session;
        $session->add("admin_mode", false);
        return Page::getPage();
    }

    public function needs_login(){
        global $session;
        if (null !== $session->get("user_name")){
            if (!isset($this->_url[1]) OR !isset($this->_url[2])) header('Location: ');
            $link = $this->_url[1]."/".$this->_url[2];
            header('Location: ../../logged/'.$link);
        }
        else {
            return $this->login("booking", $this->_url[2]);
        }
    }

    /*-------------------------------------------SEE EVENTS----------------------------------------------*/

    public function see_all_events(){
        $req = [
            "fields" => ['event_id'],
            "from" => "evt_events",
            "where" => [
                "active_event = 1",
                "finish_datetime >= NOW()"
            ],
            "order" => "start_datetime"
        ];
        global $model;
        $data = $model->select($req);
        //if no events
        $all_events = "";
        if (!isset($data["data"][0])){
            $title = "No current event";
        }
        else {
            $title ="Our events";
            $each_event;
            $each_view;
            foreach ($data["data"] as $row){
                $each_event = new Event("read", ["id" => $row["event_id"]]);
                $each_view = new View($each_event->getEventData(), "elt_each_event.html");
                $all_events .= $each_view->_html;
            }
        }
        $view = new View([
            "{{ title }}" => $title,
            "{{ events }}" => $all_events
        ], "content_see_all_events.html");
        return ["All events", $view->_html];
    }

    public function see_event(){
        if (isset($this->_url[1])){
            $event = new Event("read", ["id" => $this->_url[1]]);
            if ($event){
                $eventData = $event->getEventData();
                if ($event->getVarEvent("_type_tickets") != 0){
                    if ($event->getVarEvent("_nb_available_tickets") === 0 ){
                         $eventData["{{ book_tickets }}"] = "Event full! No more tickets available.";
                    }
                    else {
                        $elt_view = new View(["{{ event_id }}" => $this->_url[1]],"elt_book_tickets_btn.html");
                        $eventData["{{ book_tickets }}"] = $elt_view->_html;
                    }
                    if ($event->getVarEvent("_enable_booking")==0){$eventData["{{ book_tickets }}"] = "Booking is not available right now.";}
                }
                else {
                    $eventData["{{ book_tickets }}"] = "No reservation needed.";
                }

                $view = new View($eventData, "content_see_event.html");
                return [$event->getVarEvent("_name"), $view->_html];
            }
            else {
                header('Location: display_error');
            }
        }
        else {
            header('Location: see_all_events');
        }
    }

}
