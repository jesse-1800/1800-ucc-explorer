<?php

use App\Models\UccFiles;

class FilesController {
    public function fetch($partner_id) {
        (new Auth)->isLoggedIn();

        return json(
            UccFiles::where('partner_id',$partner_id)->get()
        );
    }
}