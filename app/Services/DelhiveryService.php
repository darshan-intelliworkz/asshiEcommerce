<?php
 
namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DelhiveryService
{
    protected $token;
    protected $baseUrl;
 
    public function __construct()
    {
        $this->token = env('DELHIVERY_API_TOKEN'); // store token in .env
        $this->baseUrl = 'https://staging-express.delhivery.com/api/dc/fetch/serviceability';
    }
 
    public function getPincodeLocations(array $pincodes)
    {
       
        $response = Http::withHeaders([
            'Authorization' => 'Token ' . $this->token,
            'Accept' => 'application/json',
        ])->get($this->baseUrl, query: [
            'filter_codes' => implode(',', $pincodes)
        ]);
 
        if ($response->failed()) {
            throw new \Exception("Delhivery API error: " . $response->body());
        }
 
        return $response->json();
    }

    public function schedulePickup($ordercounts)
    {
        $url = "https://staging-express.delhivery.com/fm/request/new/";

        $payload = [
            "pickup_time" => "20:00:00",
            "pickup_date" => now()->addday(2)->format('Y-m-d'),
            "pickup_location" => "PRADASHIMART SURFACE",
            "expected_package_count" => $ordercounts,
        ];

        // Log::info("📤 Sending Pickup Request Payload: " . json_encode($payload));

        $response = Http::withHeaders([
            "Authorization" => "Token " . env("DELHIVERY_API_TOKEN_TEST"),
            "Content-Type" => "application/json"
        ])->post($url, $payload);

        // Log::info("📥 API Response: " . $response->body());

        if (!$response->successful()) {
            log::info("111111111111");
            throw new \Exception("Delhivery Pickup API Error: " . $response->body());
        }
        
        return $response->json();
    }

}