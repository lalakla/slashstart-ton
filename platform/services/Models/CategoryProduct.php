<?php namespace Services\Models;


use CodeIgniter\Model;


class CategoryProduct extends \CodeIgniter\Model
{

	protected $table      = 'shop_categories_products';
    protected $primaryKey = 'id';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['category_id', 'product_id'];

    protected $useTimestamps = false;

    protected $validationRules    = [];

    protected $validationMessages = [];
    protected $skipValidation     = false;



    public function getProductCategories($id)
    {
    	$cids = [];

    	$all = $this->select('category_id')->where('product_id', $id)->findAll();

    	foreach ($all as $c)
    	{
    		$cids[] = $c['category_id'];
    	}

    	return $cids;
    }



}

