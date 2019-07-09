<?php

class Model {

    //PDO instance
    private $_db;

    public function __construct($base, $user, $password){
        try {
            $this->_db = new PDO('mysql:host=localhost;dbname='.$base.';charset=utf8', $user, $password);
            $this->_db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
        }
        catch(Exception $e){
            die('Erreur : '.$e->getMessage());
        }
    }

    public function request($sql, $data=NULL, $insert=false){
        try {
            if ($data == NULL){
                $result = $this->_db->query($sql);
                $data = $result->fetchAll();
            }
            else {
                $result = $this->_db->prepare($sql);
                $result->execute($data);
                //store result
            }
            if ($insert){
                $data= $this->_db->lastInsertId();
            }
            //close request
            $result->closeCursor();
            //if no result
            if (empty($data)) $data="";

            return [
                "succeed" => true,
                "data"    => $data
            ];
        }
        catch(Exception $e) {
            print_r($e);
            return [
                "succeed" => false,
                "data"    => $e
            ];
        }
    }

    // build an sql SELECT query from args array
    public function select($args){
        //add all fields to be selected
        $req = 'SELECT '.implode(", ", $args["fields"]);
        //add db table
        $req .= ' FROM '.$args["from"];
        //add optional thing
        if (isset($args["join"])) $req .= ' INNER JOIN ' .$args["join"];
        if (isset($args["on"])) $req.= ' ON ' .$args["on"];
        if (isset($args["where"])) $req .= ' WHERE ' .implode(" AND ", $args["where"]);
        if (isset($args["order"])) $req .= " ORDER BY ".$args["order"];
        if (isset($args["limit"])) $req .= " LIMIT ".$args["limit"];
        //launch query and return result
        return $this->request($req);
    }

    // build an sql UPDATE query from args array
    public function update($args, $data){
        $req = 'UPDATE '.$args["table"];
        $req .= ' SET '.implode("=? , ", $args["fields"])."=?";
        $req .= ' WHERE '.implode(" AND ", $args["where"]);
        if (isset($args["limit"])) $req .= " LIMIT ".$args["limit"];
        //launch query and return result
        return $this->request($req, $data);
    }

    // build an sql INSERT query from args array
    public function insert($args, $data){
        $req = 'INSERT INTO '.$args["table"];
        $req .= ' ('.implode(", ", $args["fields"]).")";
        $req .= ' VALUES ( ?';
        $i = 1;
        while (isset($args["fields"][$i])){
            $req .= " , ?";
            $i++;
        }
        $req .= " )";
        //launch query and return result
        return $this->request($req, $data, true);
    }

    // build an sql DELETE query from args array
    public function delete($args){
        $req = 'DELETE FROM '.$args["from"];
        if (isset($args["where"])) $req .= ' WHERE ' .implode(" AND ", $args["where"]);
        //launch query and return result
        return $this->request($req);
    }

}
