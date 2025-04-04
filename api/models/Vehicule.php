<?php
require_once __DIR__ . '/../config.php';

class Vehicule {
    private $conn;
    private $table = 'vehicules';

    public $id;
    public $type;
    public $immatriculation;
    public $capacite;

    public function __construct() {
        $this->conn = DB::connect();
    }

    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' 
                 SET type = :type, 
                     immatriculation = :immatriculation, 
                     capacite = :capacite';

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':immatriculation', $this->immatriculation);
        $stmt->bindParam(':capacite', $this->capacite);

        return $stmt->execute();
    }

    public function getAll() {
        $query = 'SELECT * FROM ' . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = :id LIMIT 0,1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        $query = 'UPDATE ' . $this->table . ' 
                 SET type = :type, 
                     immatriculation = :immatriculation, 
                     capacite = :capacite
                 WHERE id = :id';

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':immatriculation', $this->immatriculation);
        $stmt->bindParam(':capacite', $this->capacite);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function delete() {
        $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function getAvailableVehicles() {
        $query = 'SELECT * FROM ' . $this->table . ' 
                 WHERE id NOT IN (SELECT vehicule_id FROM transferts WHERE statut IN ("En attente", "En route"))';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

public function getInService() {
    $query = 'SELECT v.* 
              FROM ' . $this->table . ' v
              INNER JOIN transferts t ON v.id = t.vehicule_id
              WHERE t.statut IN ("Assigné", "En route")
              GROUP BY v.id';
    
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    
    return $stmt;
}
}