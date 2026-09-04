<?php
/**
 * Cliente HTTP para consumir la API REST del Sistema de Piezas Arqueológicas.
 * Centraliza las peticiones cURL hacia el backend FastAPI.
 */

define('API_BASE_URL', rtrim(getenv('API_URL') ?: 'http://api:8000', '/'));

/**
 * Ejecuta una petición HTTP contra la API REST.
 *
 * @param string $method Método HTTP (GET, POST, PUT, DELETE)
 * @param string $endpoint Ruta relativa (ej. /piezas)
 * @param array|null $data Datos para el cuerpo de la petición (JSON)
 * @return array ['success' => bool, 'status' => int, 'data' => mixed, 'error' => string|null]
 */
function api_request(string $method, string $endpoint, ?array $data = null): array {
    $url = API_BASE_URL . $endpoint;
    
    // Si cURL está disponible, lo usamos (método principal)
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json'
        ];

        if ($data !== null) {
            $jsonPayload = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $responseBody = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return [
                'success' => false,
                'status' => 0,
                'data' => null,
                'error' => "Error de conexión con la API en {$url}: {$curlError}"
            ];
        }

        $decoded = json_decode($responseBody, true);
        $isSuccess = ($httpCode >= 200 && $httpCode < 300);

        return [
            'success' => $isSuccess,
            'status' => $httpCode,
            'data' => $decoded,
            'error' => $isSuccess ? null : extract_api_error($decoded, $httpCode)
        ];
    }

    // Fallback utilizando stream_context si cURL no estuviera activo
    $opts = [
        'http' => [
            'method' => strtoupper($method),
            'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
            'ignore_errors' => true,
            'timeout' => 10
        ]
    ];

    if ($data !== null) {
        $opts['http']['content'] = json_encode($data);
    }

    $context = stream_context_create($opts);
    $responseBody = @file_get_contents($url, false, $context);

    if ($responseBody === false) {
        return [
            'success' => false,
            'status' => 0,
            'data' => null,
            'error' => "No se pudo contactar con la API en {$url}."
        ];
    }

    $httpCode = 200;
    if (isset($http_response_header[0])) {
        preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $matches);
        $httpCode = isset($matches[1]) ? (int)$matches[1] : 200;
    }

    $decoded = json_decode($responseBody, true);
    $isSuccess = ($httpCode >= 200 && $httpCode < 300);

    return [
        'success' => $isSuccess,
        'status' => $httpCode,
        'data' => $decoded,
        'error' => $isSuccess ? null : extract_api_error($decoded, $httpCode)
    ];
}

/**
 * Extrae un mensaje de error legible a partir de la respuesta de FastAPI.
 */
function extract_api_error($decoded, int $httpCode): string {
    if (is_array($decoded)) {
        if (isset($decoded['detail'])) {
            if (is_string($decoded['detail'])) {
                return $decoded['detail'];
            }
            if (is_array($decoded['detail'])) {
                // Errores de validación Pydantic
                $messages = [];
                foreach ($decoded['detail'] as $err) {
                    $loc = isset($err['loc']) ? implode(' -> ', $err['loc']) : 'campo';
                    $msg = $err['msg'] ?? 'inválido';
                    $messages[] = "{$loc}: {$msg}";
                }
                return implode('; ', $messages);
            }
        }
        if (isset($decoded['message'])) {
            return $decoded['message'];
        }
    }
    return "Error en la petición (Código HTTP {$httpCode})";
}

// Funciones específicas para el CRUD de piezas

function api_get_piezas(): array {
    return api_request('GET', '/piezas');
}

function api_get_pieza(int $id): array {
    return api_request('GET', "/piezas/{$id}");
}

function api_create_pieza(array $data): array {
    return api_request('POST', '/piezas', $data);
}

function api_update_pieza(int $id, array $data): array {
    return api_request('PUT', "/piezas/{$id}", $data);
}

function api_delete_pieza(int $id): array {
    return api_request('DELETE', "/piezas/{$id}");
}
