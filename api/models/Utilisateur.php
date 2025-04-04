<?php
require_once __DIR__ . '/../config.php';

class Utilisateur {
    private $conn;
    private $table = 'utilisateurs';

    public $id;
    public $nom;
    public $prenom;
    public $statut;
    public $numero_vol;
    public $heure_arrivee;
    public $aeroport_arrivee;
    public $telephone;
    public $email;

    public function __construct() {
        $this->conn = DB::connect();
    }

    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' 
                 SET nom = :nom, 
                     prenom = :prenom, 
                     statut = :statut, 
                     numero_vol = :numero_vol, 
                     heure_arrivee = :heure_arrivee, 
                     aeroport_arrivee = :aeroport_arrivee, 
                     telephone = :telephone, 
                     email = :email';

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(':nom', $this->nom);
        $stmt->bindParam(':prenom', $this->prenom);
        $stmt->bindParam(':statut', $this->statut);
        $stmt->bindParam(':numero_vol', $this->numero_vol);
        $stmt->bindParam(':heure_arrivee', $this->heure_arrivee);
        $stmt->bindParam(':aeroport_arrivee', $this->aeroport_arrivee);
        $stmt->bindParam(':telephone', $this->telephone);
        $stmt->bindParam(':email', $this->email);

        if ($stmt->execute()) {
            return true;
        }
        return false;
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
                 SET nom = :nom, 
                     prenom = :prenom, 
                     statut = :statut, 
                     numero_vol = :numero_vol, 
                     heure_arrivee = :heure_arrivee, 
                     aeroport_arrivee = :aeroport_arrivee, 
                     telephone = :telephone, 
                     email = :email
                 WHERE id = :id';

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nom', $this->nom);
        $stmt->bindParam(':prenom', $this->prenom);
        $stmt->bindParam(':statut', $this->statut);
        $stmt->bindParam(':numero_vol', $this->numero_vol);
        $stmt->bindParam(':heure_arrivee', $this->heure_arrivee);
        $stmt->bindParam(':aeroport_arrivee', $this->aeroport_arrivee);
        $stmt->bindParam(':telephone', $this->telephone);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function findByFlightNumber() {
        $stmt = $this->conn->prepare("SELECT * FROM utilisateurs WHERE numero_vol = :numero_vol");
        $stmt->bindParam(':numero_vol', $this->numero_vol);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}