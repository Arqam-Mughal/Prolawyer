<?php

namespace App\Services;

class EncryptionService
{
    public function encryptDataToString($simple, $key)
    {
        if (!empty($key)) {
            $keyBytes = mb_convert_encoding($key, 'UTF-8'); // Example encryption key
            $result = mb_convert_encoding($simple, 'UTF-8'); // The result to encrypt
            $resultIv = openssl_random_pseudo_bytes(16); // Generate a new initialization vector for the result
            $resultCipher = "AES-128-CBC";
            $encryptedResult = openssl_encrypt($result, $resultCipher, $keyBytes, OPENSSL_RAW_DATA, $resultIv);
            $base64Result = base64_encode($encryptedResult); // Encode the result as base64
            $base64ResultIv = base64_encode($resultIv); // Encode the IV as base64
            return $base64Result . ":" . $base64ResultIv;
        } else {
            return $simple;
        }
    }

    public function decryptDataToJson($json, $key)
    {
        if ($key != null && !empty($key)) {
            $keyBytes = mb_convert_encoding($key, 'UTF-8'); // Example encryption key
            $jsonObject = json_decode($json);

            // Handle invalid JSON input
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException("Invalid JSON input");
            }

            if (!isset($jsonObject->data) || !isset($jsonObject->iv)) {
                throw new \InvalidArgumentException("Invalid JSON structure");
            }

            $base64Result = $jsonObject->data; // Extract the encrypted data as a base64 string
            $base64ResultIv = $jsonObject->iv; // Extract the initialization vector as a base64 string
            $encryptedResult = base64_decode($base64Result); // Decode the encrypted data from base64
            $resultIv = base64_decode($base64ResultIv); // Decode the initialization vector from base64
            $resultCipher = 'AES-128-CBC';
            $decryptedResult = openssl_decrypt($encryptedResult, $resultCipher, $keyBytes, OPENSSL_RAW_DATA, $resultIv);
            $decryptedString = mb_convert_encoding($decryptedResult, 'UTF-8', 'auto'); // Convert the decrypted data back to a string

            return $decryptedString;
        } else {
            return $json;
        }
    }
}
