<?php

class PageAdmin extends Page
{

    public function __construct($url){
        $url = array_slice($url, 1);
        Page::__construct($url);
        $this->_defaultPage = "dashboard";
    }

    //adds a complement before using parent::getPage() to securize all the admin interface: only connect if logged!
    public function getPage(){
        global $session;
        //check if no admin rights
        if ($this->_rights != "admin"){
            header('Location: see_all_events');
        }
        //else the user is logged in so go to the page in admin interface
        else {
            $session->add("admin_mode", true);
            return Page::getPage();
        }
    }

    public function dashboard(){
        $content = file_get_contents("template/content_admin_dashboard.html");
        return ["dashboard", $content];
    }

    /*-------------------------------------------MANAGING EVENTS----------------------------------------------------*/

    public function create_event(){
        $location_choices = $this->setLocationChoices();
        $image_choices = $this->setImageChoices();
        $content = View::makeHtml([
            "{{ name }}" => "",
            "{{ description }}" => "",
            "{{ location_choices }}" => $location_choices,
            "{{ location_id }}" => 1,
            "{{ image_choices }}" => $image_choices,
            "{{ image_id }}" => 1,
            "{{ category }}" => "",
            "{{ active_event }}" => 1,
            "{{ start_date }}" => "",
            "{{ start_time }}" => "",
            "{{ start_date_format }}" => "",
            "{{ finish_date }}" => "",
            "{{ finish_time }}" => "",
            "{{ finish_date_format }}" => "",
            "{{ type_tickets }}" => 0,
            "{{ public }}" => 1,
            "{{ members_only }}" => 0,
            "{{ max_tickets }}" => "",
            "{{ price_adult_mb }}" => "",
            "{{ price_adult }}" => "",
            "{{ price_child_mb }}" => "",
            "{{ price_child }}" => "",
            "{{ enable_booking }}" => 1,
            "{{ create_evt_error_msg }}" => "",
            "{{ title }}" => "Create a new event",
            "{{ action }}" => "save_event",
            "{{ button }}" => "Create the event"
        ], "content_admin_create_event.html");
        return ["Create event", $content];
    }

    public function save_event(){
        global $safeData;
        if (!$safeData->postEmpty()){
            $name = $safeData->_post["name"];
            $name = ucfirst($name);
            $description = $safeData->_post["description"];
            $description = ucfirst($description);
            $location_id = $safeData->_post["location_id"];
            $image_id = $safeData->_post["image_id"];
            $category = $safeData->_post["category"];
            $start_date = $safeData->_post["start_date"];
            $start_time = $safeData->_post["start_time"];
            $finish_date = $safeData->_post["finish_date"];
            $finish_time = $safeData->_post["finish_time"];
            $start_datetime = date("Y-m-d H:i:s", strtotime($start_date." ".$start_time));
            $finish_datetime = date("Y-m-d H:i:s", strtotime($finish_date." ".$finish_time));
            if (empty($safeData->_post["active_event"])){$active_event = 0;} else {$active_event = 1;}
            if (empty($safeData->_post["type_tickets"])){$type_tickets = 0;} else {$type_tickets = $safeData->_post["type_tickets"];}
            if (empty($safeData->_post["public"])){$public = 1;} else {$public = $safeData->_post["public"];}
            if (empty($safeData->_post["members_only"])){$members_only = 0; $price_adult=NULL; $price_child=NULL;} else {$members_only = 1;}
            if (empty($safeData->_post["max_tickets"])){$max_tickets = 0;} else {$max_tickets = $safeData->_post["max_tickets"];}
            if (empty($safeData->_post["price_adult_mb"])){$price_adult_mb = NULL;} else {$price_adult_mb = $safeData->_post["price_adult_mb"];}
            if (empty($safeData->_post["price_adult"])){$price_adult = NULL;} else {$price_adult =$safeData->_post["price_adult"];}
            if (empty($safeData->_post["price_child_mb"])){$price_child_mb = NULL;} else {$price_child_mb = $safeData->_post["price_child_mb"];}
            if (empty($safeData->_post["price_child"])){$price_child = NULL; } else {$price_child = $safeData->_post["price_child"];}
            if (empty($safeData->_post["enable_booking"])){$enable_booking = 0;} else {$enable_booking = 1;}
            if ($public == 2){$price_child=NULL; $price_child_mb=NULL;}
            if ($public == 3){$price_adult=NULL; $price_adult_mb=NULL;}
            //check if date is correct
            if ($start_datetime > $finish_datetime){
                $args = [
                    "{{ name }}" => $name,
                    "{{ description }}" => $description,
                    "{{ location_id }}" => $location_id,
                    "{{ image_id }}" => $image_id,
                    "{{ category }}" => $category,
                    "{{ active_event }}" => $active_event,
                    "{{ start_date_format }}" => $start_date,
                    "{{ start_time }}" => $start_time,
                    "{{ finish_date_format }}" => $finish_date,
                    "{{ finish_time }}" => $finish_time,
                    "{{ type_tickets }}" => $type_tickets,
                    "{{ public }}" => $public,
                    "{{ members_only }}" => $members_only,
                    "{{ max_tickets }}" => $max_tickets,
                    "{{ price_adult_mb }}" => $price_adult_mb,
                    "{{ price_adult }}" => $price_adult,
                    "{{ price_child_mb }}" => $price_child_mb,
                    "{{ price_child }}" => $price_child,
                    "{{ enable_booking }}" => $enable_booking,
                    "{{ create_evt_error_msg }}" => "Ending date must be after the starting date."
                ];
                if (isset($this->_url[1])){
                    $args["{{ title }}"] = "Modify the event";
                    $args["{{ action }}"] = "save_event/".$this->_url[1];
                    $args["{{ button }}"] = "Modify the event";
                }
                else{
                    $args["{{ title }}"] = "Create an event";
                    $args["{{ action }}"] = "save_event";
                    $args["{{ button }}"] = "Create the event";
                }
                $content = View::makeHtml($args, "content_admin_create_event.html");
                return ["Event", $content];
            }
            $data = [$name, $description, $location_id, $image_id, $category, $active_event, $start_datetime, $finish_datetime, $max_tickets, $type_tickets, $public, $members_only, $price_adult_mb, $price_adult, $price_child_mb, $price_child, $enable_booking];
            //print_r($data);
            //if modifying event
            if (isset($this->_url[1])){
                $event = new Event("update", ["id" => $this->_url[1], "data" => $data]);
                if ($event){
                    $msg = "Your changes have been updated.";
                    $link = "../manage_events";
                    $this->alertRedirect($msg, $link);
                }
                else {header('Location: ../../display_error/admin');}
            }
            //if creating new event
            else {
                $new_event = new Event("create", $data);
                if ($new_event){
                    $msg = "The event has been created.";
                    $link = "../manage_events";
                    $this->alertRedirect($msg, $link);
                }
                else { header('Location: ../display_error/admin');}
            }
        }
        else { header('Location: ');}
    }

