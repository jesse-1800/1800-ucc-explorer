<?php

use App\Models\GCSBucketModel;
use App\Models\UccFileManager;
use App\Models\UccFilings;
use App\Models\UccLanguageModel;
use Kernel\Database\Database;

/**
 * This class is meant for imports only.
 * For rehydration, check FilesController.php
 */
class ImportControllerCopy
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
        $gcs_inst = new GCSBucketModel();
        $file_mgr = UccFileManager::find($_POST['file_id']);
        $contents = $gcs_inst->parse_file($file_mgr->name);
        $csv_data = $gcs_inst->csv_to_array($contents);
        $mappings = json_decode($_POST['mappings']);
        $data_list = array();

        // Loop through each CSV record
        foreach ($csv_data as $csv_row) {
            $row_data = array();

            // Apply mappings to transform CSV headers to database columns
            foreach ($mappings as $mapping) {
                $db_column  = $mapping->db_column; // e.g., 'buyid'
                $csv_header = $mapping->mapped_to; // e.g., 'BUYID'

                // Get the value from CSV row using the mapped header
                if (isset($csv_row[$csv_header])) {
                    $row_data[$db_column] = $csv_row[$csv_header];
                } else {
                    // Optional: handle missing values
                    $row_data[$db_column] = null;
                }
            }

            // Add the transformed row to data_list
            $data_list[] = $row_data;
        }

        // The actual database insertion
        $rows_inserted = 0;
        foreach($data_list as $insert) {
            $is_inserted = UccFilings::insert($insert);
            if ($is_inserted) $rows_inserted++;
        }
        $result = $rows_inserted == count($data_list);

        // Update file status
        $file_mgr->is_imported = 1;
        $file_mgr->save();

        return json([
            "result" => $result,
            "message" => "$rows_inserted row(s) inserted out of (".count($data_list).") from your file."
        ]);
    }

}
