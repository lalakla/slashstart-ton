<?php namespace Services\Models;


use CodeIgniter\Model;


class Order extends \CodeIgniter\Model
{

	protected $table      = 'shop_orders';
    protected $primaryKey = 'id';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['user_id', 'shop_id', 'active', 'count', 'total', 'cashback', 'cashback_used', 'user_name', 'user_phone', 'user_email', 'user_comments', 'user_address', 'products'];

    protected $useTimestamps = true;

    protected $validationRules    = [];

    protected $validationMessages = [];
    protected $skipValidation     = false;




    function getLastOrder($userId)
    {

    	$order = $this->where([
    		'user_id' => $userId
    	])
    	->orderBy('id', 'desc')
    	->limit(1)
    	->first();

    	if (!empty($order['products']))
    		$order['products'] = json_decode($order['products'], true);

    	return $order;

    }


}

