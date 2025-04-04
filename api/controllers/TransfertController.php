<?php
require_once __DIR__ . '/../models/Transfert.php';
require_once __DIR__ . '/../models/Utilisateur.php';
require_once __DIR__ . '/../models/Vehicule.php';
require_once __DIR__ . '/../models/UtilisateurSysteme.php';

class TransfertController {
    private $transfert;
    private $request_method;
    private $endpoint;

    public function __construct($request_method, $endpoint) {
        $this->transfert = new Transfert();
        $this->request_method = $request_method;
        $this->endpoint = $endpoint;
    }

    public function processRequest() {
        switch (true) {
            case ($this->request_method === 'POST' && $this->endpoint === '/transferts'):
                return $this->createTransfert();
            
            case ($this->request_method === 'GET' && $this->endpoint === '/transferts'):
                return $this->getAllTransferts();
                
            case ($this->request_method === 'GET' && $this->endpoint === '/transferts/pending'):
                return $this->getPendingTransferts();
                
            case ($this->request_method === 'GET' && preg_match('/^\/transferts\/(\d+)$/', $this->endpoint, $matches)):
                return $this->getTransfert($matches[1]);
                
            case ($this->request_method === 'PUT' && preg_match('/^\/transferts\/(\d+)$/', $this->endpoint, $matches)):
                return $this->updateTransfert($matches[1]);
                
            case ($this->request_method === 'PUT' && preg_match('/^\/transferts\/(\d+)\/status$/', $this->endpoint, $matches)):
                return $this->updateTransfertStatus($matches[1]);
                
            case ($this->request_method === 'PUT' && preg_match('/^\/transferts\/(\d+)\/assign$/', $this->endpoint, $matches)):
                return $this->assignTransfert($matches[1]);
                
            case ($this->request_method === 'DELETE' && preg_match('/^\/transferts\/(\d+)$/', $this->endpoint, $matches)):
                return $this->deleteTransfert($matches[1]);
                
            default:
                return ['status' => 404, 'message' => 'Endpoint non trouvé'];
        }
    }

    private function createTransfert() {
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->utilisateurs_id)) {
            return ['status' => 400, 'message' => 'Champs obligatoires manquants'];
        }

        $this->transfert->utilisateurs_id = $data->utilisateurs_id;
        $this->transfert->statut = 'En attente';
        
        $this->transfert->chauffeur_id = $data->chauffeur_id ?? null;
        $this->transfert->vehicule_id = $data->vehicule_id ?? null;
        $this->transfert->heure_depart = $data->heure_depart ?? null;
        $this->transfert->heure_arrivee = $data->heure_arrivee ?? null;

        if ($this->transfert->create()) {
            return [
                'status' => 201,
                'data' => [
                    'message' => 'Transfert créé avec succès',
                    'id' => $this->transfert->id,
                    'statut' => $this->transfert->statut
                ]
            ];
        }
        return ['status' => 400, 'message' => 'Échec de la création du transfert'];
    }

    private function getAllTransferts() {
        $result = $this->transfert->getAll();
        $transferts = $result->fetchAll(PDO::FETCH_ASSOC);
        
        if ($transferts) {
            $enriched = array_map([$this, 'enrichTransfertData'], $transferts);
            return ['status' => 200, 'data' => $enriched];
        }
        return ['status' => 404, 'message' => 'Aucun transfert trouvé'];
    }

    private function getPendingTransferts() {
        $result = $this->transfert->getByStatus('En attente');
        $transferts = $result->fetchAll(PDO::FETCH_ASSOC);
        
        if ($transferts) {
            $enriched = array_map([$this, 'enrichTransfertData'], $transferts);
            return ['status' => 200, 'data' => $enriched];
        }
        return ['status' => 404, 'message' => 'Aucun transfert en attente'];
    }

    private function getTransfert($id) {
        $this->transfert->id = $id;
        $stmt = $this->transfert->getById();
        $transfert_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($transfert_data) {
            $enriched = $this->enrichTransfertData($transfert_data);
            return ['status' => 200, 'data' => $enriched];
        }
        return ['status' => 404, 'message' => 'Transfert non trouvé'];
    }

    private function updateTransfert($id) {
        $data = json_decode(file_get_contents("php://input"));
        
        $this->transfert->id = $id;
        $this->transfert->utilisateurs_id = $data->utilisateurs_id ?? null;
        $this->transfert->chauffeur_id = $data->chauffeur_id ?? null;
        $this->transfert->vehicule_id = $data->vehicule_id ?? null;
        $this->transfert->statut = $data->statut ?? null;
        $this->transfert->heure_depart = $data->heure_depart ?? null;
        $this->transfert->heure_arrivee = $data->heure_arrivee ?? null;

        if ($this->transfert->update()) {
            return ['status' => 200, 'message' => 'Transfert mis à jour'];
        }
        return ['status' => 400, 'message' => 'Échec de la mise à jour'];
    }

    private function updateTransfertStatus($id) {
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->statut)) {
            return ['status' => 400, 'message' => 'Statut manquant'];
        }

        $this->transfert->id = $id;
        if ($this->transfert->updateStatus($data->statut)) {
            return ['status' => 200, 'message' => 'Statut du transfert mis à jour'];
        }
        return ['status' => 400, 'message' => 'Échec de la mise à jour du statut'];
    }

    private function assignTransfert($id) {
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->chauffeur_id) || empty($data->vehicule_id)) {
            return ['status' => 400, 'message' => 'Chauffeur et véhicule requis'];
        }

        $this->transfert->id = $id;
        $this->transfert->chauffeur_id = $data->chauffeur_id;
        $this->transfert->vehicule_id = $data->vehicule_id;
        $this->transfert->statut = 'Assigné';

        if ($this->transfert->assign()) {
            return ['status' => 200, 'message' => 'Transfert assigné avec succès'];
        }
        return ['status' => 400, 'message' => 'Échec de l\'assignation'];
    }

    private function deleteTransfert($id) {
        $this->transfert->id = $id;
        if ($this->transfert->delete()) {
            return ['status' => 200, 'message' => 'Transfert supprimé'];
        }
        return ['status' => 400, 'message' => 'Échec de la suppression'];
    }

    private function enrichTransfertData($transfert) {
        $user = new Utilisateur();
        $user->id = $transfert['utilisateurs_id'];
        $user_stmt = $user->getById();
        $transfert['utilisateur'] = $user_stmt->fetch(PDO::FETCH_ASSOC);

        if ($transfert['chauffeur_id']) {
            $driver = new UtilisateurSysteme();
            $driver->id = $transfert['chauffeur_id'];
            $driver_stmt = $driver->getById();
            $transfert['chauffeur'] = $driver_stmt->fetch(PDO::FETCH_ASSOC);
            unset($transfert['chauffeur']['mot_de_passe']);
        }

        if ($transfert['vehicule_id']) {
            $vehicle = new Vehicule();
            $vehicle->id = $transfert['vehicule_id'];
            $vehicle_stmt = $vehicle->getById();
            $transfert['vehicule'] = $vehicle_stmt->fetch(PDO::FETCH_ASSOC);
        }

        return $transfert;
    }
}