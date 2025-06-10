<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class FirebaseService
{
    protected $database;



    public function __construct()
    {

        $credentialsFile = config('services.firebase.credentials_file');

    if (!file_exists($credentialsFile)) {
        throw new \Exception("Service account file not found at: {$credentialsFile}");
    }

    $factory = (new Factory)
        ->withServiceAccount($credentialsFile)
        ->withDatabaseUri('https://agrovision-sensor-data-default-rtdb.firebaseio.com/');
    $this->database = $factory->createDatabase();


        $factory = (new Factory)
            ->withServiceAccount(config('services.firebase.credentials_file'))
            ->withDatabaseUri('https://agrovision-sensor-data-default-rtdb.firebaseio.com/'); 
        $this->database = $factory->createDatabase();
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    public function set(string $path, array $data)
    {
        return $this->database->getReference($path)->set($data);
    }

    public function get(string $path)
    {
        return $this->database->getReference($path)->getValue();
    }
}
