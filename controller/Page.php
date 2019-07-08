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
        global $path, $session;
        //function getPage will return an array with $pageTitle and $content
        $getPage = $this->getPage();
        $user_or_admin_style = "style_user.css";
        if ($session->get('admin_mode')){
            $user_or_admin_style = "style_admin.css";
        }
        return view::makeHtml([
            "{{ pageTitle }}" => $getPage[0],
            "{{ user_or_admin_style }}" => $user_or_admin_style,
            "{{ navBar }}" => $this->addNavbar(),
            "{{ content }}" => $getPage[1],
            "{{ footer }}" => file_get_contents("template/footer.html"),
            "{{ orga_name }}" => "Alliance Française de Tucson",
            "{{ orga_logo_src }}" => "layout/images/logo_AFTucson.png",
            "{{ orga_website }}" => "www.aftucson.com",
            "{{ orga_address }}" => "2099E. River Road",
            "{{ orga_city }}" => "Tucson",
            "{{ orga_state }}" => "AZ",
            "{{ orga_zipcode }}" => "85718",
            "{{ orga_country }}" => "USA",
            "{{ orga_email }}" => "alliancefrancaisetucson@gmail.com",
            "{{ orga_phone }}" => "+1 520-881-9158",
            "{{ path }}" => $path
        ], "page_template.html");
    }

    //to add the nav bar
    public function addNavbar(){
        global $session;
        $navbar_account = "";
        $navbar_switch = "";
        $navbar_link = file_get_contents("template/navbar_user.html");
        if ($this->_rights == "visitor"){
            $navbar_accountoption = file_get_contents("template/navbar_accountoption_signin.html");
            $nav_bar_accountoption_mobile = file_get_contents("template/navbar_accountoption_signin.html");
        }
        else {
            $navbar_account = "- ".$session->get('first_name')." ". $session->get('last_name')." -";
            if ($session->get('admin_mode')){
                $navbar_switch = file_get_contents("template/navbar_switchtouser.html");
                $navbar_link = file_get_contents("template/navbar_admin.html");
                $navbar_accountoption = file_get_contents("template/navbar_accountoption_admin.html");
                $nav_bar_accountoption_mobile = file_get_contents("template/navbar_accountoption_admin_mobile.html");
            }
            else {
                 // if user with admin rights
                if ($this->_rights == "admin"){
                    $navbar_switch = file_get_contents("template/navbar_switchtoadmin.html");
                }
                $navbar_accountoption = file_get_contents("template/navbar_accountoption_logged.html");
                $nav_bar_accountoption_mobile = file_get_contents("template/navbar_accountoption_logged_mobile.html");
            }
        }
        return view::makeHtml([
            "{{ navbar_account }}" => $navbar_account,
            "{{ navbar_switch }}" => $navbar_switch,
            "{{ navbar_link }}" => $navbar_link,
            "{{ navbar_accountoption }}" => $navbar_accountoption,
            "{{ navbar_accountoption_mobile }}" => $nav_bar_accountoption_mobile
        ], "navbar_template.html");
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
            $content = View::makeHtml(["{{ may_be_event_id }}" => $may_be_event_id], "content_login.html");
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
            $content = View::makeHtml(["{{ may_be_event_id }}" => $may_be_event_id, "{{ error_msg }}" => $msg], "content_create_account.html");
        }
        return ["Create account", $content];
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
        $data = Model::select($req);
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
            $content = View::makeHtml(["{{ link }}" => "{{ path }}/admin", "{{ link_txt }}" => "Go back to dashboard"], "content_display_error.html");
            return ["Error", $content];
        }
        $content = View::makeHtml(["{{ link }}" => "{{ path }}/see_all_events", "{{ link_txt }}" => "Go back to events"], "content_display_error.html");
        return ["Error", $content];
    }

}
