<?php namespace App\Migrations;

use Kernel\Database\Migration;

class PartnerUsersMigration extends Migration
{

    /**
     * name of the table to migrate
     **/
    protected $table = "partner_users";


    /**
     * field names and data types for this table
     */
    public function __construct()
    {
        $this->increments('id');
        $this->varchar('user_id');
        $this->varchar('partner_id');
        $this->mediumtext('email_signature');
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