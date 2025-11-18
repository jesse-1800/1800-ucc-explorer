<?php namespace App\Migrations;

use Kernel\Database\Migration;

class ProposalsMigration extends Migration
{

    /**
     * name of the table to migrate
     **/
    protected $table = "proposals";


    /**
     * field names and data types for this table
     */
    public function __construct()
    {
        # References
        $this->increments('id');
        $this->varchar('user_id');
        $this->varchar('partner_id');
        $this->longtext('template_id');
        $this->longtext('template_css');
        $this->longtext('template_html');
        $this->longtext('paperwork_css');
        $this->longtext('paperwork_html');
        $this->varchar('hash_code');

        # Proposal Category/Type
        $this->varchar('category');
        $this->varchar('acquisition_type');

        # Company Details
        $this->varchar('company_name');
        $this->varchar('first_name');
        $this->varchar('last_name');
        $this->varchar('email');

        # Proposal Details
        $this->varchar('title');
        $this->text('cover_letter');
        $this->varchar('status');
        $this->varchar('expiry_date');
        $this->varchar('sent_date');

        # Lease Details
        $this->varchar('lease_type');
        $this->varchar('lease_term_offered');
        $this->varchar('lease_term_selected');

        # Toggles
        $this->tinyint('prints_included_free', ['default' => 0]);
        $this->tinyint('is_global_print_cost', ['default' => 0]);
        $this->tinyint('show_contract_pages',  ['default' => 0]);
        $this->tinyint('show_term_options',    ['default' => 0]);
        $this->tinyint('show_prints_cost',     ['default' => 0]);

        # Signature Details
        $this->varchar('accepted_by');
        $this->varchar('accepted_date');
        $this->varchar('accepted_ip_addr');
        $this->blob('accepted_initials');
        $this->blob('accepted_signature');

        # These are objects stored as JSON
        $this->text('lease_factor_provider');
        $this->text('global_print_cost');
        $this->text('cart_items');
        $this->text('cost_addons');
        $this->text('it_service_items');
        $this->text('contract_pages');

        # Timestamps
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