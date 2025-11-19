<?php namespace App\Models;
use Kernel\Database\Connection;
use Kernel\Database\QueryBuilder as Model;
class IDProviderPartners extends Model
{
    // We're using multiple-database, WE MUST instantiate the class
    // and NEVER use static calls e.g. Model::get() - DONT DO THIS
    protected static $table = "idp_partners";
    public function __construct($data = null)
    {
        parent::__construct($data);
        Connection::initialize('identities');
    }
}
