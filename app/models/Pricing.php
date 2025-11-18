<?php namespace App\Models;

/**
 * Class Pricing
 *
 * @package App\Models
 */
class Pricing
{
    private GoogleApi $google_api;

    public function __construct()
    {
        $this->google_api = new GoogleApi();
    }

    public function products($partner_id)
    {
        $partner = Partners::find($partner_id);
        $sheet_id = $this->extract_sheet_id($partner->products_price_url);
        $data = $this->google_api->getSheetData($sheet_id, "Products!A1:Z");

        if (empty($data) || count($data) < 2) {
            return [];
        }

        $headers = array_map('trim', $data[0]);
        $products = [];

        foreach (array_slice($data, 1) as $row) {
            $products[] = array_combine(
                $headers, $row + array_fill(0,count($headers),'')
            );
        }

        return $products;
    }

    public function accessories($partner_id)
    {
        $partner = Partners::find($partner_id);
        $sheet_id = $this->extract_sheet_id($partner->products_price_url);
        $data = $this->google_api->getSheetData($sheet_id, "Accessories!A1:Z");

        if (empty($data) || count($data) < 2) {
            return [];
        }

        $headers = array_map('trim', $data[0]);
        $products = [];

        foreach (array_slice($data, 1) as $row) {
            $products[] = array_combine(
                $headers, $row + array_fill(0,count($headers),'')
            );
        }

        return $products;
    }

    private function extract_sheet_id($url) {
        $regex = '/\/d\/([a-zA-Z0-9-_]+)/';
        if (preg_match($regex, $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
}