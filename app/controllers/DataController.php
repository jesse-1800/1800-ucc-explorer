<?php

use App\Models\IDProviderPartners;
use Auth as PropAuth;

class DataController {
    public function fetch($partner_id)
    {
        # Authenticate
        $auth = new PropAuth();
        $auth->isLoggedIn();
        $data = array(
            'users'        => $auth->fetch_users(true,true),
            'idp_partners' => IDProviderPartners::fetch(),
            'auth0_roles'  => $auth->fetch_roles(),
        );
        return json($data);
    }
}