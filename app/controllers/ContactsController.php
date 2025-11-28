<?php

use App\Models\UccContacts;

class ContactsController {
    public function store()
    {
        $contact = json_decode($_POST['contact']);
        unset($contact->id);
        unset($contact->created_at);
        unset($contact->updated_at);
        $result = UccContacts::insert($contact);
        return json([
            'result' => $result,
            'message' => $result ? 'Contact has been added!':"An error occurred, can't add a contact."
        ]);
    }

    public function update()
    {
        $contact = json_decode($_POST['contact']);
        $result = UccContacts::update($contact,$contact->id);
        return json([
            'result' => $result,
            'message' => $result ? 'Contact has been updated!':"An error occurred, can't update contact."
        ]);
    }
}