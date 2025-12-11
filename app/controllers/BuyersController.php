<?php

use App\Models\UccBuyers;
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

    public function find_buyer($buyer_id)
    {
        $buyer = UccBuyers::find($buyer_id);
        if ($buyer) return json($buyer->data);
        return json([
            'result' => false,
            'message' => "Buyer not found."
        ]);
    }
}