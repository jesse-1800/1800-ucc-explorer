<?php namespace App\Models;
use Kernel\Database\Connection;
use Kernel\Database\QueryBuilder as Model;

class IDProviderPartnerUsers extends Model
{
    protected static $table = "idp_partner_users";
    public static function fetch()
    {
        Connection::initialize('identities');
        return self::get();
    }
}
