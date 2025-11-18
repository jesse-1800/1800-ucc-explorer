<?php namespace App\Migrations;

use Kernel\Database\Migration;

class FilesMigration extends Migration
{

    /**
     * name of the table to migrate
     **/
    protected $table = "files";


    /**
     * field names and data types for this table
     */
    public function __construct()
    {
        $this->increments('id');
        $this->varchar('user_id');
        $this->varchar('partner_id');
        $this->varchar('name');
        $this->varchar('mime_type');
        $this->varchar('extension');
        $this->varchar('is_synced');
        $this->varchar('synced_at');
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