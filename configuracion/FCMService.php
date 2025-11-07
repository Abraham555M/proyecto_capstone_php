<?php
// RUTA: ../../configuracion/FCMService.php

// Incluye la clase Google Client y Guzzle
require_once(__DIR__ . '/../vendor/autoload.php'); 
use GuzzleHttp\Client; 
use Google\Client as Google_Client;

// 🚨 SUSTITUCIÓN CRÍTICA 1: Nombre de tu archivo JSON
const FIREBASE_CREDENTIALS_PATH = __DIR__ . '/../credenciales/clavefirebase.json';
const FIREBASE_SCOPES = ['https://www.googleapis.com/auth/firebase.messaging'];
const FCM_ENDPOINT = 'https://fcm.googleapis.com/v1/projects/';


/**
 * Obtiene un token de acceso OAuth 2.0 usando el archivo de credenciales de servicio.
 */
function getAccessToken() {
    try {
        // Verifica si el archivo JSON de credenciales existe.
        if (!file_exists(FIREBASE_CREDENTIALS_PATH)) {
            error_log("FCM V1 ERROR: JSON file NOT found at path: " . FIREBASE_CREDENTIALS_PATH);
            throw new Exception("JSON file not found at expected path.");
        }
        
        // Carga el cliente y las credenciales
        $client = new Google_Client();
        $client->setAuthConfig(FIREBASE_CREDENTIALS_PATH);
        $client->setScopes(FIREBASE_SCOPES);
        
        // Obtiene el token de acceso
        $token = $client->fetchAccessTokenWithAssertion();
        
        if (empty($token['access_token'])) {
            throw new Exception("No se pudo obtener el token de acceso.");
        }
        
        return $token['access_token'];

    } catch (Exception $e) {
        error_log("Error al obtener Access Token: " . $e->getMessage());
        return false;
    }
}


/**
 * Envía una notificación FCM usando la API HTTP v1 y autenticación OAuth 2.0.
 */
function enviarNotificacionFCM($token_destino, $titulo, $cuerpo, $data_payload = []) {
    
    // 🚨 SUSTITUCIÓN CRÍTICA 2: ID de tu proyecto de Firebase
    $project_id = 'capzone-8faa4'; 
    $access_token = getAccessToken();

    if (!$access_token) {
        return array("status" => "fcm_error", "message" => "No se pudo autenticar para FCM v1.");
    }
    
    if (empty($token_destino)) {
        return array("status" => "error", "message" => "Token FCM de destino vacío.");
    }

    $url = FCM_ENDPOINT . $project_id . '/messages:send';
    
    $message = [
        'message' => [
            'token' => $token_destino,
            'notification' => [
                'title' => $titulo,
                'body' => $cuerpo,
            ],
            'data' => $data_payload 
        ]
    ];

    try {
        $client = new Client();
        $response = $client->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ],
            'json' => $message,
            'verify' => false, 
        ]);

        $http_code = $response->getStatusCode();
        $result = $response->getBody()->getContents();

        if ($http_code !== 200) {
            return array("status" => "fcm_error", "message" => "FCM v1 Error: HTTP $http_code", "response" => json_decode($result, true));
        }

        return array("status" => "fcm_sent", "message" => "Notificación FCM v1 enviada con éxito.", "response" => json_decode($result, true));

    } catch (\GuzzleHttp\Exception\RequestException $e) {
         $error_message = $e->getMessage();
         if ($e->hasResponse()) {
             $error_message .= " - Response: " . $e->getResponse()->getBody()->getContents();
         }
         return array("status" => "fcm_error", "message" => "Error de red/petición Guzzle: " . $error_message);
    } catch (Exception $e) {
        return array("status" => "fcm_error", "message" => "Excepción general: " . $e->getMessage());
    }
}