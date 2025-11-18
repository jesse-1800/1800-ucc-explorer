<?php namespace App\Models;

use Kernel\Database\QueryBuilder as Model;

/**
 * Class Options
 *
 * @package App\Models
 */
class Options extends Model
{
    protected static $table = "options";

    /**
     * Add option
     *
     * @param $user_id
     * @param $partner_id
     * @param $related_to
     * @param $name
     * @param $content
     */
    public static function add_option($user_id, $partner_id, $related_to, $name, $content)
    {
        $option = array(
            'user_id'    => $user_id,
            'partner_id' => $partner_id,
            'related_to' => $related_to,
            'key_name'   => self::slugify($name),
            'name'       => $name,
            'content'    => $content,
            'created_at' => date("Y-m-d"),
            'updated_at' => date("Y-m-d"),
        );
        return self::insert($option);
    }

    private static function slugify($string)
    {
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9]+/', '_', $string);
        $string = trim($string, '_');
        return $string;
    }

    public static function get_option($partner_id, $option_name)
    {
        $my_options = Options::where('partner_id',$partner_id)->get();
        foreach ($my_options as $my_option) {
            if ($my_option->key_name == $option_name) {
                return $my_option->content;
            }
        }
        return "";
    }
}