    public function manage_events(){
        $msg = "";
        if (isset($this->_url[1])){
            if ($this->_url[1] = "no"){
                $msg = "Some tickets are booked for this event. You can't delete it without cancelling the tickets first.";
            }
        }
        //get active current events
        $current_events = $this->getSelectedEvents(1, 1);
        $draft_events = $this->getSelectedEvents(0);
        $past_events = $this->getSelectedEvents(1, 0, false);
        //$trash_events = $this->getSelectedEvents(2);
        $content = View::makeHtml([
            "{{ msg }}" => $msg,
            "{{ current_events }}" => $current_events,
            "{{ draft_events }}" => $draft_events,
            "{{ past_events }}" => $past_events,
            //"{{ trash_events }}" => $trash_events
        ], "content_admin_manage_events.html");
        return ["Manage events", $content];

    }

    public function getSelectedEvents($active, $current = 2, $modify = true){
        $where[0] = "active_event = ".$active;
        if ($current == 0){$where[1] = "finish_datetime < NOW()";}
        else if ($current == 1){$where[1] = "finish_datetime >= NOW()";}
        $req = [
            "fields" => ['event_id'],
            "from" => "evt_events",
            "where" => $where,
            "order" => "start_datetime"
        ];
        $data = Model::select($req);
        //if no events
        $events = "";
        if (!isset($data["data"][0])){
            $events = "No event";
        }
        else {
            $admin_each_event;
            foreach ($data["data"] as $row){
                $admin_each_event = new Event("read", ["id" => $row["event_id"]]);
                $args = $admin_each_event->getEventData();
                $args["{{ book }}"] = "";
                $args["{{ modify }}"] = "";
                $args["{{ delete }}"] = "";
                if ($current == 1){
                    $args["{{ book }}"] = View::makeHtml($args, "elt_admin_each_event_book.html");
                }
                if ($modify == true){
                    $args["{{ modify }}"] = View::makeHtml($args, "elt_admin_each_event_modify.html");
                    $args["{{ delete }}"] = View::makeHtml($args, "elt_admin_each_event_delete.html");
                }
                $events .= View::makeHtml($args, "elt_admin_each_event.html");
            }
        }
        return $events;
    }

    public function modify_event(){
        if (!isset($this->_url[1])){
            header('Location: manage_events');
        }
        else {
            $location_choices = $this->setLocationChoices();
            $image_choices = $this->setImageChoices();
            $event = new Event("read", ["id" => $this->_url[1]]);
            $data = $event->getEventData();
            $data["{{ create_evt_error_msg }}"] = "";
            $data["{{ title }}"] = "Modify the event";
            $data["{{ action }}"] = "save_event/".$this->_url[1];
            $data["{{ button }}"] = "Modify the event";
            $data["{{ location_choices }}"] = $location_choices;
            $data["{{ image_choices }}"] = $image_choices;
            $content = View::makeHtml($data, "content_admin_create_event.html");
            return ["Modify event", $content];
        }
    }

