<?php
// controller/address_suggestions.php
header('Content-Type: application/json');

// *** CONFIGURACIÓN DE TU API KEY DE GOOGLE MAPS ***
$googleMapsApiKey = 'API KEY DE GOOGLE MAPS';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $query = $data['query'] ?? '';

    if (!empty($query)) {
        $suggestions = [];

        // -----------------------------------------------------------
        // 1. LLAMADA A LA PLACES API (Autocomplete) PARA SUGERENCIAS
        // -----------------------------------------------------------
        $autocompleteUrl = "https://maps.googleapis.com/maps/api/place/autocomplete/json?" .
                           "input=" . urlencode($query) .
                           "&language=es" .
                           "&components=country:pe" .
                           "&key=" . $googleMapsApiKey;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $autocompleteUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode == 200) {
            $apiResponse = json_decode($response, true);

            if (isset($apiResponse['predictions']) && !empty($apiResponse['predictions'])) {
                foreach ($apiResponse['predictions'] as $prediction) {
                    $suggestions[] = [
                        'address' => $prediction['description'],
                        'place_id' => $prediction['place_id'] // Importante para Geocoding más preciso
                    ];
                }
            }
        } else {
            error_log("Error al llamar a Places API: HTTP " . $httpCode . " - " . $response);
        }

        echo json_encode(['places' => $suggestions]);

    } else {
        echo json_encode(['error' => 'No se proporcionó ninguna consulta para sugerencias.']);
    }
} else {
    echo json_encode(['error' => 'Método no permitido.']);
}
