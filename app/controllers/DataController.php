<?php

use App\Models\Addons;
use App\Models\Metrics;
use Auth as PropAuth;
use App\Models\CustomFields;
use App\Models\FieldValues;
use App\Models\Files;
use App\Models\Options;
use App\Models\Partners;
use App\Models\PartnerUsers;
use App\Models\Products;
use App\Models\Proposals;
use App\Models\Providers;
use App\Models\Templates;
use App\Models\ITServiceItems;
use App\Models\ItServiceTiers;
use Kernel\Security\Encryption;

class DataController {
    public function fetch($partner_id)
    {
        # Authenticate
        $auth = new PropAuth();
        $auth->isLoggedIn();
        $catalog = (new Products)::$data;

        $partners = Partners::get();
        foreach ($partners as $i => $partner) {
            $partners[$i]->smtp_password = Encryption::decode($partner->smtp_password);
            $partners[$i]->supported_brands = json_decode($partner->supported_brands);
        }

        $providers = Providers::where('partner_id',$partner_id)->get();
        foreach ($providers as $i => $provider) {
            $providers[$i]->lease_factors = json_decode($provider->lease_factors);
        }

        $metrics = Metrics::get();
        foreach ($metrics as $i => $metric) {
            $metrics[$i]->page_data = json_decode($metric->page_data);
        }

        $custom_fields = CustomFields::where('partner_id',$partner_id)->get();
        foreach ($custom_fields as $i => $field) {
            $custom_fields[$i]->field_options = json_decode($field->field_options);
        }

        $data = array(
            'users'            => $auth->fetch_users(true,true),
            'files'            => Files::where('partner_id',$partner_id)->get(),
            'auth0_roles'      => $auth->fetch_roles(),
            'options'          => Options::get(),

            // Catalog
            'products'          => $catalog['products'],
            'datasets'          => $catalog['datasets'],
            'accessories'       => $catalog['accessories'],
            'lease_terms'       => $catalog['lease_terms'],
            'manufacturers'     => $catalog['manufacturers'],
            'categories'        => $catalog['categories'],

            // Filters
            'printer_categories'=> $catalog['printer_categories'],
            'paper_sizes'       => $catalog['paper_sizes'],
            'connection_types'  => $catalog['connection_types'],
            'media_types'       => $catalog['media_types'],

            'partners'          => $partners,
            'proposals'         => Proposals::get_clean($partner_id),
            'providers'         => $providers,
            'partner_users'     => PartnerUsers::get(),
            'templates'         => Templates::where('partner_id',$partner_id)->get(),
            'addons'            => Addons::where('partner_id',$partner_id)->get(),
            'custom_fields'     => $custom_fields,
            'field_values'      => FieldValues::get(),
            'it_service_items'  => ITServiceItems::where('partner_id',$partner_id)->get(),
            'it_service_tiers'  => ItServiceTiers::where('partner_id',$partner_id)->get(),
            'metrics'           => $metrics,
        );

        return json($data);
    }
}