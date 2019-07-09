<?php

class Page
{

    protected $_url;
    //indicate if logged in and if admin rights: either "visitor" or "logged_visitor" or "admin"
    protected $_rights;
    protected $_defaultPage;


    public function __construct($url){
        $this->_url=$url;
        $this->setRights();
    }

    //to set the user profile
    public function setRights(){
        global $session;
        if (null === $session->get('user_name')){
            $this->_rights="visitor";
        }
        elseif (null !== $session->get('evt_managing_rights') && $session->get('evt_managing_rights')==true) {
            $this->_rights="admin";
        }
        else {
            $this->_rights="logged_visitor";
        }
    }

    //to return the html for the page
    public function getHtmlPage(){
        global $path, $session, $orga;
        //function getPage will return an array with $pageTitle and $content
        $getPage = $this->getPage();
        $style = "style_user.css";
        if ($session->get('admin_mode')){
            $style = "style_admin.css";
        }
        $navbar_view = new View($this->getNavbarData(), "navbar_template.html");
        $footer_view = new View([
            "{{ orga_name }}" => $orga["name"],
            "{{ orga_logo_src }}" => $orga["logo_src"],
            "{{ orga_website }}" => $orga["website"],
            "{{ orga_address }}" => $orga["address"],
            "{{ orga_city }}" => $orga["city"],
            "{{ orga_state }}" =>$orga["state"],
            "{{ orga_zipcode }}" => $orga["zipcode"],
            "{{ orga_country }}" => $orga["country"],
            "{{ orga_email }}" => $orga["email"],
            "{{ orga_phone }}" => $orga["phone"]
        ], "footer.html");
        $view = new View([
            "{{ pageTitle }}" => $getPage[0],
            "{{ user_or_admin_style }}" => $style,
            "{{ navBar }}" => $navbar_view->_html,
            "{{ content }}" => $getPage[1],
            "{{ footer }}" => $footer_view->_html,
            "{{ path }}" => $path
        ], "page_template.html");
        return $view->_html;
    }

    //get all data for the navbar
    public function getNavbarData(){
        global $session, $orga;
        $navbar_account = "";
        $navbar_switch = "";
        $navbar_link = file_get_contents("template/navbar_user.html");
        if ($this->_rights == "visitor"){
            $navbar_acc_opt = file_get_contents("template/navbar_accountoption_signin.html");
            $navbar_acc_opt_mob = file_get_contents("template/navbar_accountoption_signin.html");
        }
        else {
            $navbar_account = "- ".$session->get('first_name')." ". $session->get('last_name')." -";
            if ($session->get('admin_mode')){
                $navbar_switch = file_get_contents("template/navbar_switchtouser.html");
                $navbar_link = file_get_contents("template/navbar_admin.html");
                $navbar_acc_opt = file_get_contents("template/navbar_accountoption_admin.html");
                $navbar_acc_opt_mob = file_get_contents("template/navbar_accountoption_admin_mobile.html");
            }
            else {
                 // if user with admin rights
                if ($this->_rights == "admin"){
                    $navbar_switch = file_get_contents("template/navbar_switchtoadmin.html");
                }
                $navbar_acc_opt = file_get_contents("template/navbar_accountoption_logged.html");
                $navbar_acc_opt_mob = file_get_contents("template/navbar_accountoption_logged_mobile.html");
            }
        }
        return [
            "{{ orga_name }}" => $orga["name"],
            "{{ navbar_account }}" => $navbar_account,
            "{{ navbar_switch }}" => $navbar_switch,
            "{{ navbar_link }}" => $navbar_link,
            "{{ navbar_accountoption }}" => $navbar_acc_opt,
            "{{ navbar_accountoption_mobile }}" => $navbar_acc_opt_mob
        ];
    }

    public function getPage(){
        //see first part of the url and call the function
        isset($this->_url[0])? $fct_to_call = $this->_url[0] : $fct_to_call = $this->_defaultPage;
        //if empty then default page
        if ($fct_to_call == "") $fct_to_call = $this->_defaultPage;
        // if not valid name, then go to default page
        if (!method_exists($this, $fct_to_call)) $fct_to_call = $this->_defaultPage;
        //else call the function named
        return $this->$fct_to_call();
    }

    /*-------------------------------------------MANAGING LOGIN----------------------------------------------*/

    //to display the login page
    public function login($message="", $event_id=0){
        global $session;
        if ($this->_rights == "logged_visitor" OR $this->_rights == "admin"){
            if ($session->get('admin_mode')){
                header('Location: admin');
            }
            else {
                header('Location: logged/see_all_events');
            }
        }
        else {
            if ($event_id == 0) {
                $may_be_event_id = "";
            }
            else {
                $may_be_event_id = "/".$event_id;
            }
            $view = new View(["{{ may_be_event_id }}" => $may_be_event_id], "content_login.html");
            $content = $view->_html;
            if ($message == "error") $content.= file_get_contents("template/msg_login_error.html");
            if ($message == "existing_email") $content.= file_get_contents("template/msg_login_existing_email.html");
            if ($message == "booking") $content.= file_get_contents("template/msg_login_booking.html");
        }
        return ["login", $content];
    }