    public function delete_event(){
        if (!isset($this->_url[1])){
            header('Location: manage_events');
        }
        else {
            $this_event = new Event("read", ["id" => $this->_url[1]]);
            if ($this_event->getVarEvent("_nb_booked_tickets") != 0){
                header('Location: ../manage_events/delete_no');
            }
            else {
                $event = new Event("delete", ["id" => $this->_url[1]]);
                if ($event){
                    $msg = "The event has been deleted.";
                    $link = "../manage_events";
                    $this->alertRedirect($msg, $link);
                }
                else {header('Location: ../../display_error/admin');}
            }
        }
    }

    public function duplicate_event(){
        if (!isset($this->_url[1])){
            header('Location: manage_events');
        }
        else {
            $event = new Event("read", ["id" => $this->_url[1]]);
            if ($event){
                $data = $event->getEventData();
                $data["{{ name }}"] = "Copy of ".$data["{{ name }}"];
                $data["{{ enable_booking }}"] = "1";
                $data["{{ active_event }}"] = "1";
                $data["{{ create_evt_error_msg }}"] = "";
                $data["{{ title }}"] = "Create a new event";
                $data["{{ action }}"] = "save_event";
                $data["{{ button }}"] = "Create the event";
                $content = View::makeHtml($data, "content_admin_create_event.html");
                return ["Duplicate event", $content];
            }
            else {header('Location: ../../display_error/admin');}
        }
    }

    /*-------------------------------------------MANAGING ACCOUNTS--------------------------------------------------*/

    public function getActiveAccounts($active = 1){
        //select accounts in db, by default all active accounts
            $req = [
                "fields" => ['evt_account_id'],
                "from" => "evt_accounts",
                "where" => ["active_account = ".$active]
            ];
        return Model::select($req);
    }

    public function setAccountChoices(){
        $data = $this->getActiveAccounts();
        $account_choices = "";
        if (isset($data["data"][0])){
            $each_account;
            foreach ($data["data"] as $row){
                $each_account = new Account("read", ["id" => $row["evt_account_id"]]);
                $args = $each_account->getAccountData();
                $account_choices .= View::makeHtml($args, "elt_admin_each_account_select.html");
            }
        }
        return View::makeHtml(["{{ accounts_choices }}" => $account_choices], "elt_admin_account_choices.html");
    }

    public function manage_accounts(){
        //get active accounts
        $active_accounts = $this->getSelectedAccounts(1);
        $inactive_accounts = $this->getSelectedAccounts(0);
        $content = View::makeHtml([
            "{{ active_accounts }}" => $active_accounts,
            "{{ inactive_accounts }}" => $inactive_accounts,
        ], "content_admin_manage_accounts.html");
        return ["Manage accounts", $content];
    }

    public function create_account(){
        $msg = "";
        if (isset($this->_url[1])){
            $msg = "An account already exists with this email.";
        }
        $content = View::makeHtml(["{{ may_be_event_id }}" => "", "{{ error_msg }}" => $msg], "content_create_account.html");
        return ["create account", $content];
    }

    public function getSelectedAccounts($active){
        $data = $this->getActiveAccounts($active);
        //if no accounts
        $accounts = "";
        if (!isset($data["data"][0])){
            $accounts = "No account";
        }
        else {
            $admin_each_account;
            foreach ($data["data"] as $row){
                $admin_each_account = new Account("read", ["id" => $row["evt_account_id"]]);
                $args = $admin_each_account->getAccountData();
                if ($args["{{ managing_rights }}"] == 1){$args["{{ btn_admin_rights }}"] = View::makeHtml($args, "elt_admin_each_account_remove_rights.html");}
                else {$args["{{ btn_admin_rights }}"] = View::makeHtml($args, "elt_admin_each_account_give_rights.html");}
                if ($args["{{ active_account }}"] == 1){$args["{{ btn_activate }}"] = View::makeHtml($args, "elt_admin_each_account_deactivate.html");}
                else {$args["{{ btn_activate }}"] = View::makeHtml($args, "elt_admin_each_account_activate.html"); $args["{{ btn_admin_rights }}"] = "";}
                $accounts .= View::makeHtml($args, "elt_admin_each_account.html");
            }
        }
        return $accounts;
    }

    public function give_rights(){
        if (!isset($this->_url[1])){
            header('Location: manage_accounts');
        }
        else {
            $account = new Account("update", ["id" => $this->_url[1], "managing_rights" => 1]);
            if ($account){
                $msg = "Your changes have been updated.";
                $link = "../manage_accounts";
                $this->alertRedirect($msg, $link);
            }
            else {header('Location: ../../display_error/admin');}
        }
    }

