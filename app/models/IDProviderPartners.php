<?php namespace App\Models;
use Kernel\Database\Connection;
use Kernel\Database\QueryBuilder as Model;
class IDProviderPartners extends Model
{
    protected static $table = "idp_partners";
    public static function fetch()
    {
        Connection::initialize('identities');
        return self::get();
    }
}
