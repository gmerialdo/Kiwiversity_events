<?php

class Location {

    //table evt_locations fields
    private $_location_id;
    private $_name;
    private $_address;
    private $_city;
    private $_zipcode;
    private $_state;
    private $_country;
    private $_phone;
    private $_max_occupancy;

    public function __construct($todo, $args){
        switch ($todo){
            case "read":
                $this->_location_id = $args["id"];
                $this->setLocationDataFromDB();
                break;
            case "create":
                return $this->createLocation($args);
                break;
            case "update":
            //$args consists of an array with id, array of fields and array of data to update in those fields
                $this->_location_id = $args["id"];
                return $this->updateLocationInDB(["name", "address", "city", "zipcode", "state", "country", "phone", "max_occupancy", "active"], $args["data"]);
                break;
            case "delete":
                $this->_location_id = $args["id"];
                return $this->updateLocationInDB(["active"], [0]);
                break;
        }
    }

    public function setLocationDataFromDB(){
        $req = [
            "fields" => [
                'location_id',
                'name',
                'address',
                'city',
                'zipcode',
                'state',
                'country',
                'phone',
                'max_occupancy'],
            "from" => "evt_locations",
            "where" => [ "location_id = ".$this->_location_id],
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

    public function getVarLocation($_var){
        return $this->$_var;
    }

    public function getLocationData(){
        return [
            "{{ location_id }}" => $this->_location_id,
            "{{ location_name }}" => ucfirst($this->_name),
            "{{ location_address }}" => $this->_address,
            "{{ location_city }}" => ucfirst($this->_city),
            "{{ location_zipcode }}" => $this->_zipcode,
            "{{ location_state }}" => $this->_state,
            "{{ location_country }}" => ucfirst($this->_country),
            "{{ location_phone }}" => $this->_phone,
            "{{ max_occupancy }}" => $this->_max_occupancy,
            "{{ location_whole_address1 }}" => $this->_address,
            "{{ location_whole_address2 }}" => ucfirst($this->_city).", ".ucfirst($this->_state)." ".$this->_zipcode.", ".ucfirst($this->_country)
        ];
    }

    public function updateLocationInDB($fields, $data){
        $req = [
            "table"  => "evt_locations",
            "fields" => $fields,
            "where" => ["location_id = ".$this->_location_id],
            "limit" => 1
        ];
        $update = Model::update($req, $data);
        return $update["succeed"];
    }

    public function createLocation($data){
        $req = [
            "table"  => "evt_locations",
            "fields" => ["name", "address", "city", "zipcode", "state", "country", "phone", "max_occupancy", "active"],
        ];
        $insert = Model::insert($req, $data);
        return $insert["succeed"];
    }

}
