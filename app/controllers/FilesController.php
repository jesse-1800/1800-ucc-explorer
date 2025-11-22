<?php

use App\Models\UccFileManager;

class FilesController {
    public function fetch($partner_id) {
        (new Auth)->isLoggedIn();
        return json(
            UccFileManager::where('partner_id',$partner_id)->get()
        );
    }

    public function pending_import($file_id)
    {
        $file = UccFileManager::find($file_id);
    }
}