<?php namespace App\Models;

use Kernel\Database\QueryBuilder as Model;

/**
 * Class Proposals
 *
 * @package App\Models
 */
class Proposals extends Model
{
    protected static $table = "proposals";

    public static function get_author($user_id) {
        $proposal_author = null;

        if (\Session::get('auth0_users')) {
            $users_list = json_decode(\Session::get('auth0_users'));
        } else {
            $users_list = (new \Auth)->fetch_users(true,true);
        }

        # Find the author
        foreach ($users_list as $user) {
            $user = (object) $user;
            if ($user->user_id == $user_id) {
                $proposal_author = (object) [
                    'name' => $user->name,
                    'email' => $user->email,
                ];
                break;
            }
        }

        # Find partner company
        $partner_assoc = PartnerUsers::where('user_id',$user_id)->first();
        $partner_info = Partners::find($partner_assoc->partner_id);
        $proposal_author->company = $partner_info->name;

        return $proposal_author;
    }

    public static function get_clean($partner_id)
    {
        $proposals = self::where('partner_id',$partner_id)->get();
        foreach ($proposals as $i => $proposal) {
            $proposals[$i]->lease_factor_provider = json_decode($proposal->lease_factor_provider);
            $proposals[$i]->global_print_cost = json_decode($proposal->global_print_cost);
            $proposals[$i]->cost_addons = json_decode($proposal->cost_addons);
            $proposals[$i]->cart_items = json_decode($proposal->cart_items);
            $proposals[$i]->it_service_items = json_decode($proposal->it_service_items);
            $proposals[$i]->contract_pages = json_decode($proposal->contract_pages);
        }
        return $proposals;
    }
}