<?php

use App\Models\IDProviderPartners;
use App\Models\CatalogManufacturers;
use App\Models\IDProviderPartnerUsers;
use App\Models\UccFileManager;
use Auth as PropAuth;

class DataController {
    public function fetch($partner_id)
    {
        # Authenticate
        $auth = new PropAuth();
        $auth->isLoggedIn();
        $path = "{$_SERVER['DOCUMENT_ROOT']}/app/config/mapping";
        $data = array(
            'auth0_roles'       => $auth->fetch_roles(),
            'auth0_users'       => $auth->fetch_users(true,true),
            'ucc_files'         => UccFileManager::where('partner_id',$partner_id)->get(),
            'ucc_map_columns'   => [
                'buyers'     => json_decode(file_get_contents("$path/buyers.json")),
                'equipments' => json_decode(file_get_contents("$path/equipments.json")),
                'lenders'    => json_decode(file_get_contents("$path/lenders.json")),
                'lessors'    => json_decode(file_get_contents("$path/lessors.json")),
            ],
            'idp_partners'      => (new IDProviderPartners)::fetch(),
            'idp_partner_users' => (new IDProviderPartnerUsers)::get(),
            'cat_manufacturers' => (new CatalogManufacturers)::get(),
        );
        return json($data);
    }
}