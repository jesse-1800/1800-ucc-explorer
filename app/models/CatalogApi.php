<?php namespace App\Models;

use GuzzleHttp\Client;

class CatalogApi {
    protected Environment $environment;

    public function __construct()
    {
        $this->environment = new Environment();
    }

    public function fetch($endpoint)
    {
        $client = new Client([
            'base_uri' => $this->environment->get('VITE_CATALOG_BASE_URL'),
            'headers' => [
                'X-Api-Key' => $this->environment->get('VITE_CATALOG_API_KEY'),
                'Accept'    => 'application/json'
            ]
        ]);
        $response = $client->post($endpoint);
        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }
}