    public function remove_rights(){
        if (!isset($this->_url[1])){
            header('Location: manage_accounts');
        }
        else {
          $account = new Account("update", ["id" => $this->_url[1], "managing_rights" => 0]);
            if ($account){
                $msg = "Your changes have been updated.";
                $link = "../manage_accounts";
                $this->alertRedirect($msg, $link);
            }
            else {header('Location: ../../display_error/admin');}
        }
    }

    public function activate(){
        if (!isset($this->_url[1])){
            header('Location: manage_accounts');
        }
        else {
          $account = new Account("update", ["id" => $this->_url[1], "active_account" => 1]);
            if ($account){
                $msg = "Your changes have been updated.";
                $link = "../manage_accounts";
                $this->alertRedirect($msg, $link);
            }
            else {header('Location: ../../display_error/admin');}
        }
    }

    public function deactivate(){
        if (!isset($this->_url[1])){
            header('Location: manage_accounts');
        }
        else {
          $account = new Account("update", ["id" => $this->_url[1], "active_account" => 0]);
            if ($account){
                $msg = "Your changes have been updated.";
                $link = "../manage_accounts";
                $this->alertRedirect($msg, $link);
            }
            else {header('Location: ../../display_error/admin');}
        }
    }

    /*-------------------------------------------MANAGING TICKETS---------------------------------------------------*/

    public function manage_tickets(){
        $req = [
            "fields" => ['event_id'],
            "from" => "evt_events",
            "where" => [
                "active_event = 1",
                "finish_datetime >= NOW()"
            ],
            "order" => "start_datetime"
        ];
        $data = Model::select($req);
        //if no events
        $events_tickets = "";
        if (!isset($data["data"][0])){
            $events_tickets = "No event";
        }
        else {
            $admin_each_event;
            $nb_tickets;
            foreach ($data["data"] as $row){
                $admin_each_event = new Event("read", ["id" => $row["event_id"]]);
                $args = $admin_each_event->getEventData();
                if ( $admin_each_event->getVarEvent("_nb_booked_tickets") == 0 ){$args["{{ tickets }}"] = "No ticket booked";}
                else {$args["{{ tickets }}"] = "Tickets booked: ".$admin_each_event->getVarEvent("_nb_booked_tickets");}
                if ($admin_each_event->getVarEvent("_max_tickets") !== null){$args["{{ available_tickets }}"] = $admin_each_event->getVarEvent("_nb_available_tickets");}
                else {$args["{{ available_tickets }}"] = "illimited"; $args["{{ max_tickets }}"] = "undefined";}
                $events_tickets .= View::makeHtml($args, "elt_admin_each_event_tickets.html");
            }
        }
        $content = View::makeHtml(["{{ events_tickets }}" => $events_tickets], "content_admin_manage_tickets.html");
        return ["Manage tickets", $content];
    }

    public function see_tickets(){
        if (!isset($this->_url[1])){
            header('Location: manage_tickets');
        }
        else {
            $event = new Event("read", ["id" => $this->_url[1]]);
            $req = [
                "fields" => ['ticket_id'],
                "from" => "evt_tickets",
                "where" => ["event_id = ".$this->_url[1], "cancelled_time IS NULL"]
            ];
            $data = Model::select($req);
            $tickets = "";
            //if no tickets
            if (!isset($data["data"][0])){
                $tickets = "No ticket";
            }
            else {
                $each_ticket;
                foreach ($data["data"] as $row){
                    $each_ticket = new ticket("read", ["id" => $row["ticket_id"]]);
                    $ticket_data = $each_ticket->getticketData();
                    $account = new Account("read", ["id" =>  $each_ticket->getVarTicket("_evt_account_id")]);
                    $ticket_data["{{ first_name }}"] = $account->getVarAccount("_first_name");
                    $ticket_data["{{ last_name }}"] = $account->getVarAccount("_last_name");
                    $tickets .= View::makeHtml($ticket_data, "elt_admin_each_ticket.html");
                }
            }
            $args = $event->getEventData();
            if ($event->getVarEvent("_max_tickets") !== null){$args["{{ available_tickets }}"] = $event->getVarEvent("_nb_available_tickets");}
            else {$args["{{ available_tickets }}"] = "illimited"; $args["{{ max_tickets }}"] = "undefined";}
            $args = array_merge($args, ["{{ tickets }}" => $tickets]);
            $content = View::makeHtml($args, "content_admin_see_tickets.html");
            return ["See tickets", $content];
        }
    }

