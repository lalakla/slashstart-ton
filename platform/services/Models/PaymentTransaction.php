<?php namespace Services\Models;


use CodeIgniter\Model;


class PaymentTransaction extends \CodeIgniter\Model
{

	protected $table      = 'payment_transactions';
    protected $primaryKey = 'id';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['subscriber_id', 'order_id', 'service_id', 'price', 'created_at'];

    protected $useTimestamps = false;

    protected $validationRules    = [];

    protected $validationMessages = [];
    protected $skipValidation     = true;


    public function getTotalPurchaseAmount($subscriberId){
        $firstDate = date('Y-m-d', strtotime('first day of last month'));
        $lastDate = date('Y-m-d', strtotime('last day of last month'));
        $tmpPrevMonthSum = $this->select('SUM(price) as sum')
                        ->where('subscriber_id', $subscriberId)
                        ->where('created_at BETWEEN "'.$firstDate.'" AND "'.$lastDate.'" ')
                        ->first();

        $firstDate = date('Y-m-d', strtotime('first day of this month'));
        $lastDate = date('Y-m-d');
        $tmpMonthSum = $this->select('SUM(price) as sum')
                        ->where('subscriber_id', $subscriberId)
                        ->where('created_at BETWEEN "'.$firstDate.'" AND "'.$lastDate.'" ')
                        ->first();

        $total = $this->select('SUM(price) as sum')
                        ->where('subscriber_id', $subscriberId)
                        ->first();

        return [
            'amount_month'    => $tmpMonthSum['sum'] ?? 0,
            'amount_prev_month' => $tmpPrevMonthSum['sum'] ?? 0,
            'amount_all_time' => $total['sum'] ?? 0,
        ];
    }


}