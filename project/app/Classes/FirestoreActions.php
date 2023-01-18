<?php

namespace App\Classes;

use Illuminate\Support\Str;


class FirestoreActions
{
    public function __construct()
    {
        //return "construct function was initialized.";
    }

    /**
     * @param $restaurant = string code of restaurant
     * @param $method = string value 'PATCH'
     * @return returns array with status (integer) and msg (string)
     */
    public function update_refresh_code(string $restaurant)
    {
        $random_str = Str::random(10);
        
        $firestore_data  = [
            'refreshCode' => ["stringValue" => $random_str],             
        ];

        $fireData = ["fields" => (object)$firestore_data];

        $json = json_encode($fireData);

        $firestore_key = env('FIREBASE_KEY');

        $project_id = env('FIREBASE_PROJECT_ID');


        $url = "https://firestore.googleapis.com/v1beta1/projects/$project_id/databases/(default)/documents/vendor/" . $restaurant;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json),
                'X-HTTP-Method-Override: PATCH'
            ),
            CURLOPT_URL => $url . '?key=' . $firestore_key,
            CURLOPT_USERAGENT => 'cURL',
            CURLOPT_POSTFIELDS => $json
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $response_array = json_decode($response, TRUE);
        return $response_array;
    }

}