    public function book_tickets_for(){
        if (!isset($this->_url[1])){
            header('Location: manage_events');
        }
        else {
            $event = new Event("read", ["id" => $this->_url[1]]);
            if ($event->getVarEvent("_active_event") != 1 OR $event->getVarEvent("_enable_booking") == 0){
                header('Location: manage_events');
            }
            $tickets_choice = $event->setTicketChoice();
            if ($event->getVarEvent("_nb_available_tickets") !== null){
                $nb_available_tickets = $event->getVarEvent("_nb_available_tickets");
            }
            else {
                $nb_available_tickets = "";
            }
            $account_choices = $this->setAccountChoices();
            $content = View::makeHtml([
                "{{ event_id }}" => $event->getVarEvent("_event_id"),
                "{{ event_name }}" => $event->getVarEvent("_name"),
                "{{ tickets_choice }}" => $tickets_choice,
                "{{ action }}" => "admin/save_tickets_for",
                "{{ title }}" => "Book tickets for",
                "{{ btn_action }}" => "Book tickets",
                "{{ account_choices }}"=> $account_choices,
                "{{ nb_available_tickets }}" => $nb_available_tickets,
                "{{ nb_tickets_adult_mb }}" => 0,
                "{{ nb_tickets_adult }}" => 0,
                "{{ nb_tickets_culid_mb }}" => 0,
                "{{ nb_tickets_child }}" => 0,
                "{{ nb_tickets_all }}" => 0,
                "{{ donation }}" => ""
            ], "content_book_tickets.html");
            return ["Book tickets", $content];
        }
    }

    public function save_tickets_for(){
        global $safeData;
        if (!$safeData->postEmpty()){
            $data = $safeData->_post;
            //if there are already booked tickets for this account
            if ($this->alreadyBookedTickets($data["event_id"], $data["evt_account_id"])){
                $msg = "This account has already booked tickets for this event.";
                $link = "see_tickets/".$data["event_id"];
                $this->alertRedirect($msg, $link);
            }
            else {
                // if not enough tickets left
                $nb_tickets_wanted = 0;
                if (isset($data["nb_tickets_adult_mb"])) $nb_tickets_wanted += $data["nb_tickets_adult_mb"];
                if (isset($data["nb_tickets_adult"])) $nb_tickets_wanted += $data["nb_tickets_adult"];
                if (isset($data["nb_tickets_child_mb"])) $nb_tickets_wanted += $data["nb_tickets_child_mb"];
                if (isset($data["nb_tickets_child"])) $nb_tickets_wanted += $data["nb_tickets_child"];
                if (isset($data["nb_tickets_all"])) $nb_tickets_wanted += $data["nb_tickets_all"];
                if ($nb_tickets_wanted == 0){
                    $msg = "No tickets selected. Please indicate the number of tickets you want to book.";
                    $link = "book_tickets_for/".$data["event_id"];
                    $this->alertRedirect($msg, $link);
                }
                else {
                    if (!empty($data["nb_available_tickets"])){
                        if ($data["nb_available_tickets"] < $nb_tickets_wanted){
                            $msg = "Not enough tickets available.";
                            $link = "book_tickets_for/".$data["event_id"];
                            $this->alertRedirect($msg, $link);
                        }
                    }
                    $new_ticket = new Ticket("create", $data);
                    if ($new_ticket){
                            $msg = "The tickets are booked!";
                            $link = "see_tickets/".$data["event_id"];
                            $this->alertRedirect($msg, $link);
                    }
                    else {
                        header('Location: ../display_error');
                    }
                }
            }
        }
        else {
            header('Location: manage_events');
        }
    }

    public function modify_tickets(){
        if (!isset($this->_url[1])){
            header('Location: manage_tickets');
        }
        else {
            $ticket = new Ticket("read", ["id" => $this->_url[1]]);
            $event = new Event("read", ["id" => $ticket->getVarTicket("_event_id")]);
            $tickets_choice = $event->setTicketChoice();
            if (null !== $event->getVarEvent("_nb_available_tickets")){$nb_available_tickets = $event->getVarEvent("_nb_available_tickets");}
            else { $nb_available_tickets = "";}
            $data["{{ event_id }}"] = $event->getVarEvent("_event_id");
            $data["{{ event_name }}"] = $event->getVarEvent("_name");
            global $session;
            $data["{{ account_choices }}"] = View::makeHtml(["{{ evt_account_id }}" => $session->get("evt_account_id")], "elt_admin_account_no_choice.html");
            $data["{{ tickets_choice }}"] = $tickets_choice;
            $data["{{ action }}"] = "admin/save_modif_tickets/{{ ticket_id }}";
            $data["{{ title }}"] = "Modify those tickets";
            $data["{{ btn_action }}"] = "Modify tickets";
            $data["{{ ticket_id }}"] = $this->_url[1];
            $data = array_merge($data, $ticket->getTicketData());
            $nb_already_booked = 0;
            if (isset($data["{{ nb_tickets_adult_mb }}"])) $nb_already_booked += $data["{{ nb_tickets_adult_mb }}"];
            if (isset($data["{{ nb_tickets_adult }}"])) $nb_already_booked += $data["{{ nb_tickets_adult }}"];
            if (isset($data["{{ nb_tickets_child_mb }}"])) $nb_already_booked += $data["{{ nb_tickets_child_mb }}"];
            if (isset($data["{{ nb_tickets_child }}"])) $nb_already_booked += $data["{{ nb_tickets_child }}"];
            if (isset($data["{{ nb_tickets_all }}"])) $nb_already_booked += $data["{{ nb_tickets_all }}"];
            //add $nb_already_booked into available tickets
            $data["{{ nb_available_tickets }}"] = $nb_available_tickets + $nb_already_booked;
            $content = View::makeHtml($data, "content_book_tickets.html");
            return ["Modify tickets", $content];
        }
    }

