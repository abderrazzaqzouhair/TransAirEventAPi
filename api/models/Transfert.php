<?php
require_once __DIR__ . '/../config.php';

class Transfert
{
    private $conn;
    private $table = 'transferts';

    public $id;
    public $utilisateurs_id;
    public $chauffeur_id;
    public $vehicule_id;
    public $statut;
    public $heure_depart;
    public $heure_arrivee;

    public function __construct()
    {
        $this->conn = DB::connect();
    }

    public function create()
    {
        $query = 'INSERT INTO ' . $this->table . ' 
                 SET utilisateurs_id = :utilisateurs_id, 
                     chauffeur_id = :chauffeur_id, 
                     vehicule_id = :vehicule_id, 
                     statut = :statut, 
                     heure_depart = :heure_depart, 
                     heure_arrivee = :heure_arrivee';

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':utilisateurs_id', $this->utilisateurs_id);
        $stmt->bindParam(':chauffeur_id', $this->chauffeur_id);
        $stmt->bindParam(':vehicule_id', $this->vehicule_id);
        $stmt->bindParam(':statut', $this->statut);
        $stmt->bindParam(':heure_depart', $this->heure_depart);
        $stmt->bindParam(':heure_arrivee', $this->heure_arrivee);

        return $stmt->execute();
    }

    public function getAll()
    {
        $query = 'SELECT * FROM ' . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById()
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = :id LIMIT 0,1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt;
    }

    public function update()
    {
        $query = 'UPDATE ' . $this->table . ' 
                 SET utilisateurs_id = :utilisateurs_id, 
                     chauffeur_id = :chauffeur_id, 
                     vehicule_id = :vehicule_id, 
                     statut = :statut, 
                     heure_depart = :heure_depart, 
                     heure_arrivee = :heure_arrivee
                 WHERE id = :id';

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':utilisateurs_id', $this->utilisateurs_id);
        $stmt->bindParam(':chauffeur_id', $this->chauffeur_id);
        $stmt->bindParam(':vehicule_id', $this->vehicule_id);
        $stmt->bindParam(':statut', $this->statut);
        $stmt->bindParam(':heure_depart', $this->heure_depart);
        $stmt->bindParam(':heure_arrivee', $this->heure_arrivee);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function delete()
    {
        $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function updateStatus($status)
    {
        $query = 'UPDATE ' . $this->table . ' SET statut = :statut WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':statut', $status);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function getByStatus($status)
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE statut = :statut';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':statut', $status);
        $stmt->execute();
        return $stmt;
    }

    public function getActiveByVehicule($vehicule_id)
    {
        $query = 'SELECT * FROM ' . $this->table . ' 
              WHERE vehicule_id = :vehicule_id 
              AND statut IN ("En attente", "Assigné", "En route") 
              LIMIT 1';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':vehicule_id', $vehicule_id);
        $stmt->execute();

        return $stmt;
    }
    public function assign()
    {
        $query = 'UPDATE ' . $this->table . ' 
                 SET chauffeur_id = :chauffeur_id, 
                     vehicule_id = :vehicule_id, 
                     statut = :statut
                 WHERE id = :id';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':chauffeur_id', $this->chauffeur_id);
        $stmt->bindParam(':vehicule_id', $this->vehicule_id);
        $stmt->bindParam(':statut', $this->statut);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function getByVehicule($vehicule_id)
    {
        $query = 'SELECT t.*, 
                     u.nom AS utilisateur_nom, 
                     u.prenom AS utilisateur_prenom,
                     u.numero_vol,
                     u.heure_arrivee,
                     c.nom AS chauffeur_nom
              FROM ' . $this->table . ' t
              LEFT JOIN utilisateurs u ON t.utilisateurs_id = u.id
              LEFT JOIN utilisateurs_systeme c ON t.chauffeur_id = c.id
              WHERE t.vehicule_id = :vehicule_id
              ORDER BY t.heure_depart DESC';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':vehicule_id', $vehicule_id);
        $stmt->execute();

        return $stmt;
    }
}