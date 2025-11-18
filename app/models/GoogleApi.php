<?php namespace App\Models;

use Google\Exception;
use Google_Client;
use Google_Service_Sheets;

class GoogleApi {

    protected $client;
    protected $service;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setApplicationName('Sheets Editor');
        $this->client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
        $this->client->setAuthConfig("{$_SERVER['DOCUMENT_ROOT']}/app/config/google-svc-key.json");
        $this->client->setAccessType('offline');
        $this->service = new Google_Service_Sheets($this->client);
    }

    private function getService()
    {
        return $this->service;
    }

    public function getSheetData($spreadsheetId, $sheetName)
    {
        try {
            $response = $this->service->spreadsheets_values->get(
                $spreadsheetId,
                $sheetName
            );
            return $response->getValues();
        } catch (Exception $e) {
            /*$error_obj = json_decode($e->getMessage());
            return json([
                'result' => false,
                'message' => $error_obj->error->message,
            ]);*/
            // We'll return empty array instead
            return [];
        }
    }

    public function updatePrice($spreadsheetId, $sheetName, $rowIndex, $price)
    {
        try {
            $range = "{$sheetName}!D{$rowIndex}"; // D is the Price column
            $body = new \Google_Service_Sheets_ValueRange([
                'values' => [[$price]]
            ]);
            $params = ['valueInputOption' => 'USER_ENTERED'];

            return $this->service->spreadsheets_values->update(
                $spreadsheetId,
                $range,
                $body,
                $params
            );
        } catch (Exception $e) {
            return json([
                'result' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