    public function save_modif_tickets(){
        if (!isset($this->_url[1])){
            header('Location: manage_tickets');
        }
        else {
            global $safeData;
            if (!$safeData->postEmpty()){
                $data = $safeData->_post;
                $data["id"] = $this->_url[1];
                if (isset($data["total_paid"])){
                    $ticket = new Ticket("read", ["id" => $this->_url[1]]);
                    $data["payment_datetime"] = $ticket->getVarTicket("_payment_datetime");
                }
                // if not enough tickets left
                $nb_tickets_wanted = 0;
                if (isset($data["nb_tickets_adult_mb"])) $nb_tickets_wanted += $data["nb_tickets_adult_mb"];
                if (isset($data["nb_tickets_adult"])) $nb_tickets_wanted += $data["nb_tickets_adult"];
                if (isset($data["nb_tickets_child_mb"])) $nb_tickets_wanted += $data["nb_tickets_child_mb"];
                if (isset($data["nb_tickets_child"])) $nb_tickets_wanted += $data["nb_tickets_child"];
                if (isset($data["nb_tickets_all"])) $nb_tickets_wanted += $data["nb_tickets_all"];
                if ($nb_tickets_wanted == 0){
                    $msg = "No tickets selected. Please indicate the number of tickets you want to book.";
                    $link = "../admin/modify_tickets/".$this->_url[1];
                    $this->alertRedirect($msg, $link);
                }
                else {
                    if (isset($data["nb_available_tickets"])){
                        if ($data["nb_available_tickets"] < $nb_tickets_wanted){
                            $msg = "Not enough tickets available.";
                            $link = "../admin/modify_tickets/".$this->_url[1];
                            $this->alertRedirect($msg, $link);
                        }
                    }
                    $ticket = new Ticket("update", $data);
                    if ($ticket){
                        $event_id = $data["event_id"];
                        $msg = "Your changes have been updated.";
                        $link = "../../admin/see_tickets/".$event_id;
                        $this->alertRedirect($msg, $link);
                    }
                    else {header('Location: ../../display_error/admin');}
                }
            }
        }
    }

    public function modify_payment(){
        if (!isset($this->_url[1])){
            header('Location: manage_tickets');
        }
        else {
            global $safeData;
            if (!$safeData->postEmpty()){
                $ticket = new Ticket("read", ["id" => $this->_url[1]]);
                $payment_datetime = $safeData->_post["payment_datetime"];
                if ($payment_datetime == null){ header('Location: ../display_error/admin');}
                $total_paid = $safeData->_post["total_paid"];
                $update = $ticket->updateInDB(["payment_datetime", "total_paid"], [$payment_datetime, $total_paid]);
                if ($update){
                    $msg = "Your changes have been updated.";
                    $link = "../modify_payment/".$this->_url[1];
                    $this->alertRedirect($msg, $link);
                }
                else {header('Location: ../../display_error/admin');}
            }
            else {
                $ticket = new Ticket("read", ["id" => $this->_url[1]]);
                $name = new Account("read", ["id" =>  $ticket->getVarTicket("_evt_account_id")]);
                $args["{{ first_name }}"] = $name->getVarAccount("_first_name");
                $args["{{ last_name }}"] = $name->getVarAccount("_last_name");
                if ($ticket->getVarTicket("_payment_datetime") != null){
                    $args["{{ cancel_payment_btn }}"] = View::makeHtml([], "elt_admin_cancel_payment_btn.html");
                }
                else {$args["{{ cancel_payment_btn }}"] = "";}
                $args = array_merge($args, $args = $ticket->getTicketData());
                $content = View::makeHtml($args, "content_admin_modify_payment.html");
                return ["Modify payment", $content];
            }
        }
    }

    public function cancel_payment(){
        if (!isset($this->_url[1])){
            header('Location: manage_tickets');
        }
        else {
            $ticket = new Ticket("read", ["id" => $this->_url[1]]);
            $update = $ticket->updateInDB(["payment_datetime", "total_paid"], [null, null]);
            if ($update){
                $msg = "The payment has been cancelled.";
                if (!isset($this->_url[2])){$link = "../modify_payment/".$this->_url[1];}
                else {$link = "../../".$this->_url[2];}
                $this->alertRedirect($msg, $link);
            }
            else {header('Location: ../../display_error/admin');}
        }
    }

