<?php namespace Services\Models;


use CodeIgniter\Model;


class Variant extends \CodeIgniter\Model
{

	protected $table      = 'shop_variantes';
    protected $primaryKey = 'id';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['product_id', 'order', 'active', 'price', 'price_old', 'sku', 'title'];

    protected $useTimestamps = false;

    protected $validationRules    = [];

    protected $validationMessages = [];
    protected $skipValidation     = false;







}

