<?php

class Image {

    //table evt_images fields
    private $_image_id;
    //private $_private;
    //private $_orga_id;
    private $_src;
    private $_alt;

    public function __construct($todo, $args){
        switch ($todo){
            case "read":
                $this->_image_id = $args["id"];
                $this->setImageDataFromDB();
                break;
            case "create":
                return $this->createImage($args["file"]);
                break;
            case "update":
                $this->_image_id = $args["id"];
                global $safeData;
                return $this->updateImageInDB(["alt"], [$safeData->_post["new_image_name"]]);
                break;
            case "delete":
                $this->_image_id = $args["id"];
                return $this->updateImageInDB(["active"], [0]);
                break;
        }
    }

    public function getVarImage($_var){
        return $this->$_var;
    }

    public function setImageDataFromDB(){
        $req = [
            "fields" => [
                'src',
                'alt'],
            "from" => "evt_images",
            "where" => ["image_id = ".$this->_image_id],
            "limit" => 1
        ];
        global $model;
        $data = $model->select($req);
        if ($data["succeed"]){
             $newKey;
            foreach ($data["data"][0] as $key => $value){
                $newKey = "_".$key;
                $this->$newKey = $value;
            }
        }
    }

    public function updateImageInDB($fields, $data){
        $req = [
            "table"  => "evt_images",
            "fields" => $fields,
            "where" => ["image_id = ".$this->_image_id],
            "limit" => 1
        ];
        global $model;
        $update = $model->update($req, $data);
        return $update["succeed"];
    }

    public function createImage($file){
        global $safeData;
        $newfilename = round(microtime(true)) . '.'.$file["ext"];
        $path = "layout/images/".$newfilename;
        if (move_uploaded_file($file["tmp_name"], $path)){
            $req = [
                "table"  => "evt_images",
                "fields" => ["active", "src", "alt"],
            ];
            $data = [1, $path, strtolower($safeData->_post["img_name"])];
            global $model;
            $insert = $model->insert($req, $data);
            return $insert["succeed"];
        }
        else {return false;}
    }

}
