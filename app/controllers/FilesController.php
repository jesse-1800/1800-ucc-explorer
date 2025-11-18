<?php

use App\Models\Files;
use Symfony\Component\HttpFoundation\Request;

class FilesController {
    private $storage_path;

    public function __construct()
    {
        $this->storage_path = "{$_SERVER['DOCUMENT_ROOT']}/storage/uploads";
    }

    public function fetch($partner_id)
    {
        (new Auth())->isLoggedIn();
        return json(
            Files::where('partner_id',$partner_id)->get()
        );
    }

    public function upload()
    {
        (new Auth())->isLoggedIn();
        $request = Request::createFromGlobals();
        $file = $request->files->get('file');
        $extension = $file->getClientOriginalExtension();
        $upload_path = "$this->storage_path/partner-id-{$_POST['partner_id']}";

        if ($extension !== 'xlsx' && $extension !== 'csv') {
            return json([
                'result' => false,
                'message' => "Invalid file $extension. Only .xlsx and .csv files are allowed."
            ]);
        }
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0775, true);
        }

        $base_name = pathinfo($file->getClientOriginalName(),PATHINFO_FILENAME);
        $base_name = preg_replace('/[^a-zA-Z0-9-_]/','_',$base_name);
        $the_filename = "$base_name-".time().".$extension";

        if ($file->move($upload_path, $the_filename)) {
            Files::insert([
                'name'       => $the_filename,
                'user_id'    => $_POST['user_id'],
                'partner_id' => $_POST['partner_id'],
                'mime_type'  => $file->getClientMimeType(),
                'extension'  => $extension,
                'is_synced'  => "0",
                'synced_at'  => "",
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return json([
                'result' => true,
                'message' => 'File uploaded successfully.',
            ]);
        } else {
            return json([
                'result' => false,
                'message' => 'Error: failed to upload file.'
            ]);
        }
    }

    public function destroy($id)
    {
        (new Auth())->isLoggedIn();
        $file = Files::find($id);
        $path = "{$_SERVER['DOCUMENT_ROOT']}/storage/uploads";

        if ($file->delete() && unlink("$path/$file->name")) {
            return json([
                'result' => true,
                'message' => 'File successfully deleted.'
            ]);
        } else {
            return json([
                'result' => false,
                'message' => 'An error occurred while deleting the file.'
            ]);
        }
    }
}