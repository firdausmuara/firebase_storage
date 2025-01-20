<?php

use Google\Cloud\Storage\StorageClient;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;

require __DIR__.'/vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\Storage;

class Firebase_engine
{
    // static function factory()
    // {
    //     $factory = (new Factory)
    //         ->withServiceAccount('datangg_firebase_admin.json')
    //         ->withProjectId("datangg");
                    
    //     return $factory;
    // }

    static function factoryServiceAccount()
    {
        $json_content = "{}";
        if (defined("APPPATH")) {
            $json_content = file_get_contents(APPPATH . '../datangg_firebase_admin.json');
        } else {
            $json_content = file_get_contents('./datangg_firebase_admin.json');
        }
        return (new Factory)->withServiceAccount(
            json_decode($json_content, true)
        );
    }
 
    static function factoryProjectId()
    {
        $factory = (new Factory)->withProjectId("datangg");
        return $factory;
    }

    static function getAccessToken()
    {
        $credential = new ServiceAccountCredentials(
            "https://www.googleapis.com/auth/firebase.storage",
            json_decode(file_get_contents(APPPATH . '../datangg_firebase_admin.json'), true)
        );

        $accessToken = $credential->fetchAuthToken(HttpHandlerFactory::build());

        return $accessToken;
    }

    static function uploadFile($bucketName, $fileSource, $filePath)
    {       
        try {
            // Initialize Firebase
            $factory = self::factoryServiceAccount();
            $storage = $factory->createStorage();
    
            // Specify the file to upload
            // $fileSource = '/Applications/XAMPP/xamppfiles/htdocs/bis-penggajian/pinjamanDaus.pdf'; // Path to the file you want to upload
            $bucket = $storage->getBucket($bucketName); // Get the default storage bucket
    
            // Upload the file
            $object = $bucket->upload(
                fopen($fileSource, 'r'), // Open the file for reading
                [
                    'name' => $filePath // Specify the path in Firebase Storage
                ]
            );
            
            return [
                'success' => true,
                'message' => "OK",
                'data'    => [
                    "gcsUri" => $object->gcsUri(),
                    "identity" => $object->identity(),
                    "info" => $object->info(),
                    "name" => $object->name(),
                    "signedUploadUrl" => $object->signedUploadUrl(new \DateTime('+1 hour')),
                    "signedUrl" => $object->signedUrl(new \DateTime('+1 hour')),
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        } 
    }

    static function downloadFile($bucketName, $filePath, $localFilePath)
    {
        // Initialize Firebase
        $factory = self::factoryServiceAccount();
        $storage = $factory->createStorage();

        // Reference to the storage bucket
        $bucket = $storage->getBucket($bucketName);

        // Specify the file path in Firebase Storage
        $filePath = $filePath; // Path in Firebase Storage
        $localFilePath = $localFilePath; // Local path to save the file

        // Download the file
        $object = $bucket->object($filePath);
        $object->downloadToFile($localFilePath);

        echo "File downloaded successfully to: " . $localFilePath;
    }

    static function printOutFile(string $bucketName, string $filePath = "")
    {
        try {
            $factory = self::factoryServiceAccount();
            $storage = $factory->createStorage();
 
            $bucket = $storage->getBucket($bucketName);
            $object = $bucket->object($filePath);
 
            $file_type = $object->info()['contentType'] ?? "";
            $res = $object->downloadAsString();
 
            ob_clean();
            header('content-type: ' . $file_type);
            echo $res;
        } catch (Exception $e) {
            http_response_code(500);
        }
    }

    static function getPublicUrl(string $bucketName, string $filePath = "")
    {
        try {
            $factory = self::factoryServiceAccount();
            $storage = $factory->createStorage();
 
            $bucket = $storage->getBucket($bucketName);
            $object = $bucket->object($filePath);
            $public_url = $object->signedUrl(new \DateTime('+ 1 hour'));
            return $public_url;
        } catch (Exception $e) {
            return "";
        }
    }
}

/*
    |----------------------------------------------------------------------
    |   Upload File to Firebase Storage
    |----------------------------------------------------------------------
*/
try {

    // Step 1: Initialize Firebase
    $factory = (new Factory)->withServiceAccount('datangg_firebase_admin.json');
    $storage = $factory->createStorage();

    // Step 2: Specify the file to upload
    $filePath = '/Applications/XAMPP/xamppfiles/htdocs/firebase_storage/pinjamanDaus.pdf'; // Path to the file you want to upload
    $bucket = $storage->getBucket('staging-datangg'); // Get the default storage bucket
    // print_r($bucket);
    // Step 3: Upload the file
    $object = $bucket->upload(
        fopen($filePath, 'r'), // Open the file for reading
        [
            'name' => 'penggajian/uploads/pinjaman/lampiran/file.pdf' // Specify the path in Firebase Storage
        ]
    );

    // Step 4: Output the public URL of the uploaded file
    $publicUrl = $object->signedUrl(new \DateTime('+1 hour')); // Generate a signed URL valid for 1 hour
    echo "File uploaded successfully. Access it at: " . $publicUrl;
    // print_r($accessToken);
} catch (\Throwable $th) {
    //throw $th;
}

/*
|----------------------------------------------------------------------
|   Download/Get File from Firebase Storage
|----------------------------------------------------------------------
*/
try {
    //code...
} catch (\Throwable $th) {
    //throw $th;
}