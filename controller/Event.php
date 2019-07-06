<?php

class Event
{

    //table evt_events fields
    private $_event_id;
    private $_name;
    private $_description;
    private $_location_id;
    private $_image_id;
    private $_category;
    private $_active_event;
    private $_start_datetime;
    private $_finish_datetime;
    private $_max_tickets;
    private $_type_tickets;
    private $_public;
    private $_members_only;
    private $_price_adult_mb;
    private $_price_adult;
    private $_price_child_mb;
    private $_price_child;
    private $_enable_booking;

    //other attributes
    private $_start_weekday;
    private $_finish_weekday;
    private $_start_date;
    private $_finish_date;
    private $_start_time;
    private $_finish_time;
    private $_nb_booked_tickets;
    private $_nb_available_tickets;

    CONST FIELDS_TO_SET = ['name','description','location_id','image_id','category','active_event','start_datetime','finish_datetime','max_tickets','type_tickets','public','members_only','price_adult_mb','price_adult','price_child_mb','price_child','enable_booking'];

    public function __construct($todo, $args){
        switch ($todo){
            case "read":
                $this->_event_id = $args["id"];
                $this->setEventDataFromDB();
                $this->_nb_booked_tickets = $this->countTickets();
                $this->_nb_available_tickets = $this->calculateAvailableTickets();
                return true;
                break;
            case "create":
                return $this->createEvent($args);
                break;
            case "update":
            //$args consists of an array with id, array of fields and array of data to update in those fields
                $this->_event_id = $args["id"];
                return $this->updateEventInDB(Event::FIELDS_TO_SET, $args["data"]);
                break;
            case "delete":
                $this->_event_id = $args["id"];
                return $this->updateEventInDB(["active_event"], [2]);
                break;
        }
    }

    public function setEventDataFromDB(){
        $req = [
            "fields" => [
                'name',
                'description',
                'location_id',
                'image_id',
                'category',
                'active_event',
                'start_datetime',
                'DATE_FORMAT(start_datetime, \'%W\') AS "start_weekday"',
                'DATE_FORMAT(start_datetime, \'%b %D, %Y\') AS "start_date"',
                'DATE_FORMAT(start_datetime, \'%l:%i%p\') AS "start_time"',
                'finish_datetime',
                'DATE_FORMAT(finish_datetime, \'%W\') AS "finish_weekday"',
                'DATE_FORMAT(finish_datetime, \'%b %D, %Y\') AS "finish_date"',
                'DATE_FORMAT(finish_datetime, \'%l:%i%p\') AS "finish_time"',
                'max_tickets',
                'type_tickets',
                'public',
                'members_only',
                'price_adult_mb',
                'price_adult',
                'price_child_mb',
                'price_child',
                'enable_booking'
            ],
            "from" => "evt_events",
            "where" => [ "event_id = ".$this->_event_id],
            "limit" => 1
        ];
        $data = Model::select($req);
        if ($data["succeed"]){
            $newKey;
            foreach ($data["data"][0] as $key => $value){
            $newKey = "_".$key;
            $this->$newKey = $value;
            }
        }
    }

    public function getVarEvent($_var){
        return $this->$_var;
    }

    public function getEventData(){
        require_once "controller/Image.php";
        $image = new Image("read", ["id" => $this->_image_id]);
        require_once "controller/Location.php";
        $location = new Location("read", ["id" => $this->_location_id]);
        if ($this->_enable_booking){$enabled = "Open for booking";} else {$enabled = "Booking disabled";}
        return array_merge($location->getLocationData(), [
            "{{ event_id }}" => $this->_event_id,
            "{{ image_id }}" => $this->_image_id,
            "{{ image_src }}" => $image->getVarImage("_src"),
            "{{ image_alt }}" => $image->getVarImage("_alt"),
            "{{ start_weekday }}" => $this->_start_weekday,
            "{{ start_date }}" => $this->_start_date,
            "{{ start_time }}" => $this->_start_time,
            "{{ start_date_format }}" => date("Y-m-d", strtotime($this->_start_datetime)),
            "{{ finish_weekday }}" => $this->_finish_weekday,
            "{{ finish_date }}" => $this->_finish_date,
            "{{ finish_time }}" => $this->_finish_time,
            "{{ finish_date_format }}" => date("Y-m-d", strtotime($this->_finish_datetime)),
            "{{ name }}" => $this->_name,
            "{{ description }}" => $this->_description,
            "{{ public }}" => $this->_public,
            "{{ public_txt }}" => $this->addPublic(),
            "{{ price }}" => $this->addPrice(false, true),
            "{{ prices }}" => $this->addPrice(false),
            "{{ prices_inline }}" => $this->addPrice(true, false),
            "{{ price_adult_mb }}" => $this->_price_adult_mb,
            "{{ price_adult }}" => $this->_price_adult,
            "{{ price_child_mb }}" => $this->_price_child_mb,
            "{{ price_child }}" => $this->_price_child,
            "{{ enable_booking }}" => $this->_enable_booking,
            "{{ enabled }}" => $enabled,
            "{{ active_event }}" => $this->_active_event,
            "{{ type_tickets }}" => $this->_type_tickets,
            "{{ members_only }}" => $this->_members_only,
            "{{ max_tickets }}" => $this->_max_tickets,
            "{{ category }}" => $this->_category
        ]);
    }

    public function addPublic(){
        switch ($this->_public){
            //case free
            case 1:
                $public = "for all ages";
                break;
            //case paid
            case 2:
                $public = "for adults";
                break;
            //case donation
            case 3:
                $public = "for children";
                break;
            };
        if ($this->_members_only == true){
            $public .= " - members only";
        }
        return $public;
    }

