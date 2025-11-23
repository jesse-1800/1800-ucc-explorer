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
            'ucc_columns'       => json_decode(file_get_contents("$path/ucc_filings.json")),
            'idp_partners'      => (new IDProviderPartners)::fetch(),
            'idp_partner_users' => (new IDProviderPartnerUsers)::get(),
            'cat_manufacturers' => (new CatalogManufacturers)::get(),
        );
        return json($data);
    }
}