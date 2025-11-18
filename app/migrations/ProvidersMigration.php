<?php namespace App\Migrations;

use Kernel\Database\Migration;

class ProvidersMigration extends Migration
{

    /**
     * name of the table to migrate
     **/
    protected $table = "providers";


    /**
     * field names and data types for this table
     */
    public function __construct()
    {
        $this->increments('id');
        $this->int('partner_id');
        $this->varchar('name');
        $this->text('lease_factors');
        $this->int('is_default', ['default' => '0']);
        $this->longtext('paperwork_html');
        $this->longtext('paperwork_css');
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