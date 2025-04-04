<?php
require_once __DIR__ . '/../config.php';

class UtilisateurSysteme {
    private $conn;
    private $table = 'utilisateurs_systeme';

    public $id;
    public $nom;
    public $email;
    public $mot_de_passe;
    public $role;

    public function __construct() {
        $this->conn = DB::connect();
    }

    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' 
                 SET nom = :nom, 
                     email = :email, 
                     mot_de_passe = :mot_de_passe, 
                     role = :role';

        $stmt = $this->conn->prepare($query);

        $hashed_password = password_hash($this->mot_de_passe, PASSWORD_BCRYPT);

        $stmt->bindParam(':nom', $this->nom);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':mot_de_passe', $hashed_password);
        $stmt->bindParam(':role', $this->role);

        return $stmt->execute();
    }

    public function getAll() {
        $query = 'SELECT id, nom, email, role FROM ' . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById() {
        $query = 'SELECT id, nom, email, role FROM ' . $this->table . ' WHERE id = :id LIMIT 0,1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        $query = 'UPDATE ' . $this->table . ' 
                 SET nom = :nom, 
                     email = :email, 
                     role = :role 
                 WHERE id = :id';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nom', $this->nom);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function delete() {
        $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function login($email, $password) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE email = :email LIMIT 0,1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            return $user;
        }
        return false;
    }
}