    public function addPrice($inline, $smallestOnly = false){
        switch ($this->_type_tickets){
            //case no booking
            case 0:
                return "free - no reservation";
                break;
            //case free
            case 1:
                return "free";
                break;
            //case paid
            case 2:
                if ($smallestOnly){
                    $smallest_price = min(array_filter([$this->_price_child, $this->_price_adult, $this->_price_child_mb, $this->_price_adult_mb], function ($v){ return !is_null($v); }));
                    return "starts at $".$smallest_price;
                    break;
                }
                else {
                    $prices ="";
                    if ($this->_price_child){
                        $prices .= "Child : $".$this->_price_child." ";
                        if (!$inline){$prices .= "<br/>";}
                    }
                    if ($this->_price_child_mb) {
                        $prices .= "Child (member): $".$this->_price_child_mb." ";
                        if (!$inline){$prices .= "<br/>";}
                    }
                    if ($this->_price_adult) {
                        $prices .= "Adult : $".$this->_price_adult." ";
                        if (!$inline){$prices .= "<br/>";}
                    }

                    if ($this->_price_adult_mb) {
                        $prices .= "Adult (member): $".$this->_price_adult_mb." ";
                    }
                    return $prices;
                    break;
                }
            //case donation
            case 3:
                return "free - donations welcome";
                break;
        }
    }

    public function countTickets(){
        $req = [
            "fields" => ["ticket_id"],
            "from" => "evt_tickets",
            "where" => [ "event_id = ".$this->_event_id, "cancelled_time IS NULL"]
        ];
        $data = Model::select($req);
        if ($data["succeed"]){
            $booked_tickets = 0;
            if (isset($data["data"][0])){
                $ticket;
                foreach ($data["data"] as $row) {
                    require_once("controller/Ticket.php");
                    $ticket = new Ticket("read", ["id" => $row["ticket_id"]]);
                    $booked_tickets += $ticket->getVarTicket("_total_nb_tickets");
                }
            }
        }
        return $booked_tickets;
    }

    public function calculateAvailableTickets(){
        if (!isset($this->_max_tickets)){
            return null;
        }
        else {
            return $this->_max_tickets - $this->_nb_booked_tickets;
        }
    }

    public function updateEventInDB($fields, $data){
        $req = [
            "table"  => "evt_events",
            "fields" => $fields,
            "where" => ["event_id = ".$this->_event_id],
            "limit" => 1
        ];
        $update = Model::update($req, $data);
        return $update["succeed"];
    }

    public function createEvent($data){
        $req = [
            "table"  => "evt_events",
            "fields" => Event::FIELDS_TO_SET
        ];
        $create = Model::insert($req, $data);
        $this->_event_id = $create["data"];
        return $create["succeed"];
    }

    public function setTicketChoice(){
        $tickets_choice = "";
        switch ($this->_type_tickets){
            case 1:
                $tickets_choice .= $this->addOptionTickets("quantity", "nb_tickets_all", "free", "", "");
                break;
            case 2:
                switch ($this->_public){
                    case 1:
                        if (null !== $this->_price_adult_mb){
                            $tickets_choice .= $this->addOptionTickets("adult (member)", "nb_tickets_adult_mb", $this->_price_adult_mb, "$", "price_adult_mb_booked");
                        }
                        if (null !== $this->_price_adult){
                            $tickets_choice .= $this->addOptionTickets("adult", "nb_tickets_adult", $this->_price_adult, "$", "price_adult_booked");
                        }
                        if (null !== $this->_price_child_mb){
                            $tickets_choice .= $this->addOptionTickets("child (member)", "nb_tickets_child_mb", $this->_price_child_mb, "$", "price_child_mb_booked");
                        }
                        if (null !== $this->_price_child){
                            $tickets_choice .= $this->addOptionTickets("child", "nb_tickets_child", $this->_price_child, "$", "price_child_booked");
                        }
                        break;
                    case 2:
                        if (null !== $this->_price_adult_mb){
                            $tickets_choice .= $this->addOptionTickets("adult (member)", "nb_tickets_adult_mb", $this->_price_adult_mb, "$", "price_adult_mb_booked");
                        }
                        if (null !== $this->_price_adult){
                            $tickets_choice .= $this->addOptionTickets("adult", "nb_tickets_adult", $this->_price_adult, "$", "price_adult_booked");
                        }
                        break;
                    case 3:
                        if (null !== $this->_price_child_mb){
                            $tickets_choice .= $this->addOptionTickets("child (member)", "nb_tickets_child_mb", $this->_price_child_mb, "$", "price_child_mb_booked");
                        }
                        if (null !== $this->_price_child){
                            $tickets_choice .= $this->addOptionTickets("child", "nb_tickets_child", $this->_price_child, "$", "price_child_booked");
                        }
                        break;
                }
                break;
            case 3:
                $tickets_choice .= $this->addOptionTickets("quantity", "nb_tickets_all", "donation welcome", "", "");
                $tickets_choice .= file_get_contents("template/elt_nb_tickets_donation.html");
                break;
        }
        return $tickets_choice;
    }

    public function addOptionTickets($type, $name, $price, $sign, $price_booked){
        return View::makeHtml([
            "{{ type_ticket }}" => $type,
            "{{ name_ticket }}" => $name,
            "{{ price_ticket }}" => $price,
            "{{ dollar_sign }}" => $sign,
            "{{ price_booked }}" => $price_booked
        ],"elt_nb_tickets.html");
    }

}
