<?php

class Model {
  private $db;

  public function __construct($base, $user, $password){
    try {
      $this->db = new PDO('mysql:host=localhost;dbname='.$base.';charset=utf8', $user, $password);
      $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
    }
    catch(Exception $e){
      die('Erreur : '.$e->getMessage());
    }
  }

  /**
   * Description transforme le tableau de requete en requete SQL pour insérer
   *
   * Some example:
   * @code
   * $request =[
   *   "type" => "insert",
   *   "req" => [
   *     "table" => "commentaire",
   *     "set"   => [
   *       "auteur"  => "jean David",
   *       "message" => "superarticle"
   *     ]
   *   ]
   * ];
   * @endcode
   *
   * @param array $elementsRequete contient la requete sous forme de tableau
   * @return array retourne un tableau contenant les données
   */
  private function inserer($elementsRequete){
    $list   = [];
    $values = [];
    foreach ($elementsRequete["set"] as $key => $value) {
      array_push($list, "`$key`");
      array_push($values, ":$key");
    }

    $sql    = "INSERT INTO `".$elementsRequete["table"]."` (";
    $sql .= implode(", ", $list);
    $sql .= ") VALUES (";
    $sql .= implode(", ", $values);
    $sql .= ")";

    $result = $this->prepAndExec($sql, $elementsRequete["set"]);

    if ($result["succeed"]) $result["data"] = $this->db->lastInsertId();
    return $result;
  }

  private function read($elementsRequete){
    $sql = "SELECT ";
    $sql .= implode(", ", $elementsRequete["need"]);
    $sql .= " FROM ".$elementsRequete["table"];
    if (isset($elementsRequete["where"])){
      $sql .= ' WHERE ' .implode(" AND ", $elementsRequete["where"]);
    }
    if (isset($elementsRequete["limit"])){
      $sql .= " LIMIT ".$elementsRequete["limit"];
    }
    return $this->query($sql);
  }

  private function query($sql){
    try { // query

      $resultat = $this->db->query($sql);

      $data = $resultat->fetchAll();
      $resultat->closeCursor();
      return [
        "succeed" => TRUE,
        "data" => $data
      ];
    }
    catch(Exception $e) {
      return [
        "succeed" => FASLE,
        "data" => $e
      ];
    }
  }

  private function maj($elementsRequete){
    $set = [];
    $sql = "UPDATE `".$elementsRequete["table"]."` SET ";
    foreach ($elementsRequete["set"] as $key => $value) {
      array_push($set, "`$key` = :$key");
    }
    $sql .= implode(", ", $set);
    $sql .= " WHERE ";
    $sql .= implode(" AND ", $elementsRequete["where"]);
    return $this->prepAndExec($sql, $elementsRequete["set"]);
  }

  private function prepAndExec($sql, $data){
    try { // prepaer & execute


      $resultat = $this->db->prepare($sql);
      $resultat->execute($data);
      $data = $resultat->fetchAll();
      $resultat->closeCursor();
      return [
        "succeed" => TRUE,
        "data" => $data
      ];
    }
    catch(Exception $e) {
      return [
        "succeed" => FASLE,
        "data" => $e
      ];
    }
  }




  private function effacer($sql){
    $req= $this->db->query($sql);
  }

  public function getData($request){
    // if (empty($this->db)) $this->dbConnect("francis","devUser","devPassword");
    switch ($request["type"]) {
    case 'select':
      $todo = "read";
      break;
    case 'update':
      $todo = "maj";
      break;
    case 'insert':
      $todo = "inserer";
      break;
    default:
      # code...
      break;
    }

    return $this->$todo($request["req"]);
  }

}


/*
exemple de requete de mise à jour
  $request =[
    "type" => "update",
    "req" => [
      "table" => "user",
      "where" => ["id = 2"],
      "set"   => [
        "name"     => "ljkkljklj",
        "password" => "dfsdffsdg"
      ]
    ]
  ];

exemple de requete de mlecture
  $elementsRequete= [
    "need" => ["id"],
    "table" => "user",
    "where" => ["name = ".$username, "password = ", $password]
  ];

exemple de requete de mise à jour

  */
