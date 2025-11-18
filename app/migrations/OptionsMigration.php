<?php namespace App\Migrations;

use Kernel\Database\Migration;

class OptionsMigration extends Migration
{

    /**
     * name of the table to migrate
     **/
    protected $table = "options";


    /**
     * field names and data types for this table
     */
    public function __construct()
    {
        $this->increments('id');
        $this->varchar('user_id');
        $this->varchar('partner_id');
        $this->varchar('related_to');
        $this->varchar('key_name');
        $this->varchar('name');
        $this->varchar('content');
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