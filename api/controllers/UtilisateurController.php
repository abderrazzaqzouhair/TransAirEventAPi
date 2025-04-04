<?php
require_once __DIR__ .'/../models/Utilisateur.php';
class UtilisateurController {
    private $utilisateur;
    private $request_method;
    private $endpoint;

    public function __construct($request_method, $endpoint) {
        $this->utilisateur = new Utilisateur();
        $this->request_method = $request_method;
        $this->endpoint = $endpoint;
    }

    public function processRequest() {
        switch (true) {
            case ($this->request_method === 'POST' && $this->endpoint === '/utilisateurs'):
                return $this->createUtilisateur();
            
            case ($this->request_method === 'GET' && $this->endpoint === '/utilisateurs'):
                return $this->getAllUtilisateurs();
                
            case ($this->request_method === 'GET' && preg_match('/^\/utilisateurs\/(\d+)$/', $this->endpoint, $matches)):
                return $this->getUtilisateur($matches[1]);
                
            case ($this->request_method === 'PUT' && preg_match('/^\/utilisateurs\/(\d+)$/', $this->endpoint, $matches)):
                return $this->updateUtilisateur($matches[1]);
                
            case ($this->request_method === 'DELETE' && preg_match('/^\/utilisateurs\/(\d+)$/', $this->endpoint, $matches)):
                return $this->deleteUtilisateur($matches[1]);
                
            default:
                return ['status' => 404, 'message' => 'Endpoint non trouvé'];
        }
    }

    private function createUtilisateur() {
        $data = json_decode(file_get_contents("php://input"));
        
        $this->utilisateur->nom = $data->nom;
        $this->utilisateur->prenom = $data->prenom;
        $this->utilisateur->statut = $data->statut ?? 'Standard';
        $this->utilisateur->numero_vol = $data->numero_vol;
        $this->utilisateur->heure_arrivee = $data->heure_arrivee;
        $this->utilisateur->aeroport_arrivee = $data->aeroport_arrivee;
        $this->utilisateur->telephone = $data->telephone;
        $this->utilisateur->email = $data->email;

        if ($this->utilisateur->create()) {
            return [
                'status' => 201,
                'data' => [
                    'message' => 'Utilisateur créé avec succès',
                    'id' => $this->utilisateur->id,
                    'nom' => $this->utilisateur->nom,
                    'prenom' => $this->utilisateur->prenom,
                    'email' => $this->utilisateur->email
                ]
            ];
        }
        return ['status' => 400, 'message' => 'Échec de la création'];
    }

    private function getAllUtilisateurs() {
        $result = $this->utilisateur->getAll();
        $utilisateurs = $result->fetchAll(PDO::FETCH_ASSOC);
        
        if ($utilisateurs) {
            return ['status' => 200, 'data' => $utilisateurs];
        }
        return ['status' => 404, 'message' => 'Aucun utilisateur trouvé'];
    }

    private function getUtilisateur($id) {
        $this->utilisateur->id = $id;
        $stmt = $this->utilisateur->getById();
        $utilisateur_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($utilisateur_data) {
            return ['status' => 200, 'data' => $utilisateur_data];
        }
        return ['status' => 404, 'message' => 'Utilisateur non trouvé'];
    }

    private function updateUtilisateur($id) {
        $data = json_decode(file_get_contents("php://input"));
        
        $this->utilisateur->id = $id;
        $this->utilisateur->nom = $data->nom;
        $this->utilisateur->prenom = $data->prenom;
        $this->utilisateur->statut = $data->statut;
        $this->utilisateur->numero_vol = $data->numero_vol;
        $this->utilisateur->heure_arrivee = $data->heure_arrivee;
        $this->utilisateur->aeroport_arrivee = $data->aeroport_arrivee;
        $this->utilisateur->telephone = $data->telephone;
        $this->utilisateur->email = $data->email;

        if ($this->utilisateur->update()) {
            return ['status' => 200, 'message' => 'Utilisateur mis à jour'];
        }
        return ['status' => 400, 'message' => 'Échec de la mise à jour'];
    }

    private function deleteUtilisateur($id) {
        $this->utilisateur->id = $id;
        if ($this->utilisateur->delete()) {
            return ['status' => 200, 'message' => 'Utilisateur supprimé'];
        }
        return ['status' => 400, 'message' => 'Échec de la suppression'];
    }
}