    public function cancel_tickets(){
        if (!isset($this->_url[1])){
            header('Location: manage_tickets');
        }
        else {
            $ticket = new Ticket("read", ["id" => $this->_url[1]]);
            $event_id = $ticket->getVarTicket("_event_id");
            $cancelled = $ticket->updateInDB(["cancelled_time"], [date("Y-m-d H:i:s")]);
            if ($cancelled){
                $msg = "Those tickets have been cancelled.";
                $link = "../see_tickets/".$event_id;
                $this->alertRedirect($msg, $link);
            }
            else {header('Location: ../../display_error/admin');}
        }
    }

    public function see_cancelled_tickets(){
        $req = [
            "fields" => ['ticket_id'],
            "from" => "evt_tickets",
            "where" => ["cancelled_time IS NOT NULL"]
        ];
        $data = Model::select($req);
        $tickets = "";
        //if no tickets
        if (!isset($data["data"][0])){
            $tickets = "No cancelled ticket";
        }
        else {
            $admin_each_ticket;
            foreach ($data["data"] as $row){
                $admin_each_ticket = new ticket("read", ["id" => $row["ticket_id"]]);
                if ($admin_each_ticket->getVarTicket("_payment_datetime") != null){
                    $args["{{ cancel_btn }}"] = file_get_contents("template/elt_admin_cancel_payment_btn_cancelled.html");
                }
                else {$args["{{ cancel_btn }}"] = "";}
                $name = new Account("read", ["id" =>  $admin_each_ticket->getVarTicket("_evt_account_id")]);
                $args["{{ first_name }}"] = $name->getVarAccount("_first_name");
                $args["{{ last_name }}"] = $name->getVarAccount("_last_name");
                $event = new Event("read", ["id" => $admin_each_ticket->getVarTicket("_event_id")]);
                $args["{{ event_name }}"] = $event->getVarEvent("_name");
                $args = array_merge($args, $admin_each_ticket->getticketData());
                $tickets .= View::makeHtml($args, "elt_admin_each_cancelled_ticket.html");
            }
        }
        $content = View::makeHtml(["{{ cancelled_tickets }}" => $tickets], "content_admin_see_cancelled_tickets.html");
        return ["See cancelled tickets", $content];
    }

    /*-------------------------------------------MANAGING IMAGES--------------------------------------------------*/

    public function getActiveImages(){
        //select all active images in db
        $req = [
            "fields" => ['image_id'],
            "from" => "evt_images",
            "where" => ["active = 1"]
        ];
        return  Model::select($req);
    }

    public function setImageChoices(){
        $data = $this->getActiveImages();
        $image_choices = "";
        if (isset($data["data"][0])){
            $each_image;
            $i=1;
            foreach ($data["data"] as $row){
                $each_image = new Image("read", ["id" => $row["image_id"]]);
                $image_choices .= View::makeHtml(["{{ src }}" => $each_image->getVarImage("_src"), "{{ alt }}" => ucfirst($each_image->getVarImage("_alt")), "{{ nb }}" => $i], "elt_admin_each_image_select.html");
                $i++;
            }
        }
        return $image_choices;
    }

    public function manage_images(){
        $req = [
            "fields" => ['image_id'],
            "from" => "evt_images",
            "where" => ["active = 1"]
        ];
        $data = Model::select($req);
        //if no images
        $images = "";
        if (!isset($data["data"][0])){
            $images = "No images";
        }
        else {
            $admin_each_image;
            foreach ($data["data"] as $row){
                $admin_each_image = new Image("read", ["id" => $row["image_id"]]);
                $images .= View::makeHtml([
                    "{{ image_id }}" => $row["image_id"],
                    "{{ image_src }}" => $admin_each_image->getVarImage("_src"),
                    "{{ image_alt }}" => ucfirst($admin_each_image->getVarImage("_alt")),
                ], "elt_admin_each_image.html");
            }
        }
        $content = View::makeHtml(["{{ active_images }}" => $images], "content_admin_manage_images.html");
        return ["Manage images", $content];
    }

    public function create_image(){
        $error = "";
        if (isset($this->_url[1])){
            switch ($this->_url[1]) {
                case '1':
                    $error = "Sorry, your file could not be uploaded.";
                    break;
                case '2':
                    $error = "Sorry, your file is too large.";
                    break;
                case '3':
                    $error = "Sorry, only JPG, JPEG & PNG files are allowed.";
                    break;
            }
        }
        $content = View::makeHtml(["{{ error_msg }}" => $error], "content_admin_create_image.html");
        return ["Add image", $content];
    }

    public function save_image(){
        global $safeData;
        if (isset($safeData->_file["new_image_file"])){
            $file = $safeData->_file["new_image_file"];
            print_r($file);
            //check size
            if ($file["size"] > 2097152){
                header('Location: create_images/2');
            }
            $tmp = explode(".", $file["name"]);
            $file["ext"] = strtolower(end($tmp));
            $extensions = array("jpeg","jpg","png");
            if (in_array($file["ext"],$extensions) === false){
                header('Location: create_images/3');
            }
            $image = new Image("create", ["file" => $file]);
            if ($image){
                $msg = "The image has been uploaded.";
                $link = "manage_images";
                $this->alertRedirect($msg, $link);
            }
        }
        else {header('Location: manage_images/1');}
    }

