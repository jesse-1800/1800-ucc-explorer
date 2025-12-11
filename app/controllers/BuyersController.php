<?php

use App\Models\UccBuyers;
use Kernel\Database\Database;

class BuyersController
{
    public function paginate()
    {
        $db = new Database();

        $curr_page = (int)($_POST['curr_page'] ?? 1);
        $page_size = (int)($_POST['page_size'] ?? 100);
        $offset    = ($curr_page - 1) * $page_size;
        $sort_map  = array(
            'id'             => 'id',
            'buyer_company'  => 'buyer_company',
            'buyer_state'    => 'buyer_state',
            'buyer_sic_desc' => 'buyer_sic_desc',
        );
        $sort_by   = $sort_map[$_POST['sort_by']] ?? 'id';
        $order_by  = $_POST['order_by'];
        $total     = $db->row("SELECT COUNT(*) AS 'total' FROM ucc_buyers")->total;

        // where queries
        $where    = array();
        $search   = trim($_POST['search'] ?? '');
        $state    = trim($_POST['state'] ?? '');
        $industry = trim($_POST['industry'] ?? '');

        if($search !== "") {
            $search_input = addslashes($search);
            $where[] = ("
                (buyer_company LIKE '%$search_input%'
                OR buyer_state LIKE '%$search_input%'
                OR UF.ucc_status LIKE '%$search_input%'
                OR UF.id LIKE '%$search_input%')
            ");
        }
        if($state !== "") {
            $where[] = "buyer_state = '$state'";
        }
        if($industry !== ""){
            $where[] = "buyer_sic_desc = '$industry'";
        }

        $where_sql = count($where) ? "WHERE " .implode(" AND ", $where) : "";
        $result = $db->query("
          SELECT * FROM ucc_buyers
          $where_sql
          ORDER BY $sort_by $order_by
          LIMIT $page_size
          OFFSET $offset;
        ");

        return json(['items' => $result, 'total' => $total]);
    }

    public function industries()
    {
        return json(
            UccBuyers::distinct('buyer_sic_desc')->get()
        );
    }
}