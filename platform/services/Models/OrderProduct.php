<?php namespace Services\Models;


use CodeIgniter\Model;


class OrderProduct extends \CodeIgniter\Model
{

	protected $table      = 'shop_orders_products';
    protected $primaryKey = 'id';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['order_id', 'product_id', 'variant_id', 'price', 'count'];

    protected $useTimestamps = false;

    protected $validationRules    = [];

    protected $validationMessages = [];
    protected $skipValidation     = false;





}

