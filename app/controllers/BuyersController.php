<?php

use App\Models\UccAssignees;
use App\Models\UccBuyers;
use App\Models\UccContacts;
use App\Models\UccEquipments;
use App\Models\UccFileManager;
use App\Models\UccProviders;
use Kernel\Database\Database;

class BuyersController
{
    public function paginate($partner_id)
    {
        $database  = new Database();
        $curr_page = (int)($_POST['curr_page'] ?? 1);
        $page_size = (int)($_POST['page_size'] ?? 100);
        $offset    = ($curr_page - 1) * $page_size;
        $sort_map  = array(
            'id'             => 'id',
            'buyer_company'  => 'buyer_company',
            'buyer_city'     => 'buyer_city',
            'buyer_state'    => 'buyer_state',
            'buyer_sic_desc' => 'buyer_sic_desc',
        );
        $sort_by   = $sort_map[$_POST['sort_by']] ?? 'id';
        $order_by  = $_POST['order_by'];
        $total     = $database->row("SELECT COUNT(*) AS 'total' FROM ucc_buyers WHERE partner_id='$partner_id'")->total;

        // where queries
        $where    = array();
        $search   = trim($_POST['search'] ?? '');
        $city     = trim($_POST['city'] ?? '');
        $state    = trim($_POST['state'] ?? '');
        $industry = trim($_POST['industry'] ?? '');

        if ($partner_id !== "") {
            $where[] = "partner_id = '$partner_id'";
        }
        if ($search !== "") {
            $search_input = addslashes($search);
            $where[] = ("
                (
                    id LIKE '%$search_input%' OR
                    buyer_company LIKE '%$search_input%' OR
                    buyer_city LIKE '%$search_input%' OR
                    buyer_state LIKE '%$search_input%' OR
                    buyer_sic_desc LIKE '%$search_input%'
                )
            ");
        }
        if ($city !== "") {
            $where[] = "buyer_city = '$city'";
        }
        if ($state !== "") {
            $where[] = "buyer_state = '$state'";
        }
        if ($industry !== ""){
            $where[] = "buyer_sic_desc = '$industry'";
        }

        $where_sql = count($where) ? "WHERE " .implode(" AND ", $where) : "";
        $result = $database->query("
          SELECT * FROM ucc_buyers
          $where_sql
          ORDER BY $sort_by $order_by
          LIMIT $page_size
          OFFSET $offset;
        ");

        return json(['items' => $result, 'total' => $total]);
    }

    public function filter_data()
    {
        return json([
            'industries' => UccBuyers::distinct('buyer_sic_desc')->get(),
            'cities' => UccBuyers::distinct('buyer_city')->get(),
        ]);
    }

    public function buyer_profile($buyer_id)
    {
        $buyer = UccBuyers::find($buyer_id);
        $contacts = UccContacts::where('buyer_id',$buyer_id)->get();
        $ucc_filings = UccContacts::where('buyer_id',$buyer_id)->get();

        foreach ($ucc_filings as $ucc) {
            $ucc->equipments = UccEquipments::where('ucc_filing_id',$ucc->id)->get();
            $ucc->assignee   = UccAssignees::find($ucc->assignee_id);
            $ucc->provider   = UccProviders::find($ucc->provider_id);
        }

        return json([
            'buyer'       => $buyer,
            'contacts'    => $contacts,
            'ucc_filings' => $ucc_filings,
        ]);
    }
}