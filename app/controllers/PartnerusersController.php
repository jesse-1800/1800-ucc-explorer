<?php use App\Models\IDProviderPartnerUsers;

/**
 * Default controller
 * Class HomeController
 */
class PartnerusersController
{
    public function fetch()
    {
        return json((new IDProviderPartnerUsers)::get());
    }

    public function submit()
    {
        $auth = new Auth();
        $auth->isLoggedIn();
        $find_record = (new IDProviderPartnerUsers)::where('user_id',$_POST['user_id'])->first();
        if ($find_record) {
            $find_record->partner_id = $_POST['partner_id'];
            $find_record->save();
        } else {
            (new IDProviderPartnerUsers)::insert([
                'user_id' => $_POST['user_id'],
                'partner_id' => $_POST['partner_id'],
            ]);
        }
    }
}
