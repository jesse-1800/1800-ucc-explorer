<?php namespace App\Models;

use Kernel\Database\QueryBuilder as Model;
use Kernel\Security\Encryption;

/**
 * Class Partners
 *
 * @package App\Models
 */
class Partners extends Model
{
    protected static $table = "partners";

    public static function smtp_settings($partner_id)
    {
        $partner = self::find($partner_id)->data;
        $partner->smtp_password = Encryption::decode($partner->smtp_password);
        return (object) $partner;
    }
}