<?php namespace App\Migrations;

use Kernel\Database\Migration;

class ITServiceItemsMigration extends Migration
{

    /**
     * name of the table to migrate
     **/
    protected $table = "it_service_items";


    /**
     * field names and data types for this table
     */
    public function __construct()
    {
        $this->increments('id');
        $this->varchar('user_id');
        $this->int('partner_id');
        $this->varchar('tier_id');
        $this->varchar('name');
        $this->text('unit_price');
        $this->text('unit_type');
        $this->text('quantity');
        $this->text('description');
        $this->varchar('charge_type');
        $this->varchar('price_margin',['default'=>0]);
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