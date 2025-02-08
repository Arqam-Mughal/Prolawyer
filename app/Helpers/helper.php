<?php
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

if (!function_exists('_lang')) {
	function _lang($string = '') {
		return trans($string);
	}
}

function uploadsDir($path = '')
{
    return $path != '' ? 'uploads/' . $path . '/' : 'uploads/';
}

function encryptDataToString($simple, $key) {
    if (!empty($key)) {
        $keyBytes = utf8_encode($key); // Example encryption key
        $result = utf8_encode($simple); // The result to encrypt
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

function decryptDataToJson($json, $key) {
    if ($key != null && !empty($key)) {
        $keyBytes = utf8_encode($key); // Example encryption key
        $jsonObject = json_decode($json); // Parse the input JSON data
        $base64Result = isset($jsonObject->data) ? $jsonObject->data : ''; // Extract the encrypted data as a base64 string
        $base64ResultIv = isset($jsonObject->iv) ? $jsonObject->iv : ''; // Extract the initialization vector as a base64 string
        $encryptedResult = base64_decode($base64Result); // Decode the encrypted data from base64
        $resultIv = base64_decode($base64ResultIv); // Decode the initialization vector from base64
//         $resultCipher = openssl_cipher_iv_length('AES-128-CBC');
//         $resultKeySpec = openssl_cipher_iv_length('AES-128-CBC');
//         $resultIvSpec = openssl_cipher_iv_length('AES-128-CBC');
//         $decryptedResult = openssl_decrypt($encryptedResult, 'AES-128-CBC', $keyBytes, OPENSSL_RAW_DATA, $resultIv);
//         $decryptedString = utf8_decode($decryptedResult); // Convert the decrypted data back to a string

        //Gaurav added to fix the text
        $resultCipher = "AES-128-CBC";
		$resultKeySpec = $keyBytes;
		$resultIvSpec = $resultIv;
        $decryptedResult = openssl_decrypt($encryptedResult, $resultCipher, $resultKeySpec, OPENSSL_RAW_DATA, $resultIvSpec);
        $decryptedString = mb_convert_encoding($decryptedResult, "UTF-8", "auto"); // Convert the decrypted data back to a string
        //closing


        return $decryptedString;
    } else {
        return $json;
    }

    // Define the function
    function moduleStatusCheck($moduleName) {
        // Implementation logic
        return true; // Example implementation
    }

}

if (!function_exists('moduleStatusCheck')) {
    function moduleStatusCheck($module)
    {
        try {
            $haveModule = \Modules\ModuleManager\Entities\Module::where('name', $module)->first();
            if (empty($haveModule)) {
                return false;
            }
            $moduleStatus = $haveModule->status;

            $is_module_available = 'Modules/' . $module . '/Providers/' . $module . 'ServiceProvider.php';

            if (file_exists($is_module_available)) {

                $moduleCheck = \Nwidart\Modules\Facades\Module::find($module)->isEnabled();

                if (!$moduleCheck) {
                    return false;
                }

                if ($moduleStatus == 1) {
                    $is_verify = \Modules\ModuleManager\Entities\InfixModuleManager::where('name', $module)->first();

                    if (!empty($is_verify->purchase_code)) {
                        return true;
                    }
                }
            }


            return false;
        } catch (\Throwable $th) {

            return false;
        }

    }
}
