<?php namespace Services\Models;


use CodeIgniter\Model;


class Trans extends \CodeIgniter\Model
{

	protected $table      = 'shop_trans';
    protected $primaryKey = 'id';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['user_id', 'type', 'amount', 'order_id', 'date'];

    protected $useTimestamps = false;

    protected $validationRules    = [];

    protected $validationMessages = [];
    protected $skipValidation     = false;





    function getBalance($userId)
    {
    	$balance = 0;

    	$trans = $this->select('type, amount')->where([
    		'user_id' => $userId
    		])
    		->findAll();

    	foreach ($trans as $k=>$v)
    	{
    		if ($v['type'])
    			$balance -= $v['amount'];
    		else
    			$balance += $v['amount'];
    	}

    	return $balance;
    	
    }


}

