<?php

use App\Models\UccFileManager;
use App\Models\UccLanguageModel;
use Kernel\Database\Database;
use Google\Cloud\Storage\StorageClient;

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
        $tables   = array(
            "buyers",
            "equipments",
            "lenders",
            "lessors",
        );

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
                    $ai_suggest = explode(",", $this->suggest_labels($column->COLUMN_NAME));
                    $column_list[] = [
                        "column" => $column->COLUMN_NAME,
                        "label" => $ai_suggest[0],
                        "description" => $ai_suggest[1],
                        "table" => $table,
                        "mapped_to" => ""
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
                    You are a database expert. Return a **very short** label and
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
    public function store()
    {
        if (!isset($_FILES['file'])) {
            return json(['result' => false, 'message' => 'Please upload a valid CSV file']);
        }

        try {
            $storage = new StorageClient([
                'keyFilePath' => "{$_SERVER['DOCUMENT_ROOT']}/app/config/ucc-explorer-svc-key.json"
            ]);
            $bucket_name = 'ucc-file-explorer-uploads';
            $bucket = $storage->bucket($bucket_name);
            $file_tmp_path = $_FILES['file']['tmp_name'];
            $file_name = "{$_POST['folder_name']}/".time().'-'.basename($_FILES['file']['name']);

            // Upload the file to GCS
            $bucket->upload(
                fopen($file_tmp_path, 'r'),
                ['name' => $file_name,'contentType' => 'text/csv']
            );

            // Store in database
            UccFiles::insert([
                "user_id"     => $_POST['user_id'],
                "partner_id"  => $_POST['partner_id'],
                "name"        => $file_name,
                "is_imported" => 0,
                "created_at"  => now(),
                "updated_at"  => now(),
            ]);

            // Return response to Vue
            return json([
                'result' => true,
                'file_name' => $file_name
            ]);
        }
        catch (Exception $e) {
            return json([
                'result' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
