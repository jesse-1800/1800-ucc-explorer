<?php namespace App\Migrations;

use Kernel\Database\Migration;

class AddonsMigration extends Migration
{

    /**
     * name of the table to migrate
     **/
    protected $table = "addons";


    /**
     * field names and data types for this table
     */
    public function __construct()
    {
        $this->increments('id');
        $this->varchar('user_id');
        $this->varchar('partner_id');
        $this->varchar('name');
        $this->varchar('type');
        $this->varchar('price');
        $this->varchar('qty');
        $this->varchar('price_margin');
        $this->varchar('type');
        $this->varchar('created_at');
        $this->varchar('updated_at');
    }


    /**
     * Install the migration
     *
     * @return boolean
     */
    public function up()
    {
        return $this->install();
    }


    /**
     * Drop the table
     *
     * @return boolean
     */
    public function down()
    {
        return $this->uninstall();
    }

}