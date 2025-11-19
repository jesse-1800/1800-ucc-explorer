<?php

use App\Models\IDProviderPartners;
use App\Models\IDProviderPartnerUsers;
use Auth as PropAuth;

class DataController {
    public function fetch($partner_id)
    {
        # Authenticate
        $auth = new PropAuth();
        $auth->isLoggedIn();
        $data = array(
            'auth0_roles'       => $auth->fetch_roles(),
            'auth0_users'       => $auth->fetch_users(true,true),
            'ucc_files'         => [],
            'idp_partners'      => (new IDProviderPartners)::get(),
            'idp_partner_users' => (new IDProviderPartnerUsers)::get(),
        );
        return json($data);
    }
}