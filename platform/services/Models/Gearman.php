<?php namespace Services\Models;


use CodeIgniter\Model;


class Gearman extends \CodeIgniter\Model
{
	protected $DBGroup	  = 'common';

	protected $table      = 'gearman';

    protected $primaryKey = 'id';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [];

    protected $useTimestamps = false;

    protected $validationRules    = [];

    protected $validationMessages = [];
    protected $skipValidation     = false;

}

