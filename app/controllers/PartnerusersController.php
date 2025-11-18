<?php use App\Models\PartnerUsers;

/**
 * Default controller
 * Class HomeController
 */
class PartnerusersController
{
    public function fetch()
    {
        return json(PartnerUsers::get());
    }

    public function submit()
    {
        $auth = new Auth();
        $auth->isLoggedIn();
        $find_record = PartnerUsers::where('user_id',$_POST['user_id'])->first();
        if ($find_record) {
            $find_record->partner_id = $_POST['partner_id'];
            $find_record->save();
        } else {
            PartnerUsers::insert([
                'user_id' => $_POST['user_id'],
                'partner_id' => $_POST['partner_id'],
            ]);
        }
    }
}
