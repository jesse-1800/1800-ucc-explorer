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
}