    //function that checks if username and pw are ok and logs in if yes
    public function checklogin(){
        global $session, $safeData;
        if (!$safeData->postEmpty()){
            $user_name = $safeData->_post["user_name"];
            ?>
            <!--keep user name in localStorage-->
            <script>
                window.localStorage.clear();
                var keep_user_name ='<?php echo $user_name;?>';
                window.localStorage.setItem('user_name', keep_user_name);
            </script>
            <?php
            $password = $safeData->_post["password"];
            $account = new Account("login", ["user_name" => $user_name, "password" => $password]);
            if ($account->getVarAccount("_valid")){
                $msg = "You successfully logged in!";
                if (isset($this->_url[1])){
                    $link = "../logged/book_tickets/".$this->_url[1];
                }
                else {
                    $link = "see_all_events";
                }
                $this->alertRedirect($msg, $link);
            }
            else {
                return $this->login("error");
            }
        }
        elseif (!empty($session->get('user_name'))){
            header('Location: ');
        }
        else {
            return $this->login("error");
        }
    }

    //function that logs out and redirect to default page
    public function logout(){
        setcookie("PHPSESSID", "", time()-3600);
        //session_destroy();
        header('Location: see_all_events');
    }

    //to display sign in page
    public function signin(){
        if ($this->_rights == "logged_visitor" OR $this->_rights == "admin"){
            header('Location: see_all_events');
        }
        else {
            $msg = "";
            $may_be_event_id = "";
            if (isset($this->_url[1])) {
                if ($this->_url[1] == "error"){
                    $msg = "An account already exists with this email. Please log in or click on 'Forgot password'.";
                    if (isset($this->_url[2])) {
                        $may_be_event_id = "/".$this->_url[2];
                    }
                }
                else {
                   $may_be_event_id = "/".$this->_url[1];
                }
            }
            $view = new View(["{{ may_be_event_id }}" => $may_be_event_id, "{{ error_msg }}" => $msg], "content_create_account.html");
        }
        return ["Create account", $view->_html];
    }

    /*-------------------------------------------MANAGING SIGNIN-------------------------------------------------*/

    //funcion that creates an account and logs into session if it worked
    public function save_account(){
        global $session, $safeData;
        if (!$safeData->postEmpty()){
            $email = $safeData->_post["new_email"];
            ?>
            <!--keep email in localStorage-->
            <script>
                window.localStorage.clear();
                var keep_email='<?php echo $email;?>';
                window.localStorage.setItem('email', keep_email);
            </script>
            <?php
            $data["email"] = $email;
            $data["first_name"] = $safeData->_post["new_first_name"];
            $data["last_name"] = $safeData->_post["new_last_name"];
            $data["password"] = $safeData->_post["new_password"];
            if (empty($session->get('user_name'))){
                $new_account = new Account("create", $data);
                if ($new_account->getVarAccount("_valid") == false){
                    if (isset($this->_url[1])){
                        header('Location: signin/error/$this->_url[1]');
                    }
                    header('Location: signin/error');
                }
                else if (!$new_account){
                    header('Location: ../signin');
                }
                else {
                    $msg = "You successfully signed in!";
                    if (isset($this->_url[1])){
                        $link = "../logged/book_tickets/".$this->_url[1];
                    }
                    else {
                        $link = "see_all_events";
                    }
                    $this->alertRedirect($msg, $link);
                }
            }
            elseif ($session->get('evt_managing_rights') == 1){
                $new_account = new Account("create", $data, true);
                if ($new_account->getVarAccount("_valid") == false){
                    header('Location: admin/create_account/error');
                }
                else if (!$new_account){
                    header('Location: ../display_error/admin');
                }
                else {
                    $msg = "The account has been created!";
                    $link = "admin/manage_accounts";
                    $this->alertRedirect($msg, $link);
                }
            }
        }
        else {
            header('Location: ');
        }
    }

    /*------------------------------------------VARIOUS OPERATIONS ON TICKETS------------------------------------------------*/

    public function alreadyBookedTickets($event_id, $evt_account_id){
        $req = [
            "fields" => ["*"],
            "from" => "evt_tickets",
            "where" => [
                "event_id = ".$event_id,
                "evt_account_id = ".$evt_account_id,
                "cancelled_time is NULL"
            ]
        ];
        global $model;
        $data = $model->select($req);
        //return true if not empty or false otherwise
        return !empty($data["data"]);
    }

    /*-------------------------------------------MANAGING JAVASCRIPT MESSAGES--------------------------------------*/

    public function alertRedirect($msg, $link){
        echo "<script> alert('$msg'); window.location.href='$link'; </script>";
    }

    /*-------------------------------------------MANAGING ERRORS---------------------------------------------------*/

    public function display_error(){
        if (isset($this->_url[1])){
            $view = new View(["{{ link }}" => "{{ path }}/admin", "{{ link_txt }}" => "Go back to dashboard"], "content_display_error.html");
            return ["Error", $view->_html];
        }
        $view = new View(["{{ link }}" => "{{ path }}/see_all_events", "{{ link_txt }}" => "Go back to events"], "content_display_error.html");
        return ["Error", $view->_html];
    }

}
