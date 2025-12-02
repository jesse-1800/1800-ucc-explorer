<?php

use App\Models\IDProviderPartners;
use App\Models\CatalogManufacturers;
use App\Models\IDProviderPartnerUsers;
use App\Models\UccAssignees;
use App\Models\UccBuyers;
use App\Models\UccContacts;
use App\Models\UccEquipments;
use App\Models\UccFileManager;
use App\Models\UccFilings;
use App\Models\UccProviders;
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

            // Shared databases
            'idp_partners'      => (new IDProviderPartners)::fetch(),
            'idp_partner_users' => (new IDProviderPartnerUsers)::get(),
            'cat_manufacturers' => (new CatalogManufacturers)::fetch(),

            // UCC Data
            'ucc_assignees'     => (new UccAssignees)::where('partner_id',$partner_id)->get(),
            'ucc_buyers'        => UccBuyers::where('partner_id',$partner_id)->get(),
            'ucc_contacts'      => UccContacts::where('partner_id',$partner_id)->get(),
            'ucc_equipments'    => UccEquipments::where('partner_id',$partner_id)->get(),
            'ucc_files'         => UccFileManager::where('partner_id',$partner_id)->get(),
            'ucc_filings'       => UccFilings::where('partner_id',$partner_id)->get(),
            'ucc_providers'     => UccProviders::where('partner_id',$partner_id)->get(),
            'ucc_map_columns'   => json_decode(file_get_contents("$path/ucc_filings.json")),
        );
        return json($data);
    }

    public function find_ucc_data($ucc_filing_id)
    {
        $ucc_filing = UccFilings::find($ucc_filing_id);
        $buyer      = UccBuyers::find($ucc_filing->buyer_id);
        $provider   = UccProviders::find($ucc_filing->provider_id);
        $assignee   = UccAssignees::find($ucc_filing->assignee_id);
        $data = (object) [
            "ucc_filing" => $ucc_filing->data,
            "buyer"      => $buyer->data,
            "contacts"   => UccContacts::where('buyer_id',$buyer->id)->get(),
            "provider"   => $provider ? $provider->data : null,
            "assignee"   => $assignee ? $assignee->data : null,
            "equipments" => UccEquipments::where('ucc_filing_id',$ucc_filing->id)->get(),
        ];
        return json($data);
    }
}