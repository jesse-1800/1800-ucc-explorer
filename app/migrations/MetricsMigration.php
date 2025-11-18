<?php namespace App\Migrations;

use Kernel\Database\Migration;

class MetricsMigration extends Migration
{

    /**
     * name of the table to migrate
     **/
    protected $table = "metrics";


    /**
     * field names and data types for this table
     */
    public function __construct()
    {
        $this->increments('id');
        $this->varchar('partner_id');
        $this->varchar('proposal_id');
        $this->varchar('total_views');
        $this->text('page_data'); // json
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