<?php

use App\Models\UccFiles;
use Google\Cloud\Storage\StorageClient;

class ImportController
{
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
