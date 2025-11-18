<?php namespace App\Models;

use Kernel\Database\QueryBuilder as Model;

/**
 * Class FinancingProviders
 *
 * @package App\Models
 */
class Providers extends Model
{
    protected static $table = "providers";

    public static function default($partner_id)
    {
        $data = self::where('partner_id',$partner_id)->where('is_default',1)->first()->data;
        $data->lease_factors = json_decode($data->lease_factors);
        return $data;
    }
}