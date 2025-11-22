<?php namespace App\Models;

use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;

class GCSBucketModel {
    private Bucket $bucket;
    private StorageClient $storage;
    private string $bucket_name = 'ucc-file-explorer-uploads';
    private string $gcs_key = 'app/config/ucc-explorer-svc-key.json';

    /**
     * Instantiate GCS and destination paths
     */
    public function __construct()
    {
        try {
            $this->storage = new StorageClient([
                'keyFilePath' => "{$_SERVER['DOCUMENT_ROOT']}/$this->gcs_key"
            ]);
            $this->bucket = $this->storage->bucket($this->bucket_name);
        } catch (Exception $e) {
            die(json([
                'result' => false,
                'message' => "GCS failed: {$e->getMessage()}"
            ]));
        }
    }

    /**
     * Upload to a specific partner folder
     */
    public function upload($file_name,$tmp_file_path)
    {
        return $this->bucket->upload(
            fopen($tmp_file_path,'r'),
            ['name' => $file_name,'contentType' => 'text/csv']
        );
    }

    /**
     * Fetch file contents from GCS bucket
     */
    public function parse_file($file_name)
    {
        try {
            $object = $this->bucket->object($file_name);
            if (!$object->exists()) {
                die(json([
                    'result' => false,
                    'message' => "File not found in GCS: $file_name"
                ]));
            }
            return $object->downloadAsString();
        }
        catch (\Exception $e) {
            die(json([
                'result' => false,
                'message' => "Failed to fetch file: {$e->getMessage()}"
            ]));
        }
    }

    /**
     * Fetch CSV headers and first row of data for field mapping
     */
    public function csv_preview($csv_content)
    {
        try {
            $lines = explode("\n", trim($csv_content));

            if (count($lines) < 2) {
                die(json([
                    'result'  => false,
                    'message' => 'CSV must contain headers and at least one data row',
                ]));
            }

            $headers = str_getcsv($lines[0]);
            $first_row = str_getcsv($lines[1]);

            return [
                'headers' => $headers,
                'sample_data' => $first_row
            ];
        }
        catch (\Exception $e) {
            die(json([
                'result'  => false,
                'message' => 'Failed to fetch a CSV preview',
            ]));
        }
    }
}