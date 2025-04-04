<?php
require_once __DIR__ . '/../models/Vehicule.php';
require_once __DIR__ . '/../models/Transfert.php';

class VehiculeController {
    private $vehicule;
    private $request_method;
    private $endpoint;

    public function __construct($request_method, $endpoint) {
        $this->vehicule = new Vehicule();
        $this->request_method = $request_method;
        $this->endpoint = $endpoint;
    }

    public function processRequest() {
        switch (true) {
            case ($this->request_method === 'POST' && $this->endpoint === '/vehicules'):
                return $this->createVehicule();
            
            case ($this->request_method === 'GET' && $this->endpoint === '/vehicules'):
                return $this->getAllVehicules();
                
            case ($this->request_method === 'GET' && $this->endpoint === '/vehicules/available'):
                return $this->getAvailableVehicules();
                
            case ($this->request_method === 'GET' && $this->endpoint === '/vehicules/in-service'):
                return $this->getInServiceVehicules();
                
            case ($this->request_method === 'GET' && preg_match('/^\/vehicules\/(\d+)$/', $this->endpoint, $matches)):
                return $this->getVehicule($matches[1]);
                
            case ($this->request_method === 'GET' && preg_match('/^\/vehicules\/(\d+)\/transferts$/', $this->endpoint, $matches)):
                return $this->getVehiculeTransferts($matches[1]);
                
            case ($this->request_method === 'PUT' && preg_match('/^\/vehicules\/(\d+)$/', $this->endpoint, $matches)):
                return $this->updateVehicule($matches[1]);
            case ($this->request_method === 'DELETE' && preg_match('/^\/vehicules\/(\d+)$/', $this->endpoint, $matches)):
                return $this->deleteVehicule($matches[1]);
                
            default:
                return ['status' => 404, 'message' => 'Endpoint non trouvé'];
        }
    }

    private function createVehicule() {
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->type) || empty($data->immatriculation) || empty($data->capacite)) {
            return ['status' => 400, 'message' => 'Tous les champs sont obligatoires'];
        }

        $this->vehicule->type = $data->type;
        $this->vehicule->immatriculation = $data->immatriculation;
        $this->vehicule->capacite = $data->capacite;
        $this->vehicule->en_maintenance = $data->en_maintenance ?? false;

        if ($this->vehicule->create()) {
            return [
                'status' => 201,
                'data' => [
                    'message' => 'Véhicule créé avec succès',
                    'id' => $this->vehicule->id,
                    'immatriculation' => $this->vehicule->immatriculation,
                    'type' => $this->vehicule->type
                ]
            ];
        }
        return ['status' => 400, 'message' => 'Échec de la création du véhicule'];
    }

    private function getAllVehicules() {
        $result = $this->vehicule->getAll();
        $vehicules = $result->fetchAll(PDO::FETCH_ASSOC);
        
        if ($vehicules) {
            return ['status' => 200, 'data' => $vehicules];
        }
        return ['status' => 404, 'message' => 'Aucun véhicule trouvé'];
    }

    private function getAvailableVehicules() {
        $result = $this->vehicule->getAvailableVehicles();
        $vehicules = $result->fetchAll(PDO::FETCH_ASSOC);
        
        if ($vehicules) {
            return ['status' => 200, 'data' => $vehicules];
        }
        return ['status' => 404, 'message' => 'Aucun véhicule disponible'];
    }

    private function getInServiceVehicules() {
        $result = $this->vehicule->getInService();
        $vehicules = $result->fetchAll(PDO::FETCH_ASSOC);
        
        if ($vehicules) {
            $enriched = array_map([$this, 'enrichWithCurrentTransfer'], $vehicules);
            return ['status' => 200, 'data' => $enriched];
        }
        return ['status' => 404, 'message' => 'Aucun véhicule en service'];
    }

    private function getVehicule($id) {
        $this->vehicule->id = $id;
        $stmt = $this->vehicule->getById();
        $vehicule_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($vehicule_data) {
            $enriched = $this->enrichWithCurrentTransfer($vehicule_data);
            return ['status' => 200, 'data' => $enriched];
        }
        return ['status' => 404, 'message' => 'Véhicule non trouvé'];
    }

    private function getVehiculeTransferts($id) {
        $transfert = new Transfert();
        $result = $transfert->getByVehicule($id);
        $transferts = $result->fetchAll(PDO::FETCH_ASSOC);
        
        if ($transferts) {
            return ['status' => 200, 'data' => $transferts];
        }
        return ['status' => 404, 'message' => 'Aucun transfert pour ce véhicule'];
    }

    private function updateVehicule($id) {
        $data = json_decode(file_get_contents("php://input"));
        
        $this->vehicule->id = $id;
        $this->vehicule->type = $data->type ?? null;
        $this->vehicule->immatriculation = $data->immatriculation ?? null;
        $this->vehicule->capacite = $data->capacite ?? null;
        $this->vehicule->en_maintenance = $data->en_maintenance ?? null;

        if ($this->vehicule->update()) {
            return ['status' => 200, 'message' => 'Véhicule mis à jour'];
        }
        return ['status' => 400, 'message' => 'Échec de la mise à jour'];
    }



    private function deleteVehicule($id) {
        $this->vehicule->id = $id;
        
        $transfert = new Transfert();
        $active = $transfert->getActiveByVehicule($id)->fetch();
        
        if ($active) {
            return ['status' => 400, 'message' => 'Impossible de supprimer - véhicule affecté à un transfert actif'];
        }

        if ($this->vehicule->delete()) {
            return ['status' => 200, 'message' => 'Véhicule supprimé'];
        }
        return ['status' => 400, 'message' => 'Échec de la suppression'];
    }

    private function enrichWithCurrentTransfer($vehicule) {
        $transfert = new Transfert();
        $result = $transfert->getActiveByVehicule($vehicule['id']);
        $current_transfert = $result->fetch(PDO::FETCH_ASSOC);
        
        if ($current_transfert) {
            $vehicule['current_transfert'] = $current_transfert;
        }
        
        return $vehicule;
    }
}