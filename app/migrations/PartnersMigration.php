<?php namespace App\Migrations;

use Kernel\Database\Migration;

class PartnersMigration extends Migration
{

    /**
     * name of the table to migrate
     **/
    protected $table = "partners";


    /**
     * field names and data types for this table
     */
    public function __construct()
    {
        $this->increments('id');
        $this->varchar('name');
        $this->varchar('website');
        $this->mediumblob('logo');
        $this->varchar('phone');

        $this->varchar('smtp_host');
        $this->varchar('smtp_port');
        $this->varchar('smtp_username');
        $this->varchar('smtp_password');

        $this->text('products_price_url');
        $this->text('supported_brands');

        $this->int('is_active');
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