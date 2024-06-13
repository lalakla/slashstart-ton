<?php namespace Services\Models;


use CodeIgniter\Model;


class Product extends \CodeIgniter\Model
{

	protected $table      = 'shop_products';
    protected $primaryKey = 'id';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['shop_id', 'gallery', 'yml_id', 'wb_id', 'active', 'is_main', 'url_blank', 'url', 'priority', 'price', 'price_old', 'sku', 'photo', 'title', 'summary', 'content', 'cashback', 'cashback_abs', 'cashback_disabled', 'search'];

    protected $useTimestamps = false;

    protected $validationRules    = [];

    protected $validationMessages = [];
    protected $skipValidation     = false;








    public function getCart( $cart = '', $summary = false )
    {
    	$products = [];

    	if ($cart)
		{
			$prids = _explode('|', $cart);

			$total = $count = 0;

			$sku = [];

			foreach ($prids as $v)
			{
				list($id, $qty, $price) = _explode(':', $v);

				if (strpos($id, 'v') === 0)
				{
					$product = $this->select('shop_products.*, shop_variantes.id as variant_id, shop_variantes.title as variant_title, shop_variantes.price as variant_price, shop_variantes.sku as variant_sku, shop_categories.cashback as category_cashback')->where([
						'shop_products.active' => 1,
						'shop_variantes.id' => str_replace('v', '', $id),
					])
					->join('shop_variantes', 'shop_variantes.product_id = shop_products.id', 'inner')
					->join('shop_categories_products', 'shop_categories_products.product_id = shop_products.id', 'left')
					->join('shop_categories', 'shop_categories.id = shop_categories_products.category_id', 'left')
					->limit(1)
					->first();
				}
				else
				{
					$product = $this->select('shop_products.*, shop_categories.cashback as category_cashback')->where(['shop_products.active' => 1, 'shop_products.id' => $id])
					->join('shop_categories_products', 'shop_categories_products.product_id = shop_products.id', 'left')
					->join('shop_categories', 'shop_categories.id = shop_categories_products.category_id', 'left')
					->limit(1)
					->first();

				}

				if ($product)
				{
					unset($product['shop_id']);

					if ($summary)
					{
						unset($product['priority'], $product['price_old'], $product['photo'], $product['summary'], $product['content']);
					}

					$product['count'] = $qty;
					$product['total'] = $qty * ($product['variant_price'] ?? $product['price']);

					$total += $product['total'];
					$count += $product['count'];

					$sku[] = $product['variant_sku'] ?? $product['sku'];

					$products[] = $product;
				}
			}

			if ($summary)
			{
				$result = compact(['count', 'total', 'sku', 'products']);

				return $result;
			}

		}

		return $products;

    }




}

