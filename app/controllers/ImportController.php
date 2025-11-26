<?php

use App\Models\GCSBucketModel;
use App\Models\UccAssignees;
use App\Models\UccBuyers;
use App\Models\UccContacts;
use App\Models\UccEquipments;
use App\Models\UccFileManager;
use App\Models\UccFilings;
use App\Models\UccLanguageModel;
use App\Models\UccProviders;
use Kernel\Database\Database;

/**
 * This class is meant for imports only.
 * For rehydration, check FilesController.php
 */
class ImportController
{
    /**
     * Will pull column names from select table
     * Let's AI generate a label for each.
     **/
    public function generate_definitions()
    {
        $database = new Database;
        $savepath = "{$_SERVER['DOCUMENT_ROOT']}/app/config/mapping";
        $tables   = array("ucc_filings");

        foreach ($tables as $table) {
            # Only generate if table.json doesn't exist
            if (!file_exists("$savepath/$table.json")) {
                $column_list = array();
                $raw_columns = $database->query("
                    SELECT COLUMN_NAME
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = '$table';
                ");
                foreach($raw_columns as $column) {
                    $column_list[] = [
                        "column"      => $column->COLUMN_NAME,
                        "label"       => '',
                        "description" => '',
                        "table"       => $table,
                        "mapped_to"   => "",
                        "display"     => false,
                        "category"    => "",
                        "preselect"   => [""]
                    ];
                }
                file_put_contents("$savepath/$table.json", json_encode($column_list));
            }
        }
    }

    /**
     * We'll use AI to generate label and description
     **/
    private function suggest_labels($column)
    {
        $llm = new UccLanguageModel();
        return $llm->completions([
            [
                'role' => 'system',
                'content' => ("
                    You are a database and UCC Filings expert. Return a **very short** label and
                    a **very short** description that explain the meaning of the
                    column name '$column'. Respond using ONLY this exact CSV format,
                    with no code formatting, no explanations, and no reasoning:
     
                    <label>,<description>
                ")
            ],
        ]);
    }

    /**
     * Uploads file into GCS Bucket under a partner's
     * company name slug e.g. 1800-office-solutions
     */
    public function upload_to_gcs()
    {
        if (!isset($_FILES['file'])) {
            return json([
                'result' => false,
                'message' => 'Please upload a valid CSV file'
            ]);
        }
        try {
            $gcs_instance = new GCSBucketModel();
            $file_tmp_path = $_FILES['file']['tmp_name'];
            $file_ext = basename($_FILES['file']['name']);
            $file_name = "{$_POST['folder_name']}/".time()."-$file_ext";

            // Upload the file to GCS
            $gcs_instance->upload($file_name,$file_tmp_path);

            // Store in database
            UccFileManager::insert([
                "user_id"     => $_POST['user_id'],
                "partner_id"  => $_POST['partner_id'],
                "name"        => $file_name,
                "is_imported" => 0,
                "created_at"  => now(),
                "updated_at"  => now(),
            ]);

            // Return response to Vue
            return json([
                'result'   => true,
                'contents' => $file_name
            ]);
        }
        catch (Exception $e) {
            return json([
                'result' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * After user mapped the fields, begin import
     */
    public function import_data()
    {
        $partner_id  = $_POST['partner_id'];
        $mapped_data = json_decode($_POST['data']);

        // Loop through each UCC ID filing.
        foreach ($mapped_data as $row) {
            // Insert the buyer data
            $buyer_id = UccBuyers::get_or_insert($partner_id,$row->buyer);

            // Insert Primary Contact
            $primary_contact_id = UccContacts::get_or_insert(
                $buyer_id,
                $partner_id,
                $row->contacts->buyer_primary_firstname,
                $row->contacts->buyer_primary_lastname,
                $row->contacts->buyer_primary_title,
            );

            // Insert Secondary Contact
            $secondary_contact_id = UccContacts::get_or_insert(
                $buyer_id,
                $partner_id,
                $row->contacts->buyer_secondary_firstname,
                $row->contacts->buyer_secondary_lastname,
                $row->contacts->buyer_secondary_title,
            );

            // Insert Service Provider
            $provider_id = UccProviders::get_or_insert($partner_id,$row->provider);

            // Insert Assignee
            $assignee_id = UccAssignees::get_or_insert($partner_id,$row->assignee);

            // Insert UCC Filing (required for Equipments)
            UccFilings::insert([
                'id'                  => $row->ucc_data->ucc_id,
                'partner_id'          => $partner_id,
                'buyer_id'            => $buyer_id,
                'assignee_id'         => $assignee_id,
                'provider_id'         => $provider_id,
                'primary_contact_id'  => $primary_contact_id,
                'secondary_contact_id'=> $secondary_contact_id,
                'ucc_transaction_id'  => $row->ucc_data->ucc_transaction_id,
                'ucc_date'            => $row->ucc_data->ucc_date,
                'ucc_lease_acqui_date'=> $row->ucc_data->ucc_lease_acqui_date,
                'ucc_status'          => $row->ucc_data->ucc_status,
                'ucc_lien'            => $row->ucc_data->ucc_lien,
                'ucc_comments'        => $row->ucc_data->ucc_comments,
                'ucc_fips2'           => $row->ucc_data->ucc_fips2,
                'ucc_batch'           => $row->ucc_data->ucc_batch,
            ]);

            // Iterate and Insert Equipments
            foreach ($row->equipments as $equipment) {
                UccEquipments::insert([
                    'partner_id'          => $partner_id,
                    'ucc_filing_id'       => $row->ucc_data->ucc_id,
                    'equipment_unit'      => $equipment->equipment_unit,
                    'equipment_ucc_year'  => $equipment->equipment_ucc_year,
                    'equipment_number'    => $equipment->equipment_number,
                    'equipment_brand'     => $equipment->equipment_brand,
                    'equipment_model'     => $equipment->equipment_model,
                    'equipment_desc'      => $equipment->equipment_desc,
                    'equipment_code'      => $equipment->equipment_code,
                    'equipment_serial_no' => $equipment->equipment_serial_no,
                    'equipment_size'      => $equipment->equipment_size,
                    'equipment_end_year'  => $equipment->equipment_end_year,
                    'equipment_attachment'=> $equipment->equipment_attachment,
                    'equipment_value'     => $equipment->equipment_value,
                    'equipment_tae'       => $equipment->equipment_tae,
                ]);
            }
        }

        // Update file status
        $file_model = UccFileManager::find($_POST['file_id']);
        $file_model->is_imported = 1;
        $file_model->save();

        return json([
            'result' => true,
            'message' => count($mapped_data) . ' UCC records imported successfully.'
        ]);
    }
}
