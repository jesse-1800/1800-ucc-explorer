<?php
use Auth as PropAuth;
use App\Models\Files;

class DataController {
    public function fetch($partner_id)
    {
        # Authenticate
        $auth = new PropAuth();
        $auth->isLoggedIn();
        $data = array(
            'users'            => $auth->fetch_users(true,true),
            'files'            => Files::where('partner_id',$partner_id)->get(),
            'auth0_roles'      => $auth->fetch_roles(),
        );
        return json($data);
    }
}