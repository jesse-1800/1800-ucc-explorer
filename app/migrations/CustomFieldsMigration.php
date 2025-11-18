<?php namespace App\Migrations;

use Kernel\Database\Migration;

class CustomFieldsMigration extends Migration
{

    /**
     * name of the table to migrate
     **/
    protected $table = "custom_fields";


    /**
     * field names and data types for this table
     */
    public function __construct()
    {
        $this->increments('id');
        $this->varchar('user_id');
        $this->tinyint('partner_id');
        $this->varchar('field_key');
        $this->varchar('field_type');
        $this->varchar('field_label');
        $this->text('field_options');
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