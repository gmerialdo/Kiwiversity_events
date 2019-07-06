<?php

class Account
{

    private $_valid;

    //table evt_accounts fields
    private $_evt_account_id;
    private $_email;
    private $_user_name;
    private $_password ;
    private $_first_name;
    private $_last_name;
    private $_managing_rights;
    private $_active_account;


    public function __construct($todo, $args){
        switch ($todo){
            case "login":
                $this->_valid = $this->validateLogin($args["user_name"], $args["password"]);
                if ($this->_valid){
                    $this->setAccountDataFromDB();
                    $this->logSession();
                }
                break;
            case "read":
                $this->_evt_account_id = $args["id"];
                $this->setAccountDataFromDB();
                break;
            case "create":
                $this->_email = $args["email"];
                $this->_valid = $this->emailFree();
                if ($this->_valid == false){
                    return false;
                }
                return $this->createAccount($args);
                break;
            case "update":
                $this->_evt_account_id = $args["id"];
                $this->updateAccountInDB(array_slice($args, 1));
                break;
        }
    }

    public function setAccountDataFromDB(){
        $req = [
            "fields" => [
                'evt_account_id',
                'email',
                'user_name',
                'password',
                'first_name',
                'last_name',
                'managing_rights',
                'active_account'
            ],
            "from" => "evt_accounts",
            "where" => [ "evt_account_id = ".$this->_evt_account_id],
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

    public function getVarAccount($_var){
        return $this->$_var;
    }

    public function getAccountData(){
        if ($this->_managing_rights == 0){$status = "User";} else {$status = "Admin";}
        return [
            "{{ evt_account_id }}" => $this->_evt_account_id,
            "{{ first_name }}" => $this->_first_name,
            "{{ last_name }}" => $this->_last_name,
            "{{ user_name }}" => $this->_user_name,
            "{{ email }}" => $this->_email,
            "{{ active_account }}" => $this->_active_account,
            "{{ managing_rights }}" => $this->_managing_rights,
            "{{ status }}" => $status
        ];
    }

    public function validateLogin($user_name, $password){
        $hash = hash("sha256", $password);
        $req = [
                "fields" => ["*"],
                "from" => "evt_accounts",
                "where" => [
                    "user_name ='$user_name'",
                    "password ='$hash'",
                    "active_account = 1"
                    ]
        ];
        $data = Model::select($req);
        if (!empty($data["data"])){
            $this->_evt_account_id = $data["data"][0]["evt_account_id"];
        }
        //return true if not empty or false otherwise
        return !empty($data["data"]);
    }

    public function logSession(){
        global $session;
        $session->add("user_name", $this->_user_name);
        $session->add("first_name", $this->_first_name);
        $session->add("last_name", $this->_last_name);
        $session->add("evt_managing_rights", $this->_managing_rights);
        $session->add("admin_mode", false);
        $session->add("evt_account_id", $this->_evt_account_id);
    }

    public function emailFree(){
        $req = [
                "fields" => ["*"],
                "from" => "evt_accounts",
                "where" => ["email ='$this->_email'"]
        ];
        $data = Model::select($req);
        //return true if not empty or false otherwise
        return empty($data["data"]);
    }

    public function createAccount($args){
        $email = $args["email"];
        $data = [
            $email ,
            hash("sha256", $args["password"]),
            $args["email"],
            ucfirst($args["first_name"]),
            ucfirst($args["last_name"]),
            0,
            1
        ];
        $req = [
            "table"  => "evt_accounts",
            "fields" => [
                'email',
                'password',
                'user_name',
                'first_name',
                'last_name',
                'managing_rights',
                'active_account'
            ]
        ];
        $create = Model::insert($req, $data);
        $this->_evt_account_id = $create["data"];
        $this->setAccountDataFromDB();
        $this->logSession();
        return $create["succeed"];
    }

    public function updateAccountInDB($args){
        $fields = [];
        $data = [];
        foreach ($args as $key => $value) {
            $fields[] = $key;
            $data[] = $value;
        }
        $req = [
            "table"  => "evt_accounts",
            "fields" => $fields,
            "where" => ["evt_account_id = ".$this->_evt_account_id],
            "limit" => 1
        ];
        $update = Model::update($req, $data);
        return $update["succeed"];
    }

}
