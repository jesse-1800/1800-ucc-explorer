<?php

class UccfilingsController
{
    public function paginate()
    {
        $db = new Kernel\Database\Database();

        $curr_page = (int)($_POST['curr_page'] ?? 1);
        $page_size = (int)($_POST['page_size'] ?? 100);
        $offset    = ($curr_page - 1) * $page_size;
        $sort_map = array(
            'id'            => 'UF.id',
            'ucc_date'      => "STR_TO_DATE(UF.ucc_date,'%m/%d/%Y')",
            'ucc_status'    => 'UF.ucc_status',
            'buyer_company' => 'UB.buyer_company',
        );
        $sort_by   = $sort_map[$_POST['sort_by']] ?? 'id';
        $order_by  = $_POST['order_by'];
        $total     = $db->row("SELECT COUNT(*) AS 'total' FROM ucc_filings")->total;
        $result = $db->query("
          SELECT UF.*,UB.buyer_company AS buyer_company,(SELECT COUNT(*) FROM ucc_equipments EQ WHERE EQ.ucc_filing_id = UF.id) AS equipment_count
          FROM ucc_filings UF
          LEFT JOIN ucc_buyers UB ON UF.buyer_id = UB.id
          ORDER BY $sort_by $order_by
          LIMIT $page_size
          OFFSET $offset;
        ");

        return json(['items' => $result, 'total' => $total]);
    }
}