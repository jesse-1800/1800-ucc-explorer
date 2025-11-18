<?php namespace App\Migrations;

use Kernel\Database\Migration;

class TemplatesMigration extends Migration
{

    /**
     * name of the table to migrate
     **/
    protected $table = "templates";


    /**
     * field names and data types for this table
     */
    public function __construct()
    {
        $this->increments('id');
        $this->varchar('user_id');
        $this->varchar('partner_id');
        $this->varchar('name');
        $this->longtext('html_content');
        $this->longtext('css_content');
        $this->int('is_default', ['default' => '0']);
        $this->mediumblob('thumbnail');
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