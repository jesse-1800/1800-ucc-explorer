<?php namespace App\Models;
use Session;

class Products
{
    /**
    - result
    - errors
    - datasets
    - products
    - accessories
    - lease_terms
    - manufacturers
    - product_pricing
    - categories
    - standard_function
    - product_reviews
    - quotes
     */
    public static $data = [];
    private static Environment $environment;
    private static CatalogApi $catalog_api;

    public function __construct()
    {
        self::instantiate();
    }

    private function instantiate()
    {
        self::$environment = new Environment();
        self::$catalog_api = new CatalogApi();
        self::$data = json_decode(Session::get('catalog_data')??'[]',true);

        if (empty(self::$data)) {
            $catalog_data = self::$catalog_api->fetch('/quotetool/fetch-data');
            Session::set('catalog_data', json_encode($catalog_data));
            self::$data = $catalog_data;
        }
    }

    public function __get($key)
    {
        return $this->data[$key] ?? null;
    }

    public static function distinct_products($proposal) {
        $distinct_products = [];
        $existing_ids = [];
        foreach ($proposal->cart_items as $item) {
            if (!in_array($item->product->id, $existing_ids)) {
                $distinct_products[] = $item->product;
                $existing_ids[] = $item->product->id;
            }
        }
        return $distinct_products;
    }

    public static function product_image($product_object)
    {
        return self::$environment->get('VITE_CATALOG_BASE_URL')."/storage/uploads/products/product-img/$product_object->product_image";
    }

    public static function attachments($item)
    {
        $attachments = array();
        foreach ($item->accessories as $accessory) {
            if ($accessory->dataset_attached == true) {
                $attachments[] = $accessory;
            }
        }
        return $attachments;
    }

    public static function products_by_partner($id)
    {
        $my_brands = json_decode(Partners::find($id)->data->supported_brands);
        $my_products = array();
        foreach (self::$data['products'] as $product) {
            if (in_array($product['manufacturer_id'],$my_brands)) {
                array_push($my_products, $product);
            }
        }
        return $my_products;
    }

    public static function product_brand_name($manufacturer_id)
    {
        foreach (self::$data['manufacturers'] as $brand) {
            if ($brand['id'] == $manufacturer_id) {
                return $brand['name'];
            }
        }
        return '-';
    }

    public static function find_product($product_id)
    {
        (new Products)->instantiate();

        $find_product = null;
        foreach (self::$data['products'] as $product) {
            if ($product['id'] == $product_id) {
                $find_product = $product;
                break;
            }
        }
        return $find_product;
    }

    public static function find_accessory($accessory_id)
    {
        foreach (self::$data['accessories'] as $accessory) {
            if ($accessory['id'] == $accessory_id) {
                return (object) $accessory;
            }
        }
    }

    public static function find_dataset($accessory_id)
    {
        foreach (self::$data['datasets'] as $dataset) {
            if ($dataset['accessory_id'] == $accessory_id) {
                return (object) $dataset;
            }
        }
    }
}
