<?php namespace App\Migrations;

use Kernel\Database\Migration;

class FieldValuesMigration extends Migration
{

    /**
     * name of the table to migrate
     **/
    protected $table = "field_values";


    /**
     * field names and data types for this table
     */
    public function __construct()
    {
        $this->increments('id');
        $this->tinyint('proposal_id');
        $this->tinyint('field_id');
        $this->text('field_value');
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