    public function rename_image(){
        if (!isset($this->_url[1])){
            header('Location: manage_events');
        }
        else {
            $image = new Image("update", ["id" => $this->_url[1]]);
            if ($image){
                $msg = "Your changes have been updated.";
                $link = "../manage_images";
                $this->alertRedirect($msg, $link);
            }
            else {header('Location: ../../display_error/admin');}
        }
    }

    public function delete_image(){
        if (!isset($this->_url[1])){
            header('Location: manage_images');
        }
        else {
            $image = new Image("delete", ["id" => $this->_url[1]]);
            if ($image){
                $msg = "The image has been deleted.";
                $link = "../manage_images";
                $this->alertRedirect($msg, $link);
            }
            else {header('Location: ../../display_error/admin');}
        }
    }

    /*-------------------------------------------MANAGING LOCATIONS--------------------------------------------------*/

    public function getActiveLocations(){
        //select all active locations in db
        $req = [
            "fields" => ['location_id'],
            "from" => "evt_locations",
            "where" => ["active = 1"]
        ];
        return Model::select($req);
    }

    public function setLocationChoices(){
        $data = $this->getActiveLocations();
        $location_choices = "";
        if (isset($data["data"][0])){
            $each_location;
            foreach ($data["data"] as $row){
                $each_location = new Location("read", ["id" => $row["location_id"]]);
                $location_choices .= View::makeHtml(["{{ name }}" => ucfirst($each_location->getVarLocation("_name")), "{{ location_id }}" => $row["location_id"]], "elt_admin_each_location_select.html");
            }
        }
        return $location_choices;
    }

    public function manage_locations(){
        $data = $this->getActiveLocations();
        //if no locations
        $locations = "";
        if (!isset($data["data"][0])){
            $locations = "No locations";
        }
        else {
            $admin_each_location;
            foreach ($data["data"] as $row){
                $admin_each_location = new Location("read", ["id" => $row["location_id"]]);
                $locations .= View::makeHtml($admin_each_location->getLocationData(), "elt_admin_each_location.html");
            }
        }
        $content = View::makeHtml([
            "{{ active_locations }}" => $locations
        ], "content_admin_manage_locations.html");
        return ["Manage locations", $content];
    }

    public function create_location(){
        $content = View::makeHtml([
            "{{ location_name }}" => "",
            "{{ location_address }}" => "",
            "{{ location_city }}" => "",
            "{{ location_zipcode }}" => "",
            "{{ location_state }}" => "",
            "{{ location_country }}" => "",
            "{{ location_phone }}" => "",
            "{{ max_occupancy }}" => "",
            "{{ title }}" => "Add a new location",
            "{{ action }}" => "save_location",
            "{{ button }}" => "Add the location"
        ], "content_admin_create_location.html");
        return ["Add location", $content];
    }

    public function modify_location(){
        if (!isset($this->_url[1])){
            header('Location: manage_locations');
        }
        else {
            $location = new Location("read", ["id" => $this->_url[1]]);
            $data = $location->getLocationData();
            $data["{{ title }}"] = "Modify the location";
            $data["{{ action }}"] = "save_location/".$this->_url[1];
            $data["{{ button }}"] = "Modify the location";
            $content = View::makeHtml($data, "content_admin_create_location.html");
            return ["Modify location", $content];
        }
    }

    public function save_location(){
        global $safeData;
        if (!$safeData->postEmpty()){
            $data = [$safeData->_post["location_name"], $safeData->_post["location_address"], $safeData->_post["location_city"], $safeData->_post["location_zipcode"], $safeData->_post["location_state"], $safeData->_post["location_country"], $safeData->_post["location_phone"], $safeData->_post["max_occupancy"], 1];
            //if modifying location
            if (isset($this->_url[1])){
                $location = new Location("update", ["id" => $this->_url[1], "data" => $data]);
                if ($location){
                    $msg = "Your changes have been updated.";
                    $link = "../manage_locations";
                    $this->alertRedirect($msg, $link);
                }
                else {header('Location: ../../display_error/admin');}
            }
            //if adding new location
            else {
                $new_location = new Location("create", $data);
                if ($new_location){
                    $msg = "The location has been added.";
                    $link = "manage_locations";
                    $this->alertRedirect($msg, $link);
                }
                else { header('Location: ../display_error/admin');}
            }
        }
        else { header('Location: ');}
    }


    public function delete_location(){
        if (!isset($this->_url[1])){
            header('Location: manage_locations');
        }
        else {
            $location = new Location("delete", ["id" => $this->_url[1]]);
            if ($location){
                $msg = "The location has been deleted.";
                $link = "../manage_locations";
                $this->alertRedirect($msg, $link);
            }
            else {header('Location: ../../display_error/admin');}
        }
    }

}
