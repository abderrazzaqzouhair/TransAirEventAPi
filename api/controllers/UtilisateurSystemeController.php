<?php
require_once __DIR__ . '/../models/UtilisateurSysteme.php';

class UtilisateurSystemeController {
    private $utilisateurSysteme;
    private $request_method;
    private $endpoint;

    public function __construct($request_method, $endpoint) {
        $this->utilisateurSysteme = new UtilisateurSysteme();
        $this->request_method = $request_method;
        $this->endpoint = $endpoint;
    }

    public function processRequest() {
        switch (true) {
            case ($this->request_method === 'POST' && $this->endpoint === '/utilisateurs_systeme'):
                return $this->createUtilisateurSysteme();
            
            case ($this->request_method === 'POST' && $this->endpoint === '/utilisateurs_systeme/login'):
                return $this->login();
                
            case ($this->request_method === 'GET' && $this->endpoint === '/utilisateurs_systeme'):
                return $this->getAllUtilisateursSysteme();
                
            case ($this->request_method === 'GET' && preg_match('/^\/utilisateurs_systeme\/(\d+)$/', $this->endpoint, $matches)):
                return $this->getUtilisateurSysteme($matches[1]);
                
            case ($this->request_method === 'PUT' && preg_match('/^\/utilisateurs_systeme\/(\d+)$/', $this->endpoint, $matches)):
                return $this->updateUtilisateurSysteme($matches[1]);
                
            case ($this->request_method === 'DELETE' && preg_match('/^\/utilisateurs_systeme\/(\d+)$/', $this->endpoint, $matches)):
                return $this->deleteUtilisateurSysteme($matches[1]);
                
            default:
                return ['status' => 404, 'message' => 'Endpoint non trouve'];
        }
    }

    private function createUtilisateurSysteme() {
        $data = json_decode(file_get_contents("php://input"));
        
        $this->utilisateurSysteme->nom = $data->nom;
        $this->utilisateurSysteme->email = $data->email;
        $this->utilisateurSysteme->mot_de_passe = $data->mot_de_passe;
        $this->utilisateurSysteme->role = $data->role ?? 'Transporteur';

        if ($this->utilisateurSysteme->create()) {
            return [
                'status' => 201,
                'data' => [
                    'message' => 'Utilisateur système créé avec succès',
                    'id' => $this->utilisateurSysteme->id,
                    'nom' => $this->utilisateurSysteme->nom,
                    'email' => $this->utilisateurSysteme->email,
                    'role' => $this->utilisateurSysteme->role
                ]
            ];
        }
        return ['status' => 400, 'message' => 'Échec de la création'];
    }

    private function login() {
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->email) || empty($data->mot_de_passe)) {
            return ['status' => 400, 'message' => 'Email et mot de passe requis'];
        }

        $user = $this->utilisateurSysteme->login($data->email, $data->mot_de_passe);
        
        if ($user) {
            return [
                'status' => 200,
                'data' => [
                    'id' => $user['id'],
                    'nom' => $user['nom'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'token' => $this->generateToken($user)
                ]
            ];
        }
        return ['status' => 401, 'message' => 'Authentification échouée'];
    }

    private function generateToken($user) {
        // Simple token generation - consider using JWT in production
        $tokenData = [
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'exp' => time() + (60 * 60) // 1 hour expiration
        ];
        return base64_encode(json_encode($tokenData));
    }

    private function getAllUtilisateursSysteme() {
        $result = $this->utilisateurSysteme->getAll();
        $utilisateurs = $result->fetchAll(PDO::FETCH_ASSOC);
        
        if ($utilisateurs) {
            return ['status' => 200, 'data' => $utilisateurs];
        }
        return ['status' => 404, 'message' => 'Aucun utilisateur système trouvé'];
    }

    private function getUtilisateurSysteme($id) {
        $this->utilisateurSysteme->id = $id;
        $stmt = $this->utilisateurSysteme->getById();
        $utilisateur_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($utilisateur_data) {
            unset($utilisateur_data['mot_de_passe']);
            return ['status' => 200, 'data' => $utilisateur_data];
        }
        return ['status' => 404, 'message' => 'Utilisateur système non trouvé'];
    }

    private function updateUtilisateurSysteme($id) {
        $data = json_decode(file_get_contents("php://input"));
        
        $this->utilisateurSysteme->id = $id;
        $this->utilisateurSysteme->nom = $data->nom;
        $this->utilisateurSysteme->email = $data->email;
        $this->utilisateurSysteme->role = $data->role;

        if ($this->utilisateurSysteme->update()) {
            return ['status' => 200, 'message' => 'Utilisateur système mis à jour'];
        }
        return ['status' => 400, 'message' => 'Échec de la mise à jour'];
    }

    private function deleteUtilisateurSysteme($id) {
        $this->utilisateurSysteme->id = $id;
        if ($this->utilisateurSysteme->delete()) {
            return ['status' => 200, 'message' => 'Utilisateur système supprimé'];
        }
        return ['status' => 400, 'message' => 'Échec de la suppression'];
    }
}