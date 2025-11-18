<?php

use App\Models\Options;
use App\Models\Partners;
use Auth0\SDK\Exception\ConfigurationException;
use Kernel\Security\Encryption;

/**
 * Default controller
 * Class HomeController
 */
class PartnersController
{
    public function fetch()
    {
        (new Auth)->isLoggedIn();
        $partners = Partners::get();
        foreach ($partners as $i => $partner) {
            $partners[$i]->smtp_password = Encryption::decode($partner->smtp_password);
            $partners[$i]->supported_brands = json_decode($partner->supported_brands);
        }
        return json($partners);
    }

    public function store()
    {
        (new Auth)->isLoggedIn();
        $form = json_decode($_POST['partner']);
        $form->smtp_password = Encryption::encode($form->smtp_password);
        $form->supported_brands = json_encode($form->supported_brands);
        $form->is_active  = 1;
        $form->created_at = date('Y-m-d');
        $form->updated_at = date('Y-m-d');
        $is_inserted = Partners::insert($form);

        $user_id = $_POST['user_id'];
        $partner_id = Partners::lastInsertedId();

        # Insert Global Overage Rates
        Options::add_option(
            $user_id,
            $partner_id,
            'proposals',
            'Global Overage Rate (Black)',
            '0'
        );
        Options::add_option(
            $user_id,
            $partner_id,
            'proposals',
            'Global Overage Rate (Color)',
            '0'
        );

        # Insert Cost Per Print Rates
        Options::add_option(
            $user_id,
            $partner_id,
            'proposals',
            'Cost Per Print (Black)',
            '0'
        );
        Options::add_option(
            $user_id,
            $partner_id,
            'proposals',
            'Cost Per Print (Color)',
            '0'
        );

        json(['result' => $is_inserted]);
    }

    public function update()
    {
        (new Auth)->isLoggedIn();
        $form = json_decode($_POST['partner']);
        $form->smtp_password = Encryption::encode($form->smtp_password);
        $form->supported_brands = json_encode($form->supported_brands);
        $form->updated_at = date('Y-m-d');
        $result = Partners::update($form,$form->id);
        json(['result' => $result,'message' => Partners::errors()]);
    }
}
