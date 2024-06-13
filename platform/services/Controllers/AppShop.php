<?php namespace Services\Controllers;


use CodeIgniter\Controller;


	function apps_shop_flatcats2tree($categories, $pid = 0, $result = array())
	{

		foreach ($categories as $c)
		{
			$cpid = !empty($c['parentId']) ? $c['parentId'] : 0;

			if ($cpid == $pid)
			{
				$icats = apps_shop_flatcats2tree($categories, $c['id']);

				if ($icats)
				{
					$c['inner'] = $icats;
				}

				$result[] = $c;
			}
		}

		return $result;
	}



	function apps_shop_buildCatsPath(&$categories, $pathName = '', $result = array())
	{
		foreach ($categories as &$c)
		{
			$c['path'] = $pathName . '///' . $c['name'];

			$result[$c['id']] = $c['path'];

			if (!empty($c['inner']))
			{
				$inn = apps_shop_buildCatsPath($c['inner'], $c['path']);
				foreach ($inn as $id=>$path)
					$result[$id] = $path;

			}
		}

		foreach ($result as $k=>$v)
			$result[$k] = ltrim($v, '/');

		return $result;
	}




class AppShop extends \App\Controllers\AppController
{


	var $allowedMethods = ['index', 'catalog', 'page', 'product', 'cart', 'checkout', 'search', 'add2cart'];

	var $mWidget;
    var $mOrder;
    var $mTrans;
    var $mOrderProduct;
    var $mProduct;
    var $mCategory;
    var $mVariant;
    var $mCategoryProduct;
    var $mService;
    var $mSubscriberAttribute;
    var $mSubscriber;
    var $mMessage;

    var $currentShop;

    var $currentApp;
    var $shops;

    var $subscriber;

    var $categories;

    var $activeCategories;
    var $messanger;
    var $currentShopConfig;

    var $mReferralSetting;

	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{

		// Do Not Edit This Line
		parent::initController($request, $response, $logger);


		$this->mOrder = new \Services\Models\Order();
		$this->mTrans = new \Services\Models\Trans();
		$this->mOrderProduct = new \Services\Models\OrderProduct();
		$this->mProduct = new \Services\Models\Product();
		$this->mCategory = new \Services\Models\Category();
		$this->mVariant = new \Services\Models\Variant();
		$this->mCategoryProduct = new \Services\Models\CategoryProduct();
		$this->mService = new \Services\Models\ServiceModel();
		$this->mSubscriberAttribute = new \Subscribers\Models\SubscriberAttribute();

		$this->mSubscriber = new \Subscribers\Models\Subscriber();
		$this->mMessage = new \Subscribers\Models\ChatMessage();


		$this->mWidget = new \Widgets\Models\Widget();

        $this->mReferralSetting = new \Referal\Models\ReferralSetting();
		
		
		// dd($this->categories);


		$shops = $this->mService->getAllByName('shop');
		$this->currentShop = $shops[0] ?? [];
		$currentShop = [];


		foreach ($shops as $k=>$v)
		{
			$cp = json_decode($v['client_params'], true);
			$shops[$k]['title'] = $cp['account'];

			unset($shops[$k]['id'], $shops[$k]['uniq'], $shops[$k]['user_id'],
					$shops[$k]['token'],
					$shops[$k]['token_expires'],
					$shops[$k]['token_params'],
					$shops[$k]['client_params']);

			if (!empty($_GET['shop_id']) && $v['hash'] == $_GET['shop_id'])
			{
				$currentShop = $shops[$k];
				$this->currentShop = $v;
			}


			if (!empty($_GET[API_PREFIX_SHORT . 'app']) && $v['hash'] == $_GET[API_PREFIX_SHORT . 'app'])
			{
				$this->currentApp = $v;
			}

		}

		if (!$currentShop)
			$currentShop = $shops[0] ?? [];

		$this->shops = $shops;

		$view = \Config\Services::renderer();

		$view->setVar('shops', $shops);
		$view->setVar('currentShop', $currentShop);


		$this->categories = $this->mCategory->getTree( $this->currentShop['id'] );
		$this->activeCategories = $this->mCategory->getTree($this->currentShop['id'], 0, 1);


		$this->messanger = loadLibrary('telegram', 'messanger');
		$this->messanger->setApp($this->mService, $this->currentShop);

		if (!empty($_GET['p']))
		{
			
			// p($this->messanger);
		}

        if (isset($this->currentShop['hash']))
        {
            $sKey = 'apps_shop_' . $this->currentShop['hash'] . '_settings';
			$config = getParam($sKey);
			if ($config)
				$config = json_decode($config, true);

            $this->currentShopConfig = $config;

            $view->setVar('config', $config);
        }
		


		if (isset($this->currentApp['hash']))
		{

			$query = $this->request->getGet();

			if (empty($query[API_PREFIX_SHORT . 'app']) || empty($query[API_PREFIX_SHORT . 'id']) || empty($query[API_PREFIX_SHORT . 'm']) || empty($query[API_PREFIX_SHORT . 'h']))
			{
				return e404('apps: incorrect query params');
			}

			$check = substr(md5($query[API_PREFIX_SHORT . 'id'] . ':' . $query[API_PREFIX_SHORT . 'm'] . ':' . $query[API_PREFIX_SHORT . 'app'] . ':' . SERVICE_EXTERNAL_ID_KEY), 5, 14);
			if ($check != $query[API_PREFIX_SHORT . 'h'])
			{
				return e404('apps: incorrect hash');
			}

			$this->subscriber = $this->mSubscriber->getAllData( extIdDecode($query[API_PREFIX_SHORT . 'id']) );
			if (!$this->subscriber)
			{
				return e404('apps: !subscriber');
			}

			// $mid = alphaID($query[API_PREFIX_SHORT . 'm'], true, 5, $query[API_PREFIX_SHORT . 'id']);
			// $this->message = $this->mMessage->where(['id' => $mid])->limit(1)->first();
			// if (!$this->message)
			// {
			// 	return e404('apps: !message');
			// }


			$this->messanger->setSubscriber( $this->subscriber );


			$sKey = 'apps_shop_' . $this->currentApp['hash'] . '_settings';
			$config = getParam($sKey);
			if ($config)
				$config = json_decode($config, true);

			foreach ($config['menu']['items'] ?? [] as $k=>$v)
			{
				if ($v)
				{
					if (strpos($v, 'p') === 0)
					{
						$item = $this->mProduct->select('id, title')->where(['id' => str_replace('p', '', $v), 'active' => 1])->limit(1)->first();
						if ($item)
							$item['type'] = 'product';
					}
					else
					{
						$item = $this->mCategory->select('id, title')->where(['id' => str_replace('p', '', $v), 'active' => 1])->limit(1)->first();
						if ($item)
							$item['type'] = 'catalog';
					}

					$config['menu']['items'][$k] = $item;
				}
			}
			foreach ($config['slider']['items'] ?? [] as $k=>$v)
			{
				if (!empty($v['photo']))
				{
					$config['slider']['enabled'] = 1;
					// break;
				}

				$config['slider']['items'][$k]['href'] = $this->messanger->parseVars( $v['href'] );

			}

			// p($config['slider']['items']);


			$this->currentShopConfig = $config;

			$view->setVar('config', $config);



			$balance = $this->mTrans->getBalance( $this->subscriber['id'] );
			$view->setVar('balance', $balance);




		}




		$this->appendBC(__('app.menu_shop', 'Shop'), '/apps/shop');
		$this->setTplData('header_nav', ['file' => 'Services\Views\apps\admin\shop\shop-nav', 'position' => 'right']);
		$this->setTplData('submenu', ['file' => 'Services\Views\apps\admin\shop\shop-submenu']);
		$this->setTplData('nav_from', 'shop');


	}





	public function admin_import_wb()
	{
		$data = trim_data($this->request->getPost('data'));

		if (empty($data['token']))
		{
			return '{"status": "error", "message": "' . __('apps.shop_token_required', 'Для импорта необходимо указать токен.') . '"}';
		}

		
		
		$token = $data['token'];


		$query = array(
			'sort' => array(
				'cursor' => array(
					'limit' => 1000
				),
				'filter' => array(
					'withPhoto' => -1
				)	
			)
		);

		$response = externalRequest([
			'u' => 'https://suppliers-api.wildberries.ru/content/v1/cards/cursor/list',
			'm' => 'post',
			'b' => json_encode($query),
			'h' => [
				'Authorization' => $token,
				'Content-Type' => 'application/json',
			]
		]);

		
		if ($response['response_status'] != 200)
		{
			return '{"status": "error", "message": "' . sprintf(__('apps.shop_token_wrong_response', 'Не уадалось выполнить запрос к WB с этим токеном. Текст ошибки: %s'), $response['response_body']) . '"}';
		}

		
		saveParam('apps_shop_wb_token', $token);

		$products = [];

		$data = json_decode($response['response_body'], true);

		foreach ($data['data']['cards'] as $card)
		{
			// p($card);

			$product = [
				'active' => 1,
				'photo' => $card['mediaFiles'][0] ?? '',
				'title' => $card['object'] . ' ' . $card['brand'] . '',
				'sku' => $card['vendorCode'],
				'articul' => $card['vendorCode'],
				'wb_id' => $card['nmId'] ?? ($card['imtID'] ?? $card['vendorCode']),
			];



			$products[] = $product;
		}


		$this->_importProducts($products, []);


		// p($products);


		return '{"status": "success", "message": "' . sprintf(__('apps.shop_yml_link_wrong_format', 'Данные успешно обновлены. Обновлено товаров: %s'), sizeof($products)) . '"}';

	}



	public function admin_import_yml()
	{
		$data = trim_data($this->request->getPost('data'));

		if (empty($data['yml']) || !isUrl($data['yml']))
		{
			return '{"status": "error", "message": "' . __('apps.shop_yml_link_required', 'Для импорта нужна корректная ссылка на YML') . '"}';
		}

		
		$url = $data['yml'];

		$tmp = WRITEPATH . 'uploads/' . preg_replace('#[^0-9a-z]#si', '', $url);


		$response = externalRequest(
			['u' => $url]
		);

		if ($response['response_status'] != 200)
		{
			unlink($tmp);
			return '{"status": "error", "message": "' . sprintf(__('apps.shop_yml_link_wrong_response', 'Не уадалось загрузить файл. Код ответа сервера с файлом %s, должен быть 200.'), $response['response_status']) . '"}';
		}

		if (strpos($response['response_body'], '<yml_catalog') === false)
		{
			unlink($tmp);
			return '{"status": "error", "message": "' . sprintf(__('apps.shop_yml_link_wrong_format', 'Содержание файла не похоже на YML формат. Начало файла: %s'), truncate( addslashes(h($response['response_body'])), 320)) . '"}';
		}


		file_put_contents($tmp, $response['response_body']);


		

		$ymlData = file_get_contents($tmp);
		$ymlData = preg_replace('/<!\[CDATA\[(.*?)\]\]>/s', '$1', $ymlData);
		$xml = simplexml_load_string($ymlData, 'SimpleXMLElement', LIBXML_NOCDATA);
		$errors = [];
		foreach(libxml_get_errors() as $error)
		{
			$errors[] = $error;
		}
		if ($errors)
		{
			unlink($tmp);
			return '{"status": "error", "message": "' . sprintf( __('apps.shop_yml_link_wrong_data', 'Ошибки при разборе YML формата: %s'), addslashes(h( join(', ', $errors) )) ) . '"}';
		}


		saveParam('apps_shop_yml_import', $url);


		$categories = $products = [];

		foreach ($xml->shop->categories->children() as $c)
		{
			$category = array(
				'name' => (string)$c,
			);
	
			foreach ($c->attributes() as $k=>$v)
				$category[$k] = (string)$v;

			$categories[] = $category;
		}


		$cats = apps_shop_flatcats2tree($categories);
		$categories = apps_shop_buildCatsPath($cats);



		
		foreach ($xml->shop->offers->children() as $p)
		{
			$product = (array)$p;

			foreach ($p->attributes() as $k=>$v)
				$product[$k] = (string)$v;

			unset($product['@attributes']);

			$pr = [
				'active' => $product['available'] ? 1 : 0,
				'price' => $product['price'],
				'title' => $product['name'],
				'content' => $product['description'],
				'photo' => $product['picture'],
				'articul' => $product['vendorCode'],
				'sku' => $product['vendorCode'],
				'category_path' => $categories[$product['categoryId']],
				'yml_id' => $product['id'], 
				'yml_category_id' => $product['categoryId'], 
			];

			$products[] = $pr;

		}
				

		



		if (!empty($data['categories']))
		{
			$this->_importCategories($cats);
		}


		$fields = [];
		if (!empty($data['prices']))
			$fields[] = 'price';

		$this->_importProducts($products, $fields);



		unlink($tmp);

		return '{"status": "success", "message": "' . sprintf(__('apps.shop_yml_link_wrong_format', 'Данные успешно обновлены. Обновлено товаров: %s'), sizeof($products)) . '"}';

	}


	
	function _importProducts($products, $fields = [])
	{
		// p($fields, 0);


		if (!$fields)
		{
			$fields = ['id', 'active', 'price', 'title', 'content', 'photo', 'sku', 'category_id'];

				// 'category_path' => $categories[$product['categoryId']],
		}

		foreach ($products as $product)
		{
			$curr = [];

			$update = ['shop_id' => $this->currentShop['id']];

			if ($product['articul'])
			{
				$curr = $this->mProduct->where(['sku' => $product['articul'], 'shop_id' => $this->currentShop['id']])->limit(1)->first();
			}

			if (!$curr && !empty($product['yml_id']))
			{
				$curr = $this->mProduct->where(['yml_id' => $product['yml_id'], 'shop_id' => $this->currentShop['id']])->limit(1)->first();	

				$update['yml_id'] = $product['yml_id'];
			}

			if (!$curr && !empty($product['wb_id']))
			{
				$curr = $this->mProduct->where(['wb_id' => $product['wb_id'], 'shop_id' => $this->currentShop['id']])->limit(1)->first();	

				$update['wb_id'] = $product['wb_id'];
			}


			foreach ($fields as $f)
			{
				if (isset($product[$f]))
				{
					$update[$f] = $product[$f];
				}
			}

			if (!empty($update['photo']))
			{
				$update['photo'] = str_replace('http://', 'https://', $update['photo']);
			}


			if (in_array('category_id', $fields) && !empty($product['category_path']))
			{
				$category = $this->mCategory->getByPath( $this->currentShop['id'], $product['category_path'], '///' );

				if ($category)
				{
					$update['category_id'] = $category['id'];
				}
			}

			if ($curr)
			{
				$update['id'] = $curr['id'];
			}


			if (in_array('id', $fields))
			{
				$this->mProduct->save( $update );

				if (!$curr)
				{
					$update['id'] = $this->mProduct->getInsertID();
				}
				else
				{
					$this->mCategoryProduct->where('product_id', $update['id'])->delete();
				}

				
				if (!empty($update['category_id']))
				{
					$this->mCategoryProduct->save(['category_id' => $update['category_id'], 'product_id' => $update['id']]);
				}
				

			}
			else
			{
				if ($curr)
				{
					$this->mProduct->save( $update );
				}
			}


			// p($update);


		}


		// p($producs);
	}


	function _importCategories($categories)
	{
		
		

		foreach ($categories as $c1)
		{
			
			$cat1 = ['title' => $c1['name'], 'parent_id' => 0, 'shop_id' => $this->currentShop['id']];

			$curr1 = $this->mCategory->select('id')->where($cat1)->limit(1)->first();

			if (!$curr1)
			{
				$this->mCategory->save( $cat1 );

				$cat1['id'] = $this->mCategory->getInsertID();
			}
			else
			{
				$cat1['id'] = $curr1['id'];	
			}

			if (!empty($c1['inner']))
			{
				foreach ($c1['inner'] as $c2)
				{
					
					$cat2 = ['title' => $c2['name'], 'parent_id' => $cat1['id'], 'shop_id' => $this->currentShop['id']];

					$curr2 = $this->mCategory->select('id')->where($cat2)->limit(1)->first();

					if (!$curr2)
					{
						$this->mCategory->save( $cat2 );

						$cat2['id'] = $this->mCategory->getInsertID();
					}
					else
					{
						$cat2['id'] = $curr2['id'];	
					}


					if (!empty($c2['inner']))
					{
						foreach ($c2['inner'] as $c3)
						{
							
							$cat3 = ['title' => $c3['name'], 'parent_id' => $cat2['id'], 'shop_id' => $this->currentShop['id']];

							$curr3 = $this->mCategory->select('id')->where($cat3)->limit(1)->first();

							if (!$curr3)
							{
								$this->mCategory->save( $cat3 );

								$cat3['id'] = $this->mCategory->getInsertID();
							}
							else
							{
								$cat3['id'] = $curr3['id'];	
							}

						}

					}


				}

			}


			// p($cat1);

		}

		// p($categories);

	}



	public function admin_product($id = '')
	{

		if ($this->request->getGet('delete'))
		{
			if ($this->mProduct->delete($this->request->getGet('delete')))
			{
				$this->mVariant->where(['product_id' => $this->request->getGet('delete')])->delete();
				$this->mCategoryProduct->where(['product_id' => $this->request->getGet('delete')])->delete();


				return '{"status": "success", "message": "' . '' . '"}';
			}
		}

		if ($this->request->getGet('delete_variant'))
		{
			if ($this->mVariant->delete($this->request->getGet('delete_variant')))
			{
				return '{"status": "success", "message": "' . '' . '"}';
			}
		}


		$product = $this->request->getPost('data');
		if (!empty($product))
		{
			$result = ['status' => 'error', 'message' => __('Не удалось сохранить. Попробуйте, пожалуйста, еще раз.')];


			if (!empty($product['id']))
			{
				$this->mCategoryProduct->where('product_id', $product['id'])->delete();
			}


			$product['shop_id'] = $this->currentShop['id'];


			$product['gallery'] = json_encode( $product['gallery'] );

            // TODO: Сохранить отзывы
            //$producs['reviews'] = json_encode($product['reviews']);

			if ($this->mProduct->save( $product ))
			{
				$id = $product['id'] ? $product['id'] : $this->mProduct->getInsertID();

				if (empty($product['sku']))
				{
					$this->mProduct->save( ['id' => $id, 'sku' => 'P' . zerofill($id, 3)] );
				}


				if (!empty($product['variantes']))
				{
					if (!empty($product['variantes'][1]) || !empty($product['variantes'][0]['title']))
					{
						foreach ($product['variantes'] as $n=>$variant)
						{
							$variant['product_id'] = $id;

							if (!$variant['order'])
								$variant['order'] = $n+1;

							$this->mVariant->save( $variant );

							$vid = $variant['id'] ? $variant['id'] : $this->mVariant->getInsertID();


							if (empty($variant['sku']))
							{
								$this->mVariant->save( ['id' => $vid, 'sku' => 'P' . zerofill($id, 3) . 'V' . zerofill($n+1, 2)] );
							}
						}
					}
				}


				session()->setFlashData('success', sprintf(__('Товар <b>%s</b> успешно сохранен.'), $product['title']));



				if (!empty($product['categories']))
				{

					foreach ($product['categories'] as $cid)
					{
						$this->mCategoryProduct->save(['category_id' => $cid, 'product_id' => $id]);
					}
				}

				$result = ['status' => 'success', 'redirect' => '/apps/shop/product/' . $id . '?shop_id=' . $this->currentShop['hash']];
				// $result = ['status' => 'success'];

			}

			return _json_encode($result);
		}



		$product = $this->mProduct->where(['id' => $id])->first();

		if ($id && !$product)
		{
			session()->setFlashdata('error', __('Товар не найден.'));
			r('/apps/shop?shop_id=' . $this->currentShop['hash']);
		}


		if ($product)
		{
			$this->appendBC($product['title'], '/apps/shop/product/' . $product['id'] . '?shop_id=' . $this->currentShop['hash']);
		}
		else
		{
			$this->appendBC(__('apps.shop_new_product_title', 'New Product'), '/apps/shop/product?shop_id=' . $this->currentShop['hash']);
		}


		if (!empty($product['gallery']))
			$product['gallery'] = (array)json_decode($product['gallery'], true);

		$categories = $this->categories;

		$product_categories = $this->mCategoryProduct->getProductCategories($id);


		$variantes = $this->mVariant->where('product_id', $id)->orderBy('order, title')->findAll();


		if (!empty($this->request->getGet('copy')))
		{

			unset($product['id'], $product['sku']);
			if ($this->mProduct->save( $product ))
			{
				$id = $this->mProduct->getInsertID();
				$this->mProduct->save( ['id' => $id, 'sku' => 'P' . zerofill($id, 3)] );

				foreach ($variantes as $n=>$variant)
				{
					$variant['product_id'] = $id;
					unset($variant['id'], $variant['sku']);

					$this->mVariant->save( $variant );

					$vid = $this->mVariant->getInsertID();

					$this->mVariant->save( ['id' => $vid, 'sku' => 'P' . zerofill($id, 3) . 'V' . zerofill($n+1, 2)] );
				}

				foreach ($product_categories as $cid)
				{
					$this->mCategoryProduct->save(['category_id' => $cid, 'product_id' => $id]);
				}

				session()->setFlashdata('success', __('Товар успешно скопирован. Сейчас открыта копия для редактирования.'));

				r('/apps/shop/product/' . $id . '?shop_id=' . $this->currentShop['hash']);

			}




		}



		$tree = $this->mCategory->getSelectTree($this->categories, $product_categories, 'name="data[categories][]" class="form-control" multiple="multiple" size="10" style="height: auto;"');




		echo view( 'Services\Views\apps/admin/shop/product', compact([
			'product', 'categories', 'tree', 'variantes'
		]));
	}

	public function admin_index()
	{
		$post = $this->request->getPost('data');
		if (!empty($post['action']))
		{
			switch ($post['action'])
			{
				case 'priority':
					foreach ($post['priority'] ?? [] as $id=>$priority)
					{
						$this->mProduct->save(['id' => $id, 'priority' => $priority ? (int)$priority : null]);
					}

					session()->setFlashData('success', __('Приоритет успешно изменен.'));
					r('/apps/shop?shop_id=' . $this->currentShop['hash']);

				break;
			}
		}



		$query = $this->mProduct->select('shop_products.*')->where('shop_products.shop_id', $this->currentShop['id']);

		$query->orderBy('shop_products.priority desc, shop_products.id desc');


		$pcnt = $filter['pcnt'] ?? 50;
		$products = $query->paginate($pcnt);
        $pager = $this->mProduct->pager;

        $categories = $this->categories;

		echo view( 'Services\Views\apps/admin/shop/index', compact([
			'products', 'pager', 'categories'
		]));
	}






	public function admin_orders()
	{
		$post = $this->request->getPost('data');
		
		$query = $this->mOrder->select('shop_orders.*')->where('shop_orders.shop_id', $this->currentShop['id']);

		$query->orderBy('shop_orders.id desc');


		$pcnt = $filter['pcnt'] ?? 50;
		$orders = $query->paginate($pcnt);
        $pager = $this->mOrder->pager;




        
        $this->setTplData('nav_from', 'orders');

		echo view( 'Services\Views\apps/admin/shop/orders', compact([
			'orders', 'pager'
		]));
	}

	public function admin_order($id = '')
	{

		if ($this->request->getGet('delete'))
		{
			if ($this->mOrder->delete($this->request->getGet('delete')))
			{
				return '{"status": "success", "message": "' . '' . '"}';
			}
		}


		if ($this->request->getGet('active') !== null)
		{
			if ($this->mOrder->save(['id' => $id, 'active' => $this->request->getGet('active')]))
			{
				return r( env('HTTP_REFERER') );
			}
		}

		

		$product = $this->mProduct->where(['id' => $id])->first();

		if ($id && !$product)
		{
			session()->setFlashdata('error', __('Товар не найден.'));
			r('/apps/shop?shop_id=' . $this->currentShop['hash']);
		}


		if ($product)
		{
			$this->appendBC($product['title'], '/apps/shop/product/' . $product['id'] . '?shop_id=' . $this->currentShop['hash']);
		}
		else
		{
			$this->appendBC(__('apps.shop_new_product_title', 'New Product'), '/apps/shop/product?shop_id=' . $this->currentShop['hash']);
		}


		if (!empty($product['gallery']))
			$product['gallery'] = (array)json_decode($product['gallery'], true);

		

		$categories = $this->categories;

		$product_categories = $this->mCategoryProduct->getProductCategories($id);


		$variantes = $this->mVariant->where('product_id', $id)->orderBy('order, title')->findAll();


		if (!empty($this->request->getGet('copy')))
		{

			unset($product['id'], $product['sku']);
			if ($this->mProduct->save( $product ))
			{
				$id = $this->mProduct->getInsertID();
				$this->mProduct->save( ['id' => $id, 'sku' => 'P' . zerofill($id, 3)] );

				foreach ($variantes as $n=>$variant)
				{
					$variant['product_id'] = $id;
					unset($variant['id'], $variant['sku']);

					$this->mVariant->save( $variant );

					$vid = $this->mVariant->getInsertID();

					$this->mVariant->save( ['id' => $vid, 'sku' => 'P' . zerofill($id, 3) . 'V' . zerofill($n+1, 2)] );
				}

				foreach ($product_categories as $cid)
				{
					$this->mCategoryProduct->save(['category_id' => $cid, 'product_id' => $id]);
				}

				session()->setFlashdata('success', __('Товар успешно скопирован. Сейчас открыта копия для редактирования.'));

				r('/apps/shop/product/' . $id . '?shop_id=' . $this->currentShop['hash']);

			}




		}



		$tree = $this->mCategory->getSelectTree($this->categories, $product_categories, 'name="data[categories][]" class="form-control" multiple="multiple" size="10" style="height: auto;"');




		echo view( 'Services\Views\apps/admin/shop/product', compact([
			'product', 'categories', 'tree', 'variantes'
		]));
	}




	public function admin_category($id = '')
	{
		if ($this->request->getGet('delete'))
		{
			if ($this->mCategory->delete($this->request->getGet('delete')))
			{
				return '{"status": "success", "message": "' . '' . '"}';
			}
		}


		$category = $this->request->getPost('data');
		if (!empty($category))
		{
			$result = ['status' => 'error', 'message' => __('Не удалось сохранить. Попробуйте, пожалуйста, еще раз.')];


			$category['shop_id'] = $this->currentShop['id'];

			$category['title'] = htmlemoji($category['title']);

			if ($this->mCategory->save( $category ))
			{
				session()->setFlashData('success', sprintf(__('Категория <b>%s</b> успешно сохранена.'), $category['title']));

				$id = $category['id'] ? $category['id'] : $this->mCategory->getInsertID();

				$result = ['status' => 'success', 'redirect' => '/apps/shop/category/' . $id . '?shop_id=' . $this->currentShop['hash']];

			}

			return _json_encode($result);
		}



		$category = $this->mCategory->where(['id' => $id])->first();
		$categories = $this->categories;


		if ($category)
		{
			$this->appendBC($category['title'], '/apps/shop/category/' . $category['id'] . '?shop_id=' . $this->currentShop['hash']);
		}
		else
		{
			$this->appendBC(__('apps.shop_new_category_title', 'New Categrory'), '/apps/shop/category?shop_id=' . $this->currentShop['hash']);
		}
		$this->setTplData('nav_from', 'category');

		echo view( 'Services\Views\apps/admin/shop/category', compact([
			'category', 'categories'
		]));
	}

	public function admin_categories()
	{
		$post = $this->request->getPost('data');
		if (!empty($post['action']))
		{
			switch ($post['action'])
			{
				case 'priority':
					foreach ($post['priority'] ?? [] as $id=>$priority)
					{
						$this->mCategory->save(['id' => $id, 'priority' => $priority ? (int)$priority : null]);
					}

					session()->setFlashData('success', __('Приоритет успешно изменен.'));
					r('/apps/shop/categories?shop_id=' . $this->currentShop['hash']);

				break;
			}
		}



		$query = $this->mCategory->select('shop_categories.*')->where('shop_id', $this->currentShop['id']);

		$query->orderBy('shop_categories.priority desc, shop_categories.title asc');


		$pcnt = $filter['pcnt'] ?? 50;
		$categories = $query->paginate($pcnt);
        $pager = $this->mProduct->pager;


        $this->setTplData('nav_from', 'categories');


		echo view( 'Services\Views\apps/admin/shop/categories', compact([
			'categories'
		]));
	}






	public function admin_config()
	{



		$config = $this->request->getPost('data');
		if (!empty($config))
		{

			// function getParam($key, $default = null)
			// function saveParam($key, $value)

			$sKey = 'apps_shop_' . $config['id'] . '_settings';

			$result = ['status' => 'error', 'message' => __('Не удалось сохранить. Попробуйте, пожалуйста, еще раз.')];

			if (saveParam($sKey, _json_encode($config['settings'])))
			{
				$result = ['status' => 'success'];
			}

			return _json_encode($result);
		}



		$sKey = 'apps_shop_' . $this->currentShop['hash'] . '_settings';
		$config = getParam($sKey);
		if ($config)
			$config = json_decode($config, true);

		// p($config);

		$categoriesTree = $this->mCategory->getSelectTree($this->categories, [], false);
		$products = $this->mProduct->select('id, title, sku')->where('shop_id', $this->currentShop['id'])->orderBy('title')->findAll();
		$attributes = $this->mSubscriberAttribute->getList();

		$this->appendBC(__('app.menu_settings', 'Settings'), '/apps/shop/config?shop_id=' . $this->currentShop['hash']);
		$this->setTplData('nav_from', 'config');

        // Вывод платежной системы на фронт
        //$_payments = $this->mService->getAllByName('leadpay');
        //$_payments = array_merge($_payments, $this->mService->getAllByName('prodamus'));

		echo view( 'Services\Views\apps/admin/shop/config', compact([
			'config', 'categoriesTree', 'products', 'attributes'
		]));
	}









	public function actions()
	{
		$method = $this->request->getGet('method') ?? 'index';
		if (!$method)
			$method = 'index';

		$result = ['status' => 'success', 'method' => $method, 'params' => $_POST];

		if (in_array($method, $this->allowedMethods))
		{
			$response = call_user_func_array([$this, $method], ['params' => $_POST]);

			$result = am($result, $response ?? []);
		}

		return _json_encode($result);
	}



	
	public function search($params)
	{
		$query = $params['q'] ?? '';

		$products = [];

		$q = like($query);

		if ($q)
		{
			
			$q[0] = $this->mProduct->db->escape($q[0]);
			$q[1] = $this->mProduct->db->escape($q[1]);

			$products = $this->mProduct->select('shop_products.*')
				->where(
					'shop_products.active = 1
					AND (
						shop_products.title LIKE '.$q[0] .' OR shop_products.title LIKE '.$q[0] .'
						OR
						shop_products.content LIKE '.$q[0] .' OR shop_products.content LIKE '.$q[0] .'
						OR
						shop_products.search LIKE '.$q[0] .' OR shop_products.search LIKE '.$q[0] .'
					)'
				)
				
			->join('shop_categories_products', 'shop_categories_products.product_id = shop_products.id', 'inner')
			->orderBy('shop_products.priority DESC, shop_products.id DESC')
			->findAll();

		}
			

		foreach ($products as $k=>$v)
		{
			$products[$k]['variantes'] = $this->mVariant->where(['active' => 1, 'product_id' => $v['id']])->orderBy('order, title')->findAll();

			$products[$k]['url'] = $this->messanger->parseVars( $products[$k]['url'] );
		}



		$content = view( 'Services\Views\apps/shop-catalog', compact([
			'products', 'query'
		]));

		return ['update' => ['selector' => '#main', 'content' => $content]];
	}



	public function catalog($params)
	{
		$categoryId = $params['id'] ?? 0;

		$category = $this->mCategory->where(['id' => $categoryId, 'active' => 1])->limit(1)->first();

		if (!$category)
		{
			$content = view( 'Services\Views\apps/404');

			return ['update' => ['selector' => '#main', 'content' => $content]];
		}

		$parentCategory = $childrenCategories = [];
		if (!empty($category['parent_id']))
		{
			$parentCategory = $this->mCategory->where(['id' => $category['parent_id'], 'active' => 1])->limit(1)->first();
		}

		$childrenCategories = $this->mCategory->where(['parent_id' => $category['id'], 'active' => 1])->limit(1)->findAll();


		$products = $this->mProduct->select('shop_products.*, shop_categories.cashback as category_cashback')->where([
			'shop_products.active' => 1,
			'shop_categories_products.category_id' => $categoryId
		])
		->join('shop_categories_products', 'shop_categories_products.product_id = shop_products.id', 'inner')
        ->join('shop_categories', 'shop_categories.id = shop_categories_products.category_id', 'left')
		->orderBy('shop_products.priority DESC, shop_products.id DESC')
		->findAll();


		foreach ($products as $k=>$v)
		{
			$products[$k]['variantes'] = $this->mVariant->where(['active' => 1, 'product_id' => $v['id']])->orderBy('order, title')->findAll();

			$products[$k]['url'] = $this->messanger->parseVars( $products[$k]['url'] );

            if ($v['cashback_disabled'])
                continue;
            $pr = $v;
            $pcb = 0;
            if (!empty($pr['cashback']))
            {
                    $pcb += $pr['cashback_abs'] ? $pr['cashback'] : $pr['price'] * $pr['cashback'] / 100;
            }
            elseif (!empty($pr['category_cashback']))
            {
                    $pcb += $pr['price'] * $pr['category_cashback'] / 100;
            }
            elseif (!empty($this->currentShopConfig['main']['cashback']))
            {
                    $pcb += $pr['price'] * $this->currentShopConfig['main']['cashback'] / 100;
            }
            $products[$k]['cashback_total'] = floor($pcb);
		}


		$content = view( 'Services\Views\apps/shop-catalog', compact([
			'products', 'category', 'parentCategory', 'childrenCategories'
		]));

		return ['update' => ['selector' => '#main', 'content' => $content]];
	}



	public function product($params)
	{
		$id = $params['id'] ?? 0;

		$product = $this->mProduct->where(['id' => $id, 'active' => 1])->limit(1)->first();



		if (!$product)
		{
			$content = view( 'Services\Views\apps/404');

			return ['update' => ['selector' => '#main', 'content' => $content]];
		}

		$product['url'] = $this->messanger->parseVars( $product['url'] ?? '' );


		$variantes = $this->mVariant->where(['active' => 1, 'product_id' => $id])->orderBy('order, title')->findAll();

		$product_categories = $this->mCategoryProduct->getProductCategories($id);
		$category = [];
		if ($product_categories)
		{
			$category = $this->mCategory->where(['id' => $product_categories[0]])->first();
		}

		$content = view( 'Services\Views\apps/shop-product', compact([
			'product', 'variantes', 'product_categories', 'category'
		]));

		return ['update' => ['selector' => '#main', 'content' => $content]];
	}



	public function checkout($params = [])
	{
		$oFS = new \Flows\Models\FlowSubscriber();
		$oFSA = new \Flows\Models\FlowSubscriberAttribute();

		helper('cookie');

		$cart = get_cookie('_slst_shop_cart_' . $this->currentShop['hash']);

		$products = $this->mProduct->getCart( $cart, true );

		if (empty($products['products']))
		{
			return ['update' => ['selector' => '#checkout-result', 'class' => 'alert alert-warning', 'content' => __('Нет активных продуктов для заказа.')]];
		}


		if (empty($this->currentShopConfig['cart']['imcheckout']))
		{

			$user = array_map('trim', $params['data'] ?? []);

			if (!empty($this->currentShopConfig['cart']['require_name']) && (empty($user['name']) || mb_strlen($user['name'], 'utf8') < 2))
				return ['update' => ['selector' => '#checkout-result', 'class' => 'alert alert-warning', 'content' => __('Укажите, пожалуйста, корректное имя.')]];

			if (!empty($this->currentShopConfig['cart']['require_phone']) && (empty($user['phone']) || !isPhone($user['phone'])))
				return ['update' => ['selector' => '#checkout-result', 'class' => 'alert alert-warning', 'content' => __('Укажите, пожалуйста, корректный номер телефона.')]];

			if (!empty($this->currentShopConfig['cart']['require_email']) && (empty($user['email']) || !isEmail($user['email'])))
				return ['update' => ['selector' => '#checkout-result', 'class' => 'alert alert-warning', 'content' => __('Укажите, пожалуйста, корректный адрес email.')]];


			if (!empty($this->currentShopConfig['cart']['require_address']) && empty($user['address']))
				return ['update' => ['selector' => '#checkout-result', 'class' => 'alert alert-warning', 'content' => __('Укажите, пожалуйста, корректный адрес доставки.')]];


			$updateSubscriber = [];

			$fields = ['name', 'phone', 'email', 'address'];

			foreach ($fields as $f)
			{
				if (!empty($this->currentShopConfig['cart']['require_' . $f]) && !empty($this->currentShopConfig['cart'][$f . '_attr']))
				{
					if (!is_numeric($this->currentShopConfig['cart'][$f . '_attr']))
					{
						if (empty($this->subscriber[$this->currentShopConfig['cart'][$f . '_attr']]) || empty($this->currentShopConfig['cart'][$f . '_noreplace']))
							$updateSubscriber[$this->currentShopConfig['cart'][$f . '_attr']]  = ['value' => hst($user[$f])];
					}
					else
					{
						$param = 'attr_' . (int)$this->currentShopConfig['cart'][$f . '_attr'];
						$attr = $oFSA->select('id, value')->where(['param' => $param, 'subscriber_id' => $this->subscriber['id']])->limit(1)->first();

						// p($param, 0); p($attr, 0);

						if (empty($attr['value']) || empty($this->currentShopConfig['cart'][$f . '_noreplace']))
						{
							$updateSubscriber[(int)$this->currentShopConfig['cart'][$f . '_attr']]  = ['value' => hst($user[$f]), 'current' => $attr['id'] ?? null];
						}
					}
				}
			}


			foreach ($updateSubscriber as $attr=>$value)
			{

				$this->mSubscriberAttribute->saveAttr($this->subscriber['id'], [$attr], $value['value'], $value['current'] ?? null);
			}


		}


		$cashback = $cashbackUsed = 0;

		if (empty($this->currentShopConfig['main']['cashback_disabled']))
		{

			foreach ($products['products'] as $pr)
			{
				if ($pr['cashback_disabled'])
					continue;

				$pcb = 0;

				if (!empty($pr['cashback']))
				{
					$pcb += $pr['cashback_abs'] ? $pr['cashback'] : $pr['price'] * $pr['cashback'] / 100;
				}
				elseif (!empty($pr['category_cashback']))
				{
					$pcb += $pr['price'] * $pr['category_cashback'] / 100;
				}
				elseif (!empty($this->currentShopConfig['main']['cashback']))
				{
					$pcb += $pr['price'] * $this->currentShopConfig['main']['cashback'] / 100;
				}


				$cashback += $pcb * $pr['count'];

				// p($pcb . ' - ' . $cashback, 0);
			}

			$cashback = round($cashback);

            if (!empty($this->currentShopConfig['cashback']['levels'][0]['price']))
            {
                    $levels = array_reverse($this->currentShopConfig['cashback']['levels']);
                    foreach ($levels as $cbl)
                    {
                            if ($products['total'] >= $cbl['price'])
                            {
                                    $bonus = $cbl['price'];
                                    if (isset($cbl['is_pcnt']) && $cbl['is_pcnt'])
                                            $bonus = round($products['total'] * $bonus / 100);
                                    $cashback += $bonus;
                                    break;
                            }
                    }
            }



			if (!empty($params['data']['use_cashback']) && !empty($params['data']['cashback_used']))
			{
				$cashbackUsed = intval($params['data']['cashback_used']);

				$balance = $this->mTrans->getBalance( $this->subscriber['id'] );

				if ($cashbackUsed > $balance)
				{
					$cashbackUsed = $balance;
				}

				if (!empty($this->currentShopConfig['main']['cashback_max']))
				{
					$max = $this->currentShopConfig['main']['cashback_max'];
					$max = intval($products['total'] * $max / 100); 
					if ($cashbackUsed > $max)
						$cashbackUsed = $max;
				}

			}

		}



		// p($products, 0);

		// p($this->currentShopConfig, 0);

		$order = [
			'user_id' => $this->subscriber['id'],
			'shop_id' => $this->currentShop['id'],
			'active' => 1,
			'count' => $products['count'] ?? 0,
			'total' => $products['total'] ?? 0,
			'cashback' => $cashback,
			'cashback_used' => $cashbackUsed,
			'user_name' => $user['name'] ?? '',
        	'user_phone' => $user['phone'] ?? '',
        	'user_email' => $user['email'] ?? '',
        	'user_address' => $user['address'] ?? '',
        	'user_comments' => hst($user['comments'] ?? ''),
        	'products' => _json_encode($products),
		];

        // Отнять кешбек от общ. суммы
		if ($cashbackUsed && $order['total'])
		{
			$order['total'] -= $cashbackUsed;
		}

        // Проверить поставлена ли галочна на то чтобы учитывать заказы в магазине при начислении баллов рефералки
        $oPT = new \Referal\Models\PointTransaction();
        $oRS = new \Referal\Models\ReferralSetting();
        $ablePayByPoints = $oRS->get_settings_value_by_name('payment_with_referal');
        // Отнять активные баллы
        if (isset($ablePayByPoints) && $ablePayByPoints && !empty($params['data']['use_referal']) && !empty($params['data']['use_referal_total'])){
            $oPT->subtractActivePoints($this->subscriber['id'], $params['data']['use_referal_total']);
            $order['total'] -= $params['data']['use_referal_total'];
        }
        
        $allowRefPoints = $oRS->get_settings_value_by_name('allow_ref_points');
        if (isset($allowRefPoints) && $allowRefPoints){
            // Добавить баллы
            $dataPoints = [];
            $getParentSubscribers = $this->mSubscriber->getParents($this->subscriber['ref_link']);
            // Добавить себе балл
            $settingsValues = $oRS->get_settings_value_by_name('level_0');
            $referalValue = (json_decode($settingsValues, true)['purchase'] ?? 0)  * $order['total'] / 100;
            $dataPoints[0]['subscriber_id'] = $this->subscriber['id'];
            $dataPoints[0]['point'] = $referalValue;
            // Добавить реффералам до 3 уровня бал
            foreach ($getParentSubscribers as $level => $parents){
                $settingsValues = $oRS->get_settings_value_by_name('level_'.$level);
                // Проценты от покупок
                $referalValue = (json_decode($settingsValues, true)['purchase'] ?? 0)  * $order['total'] / 100;
                $dataPoints[$level]['subscriber_id'] = $parents['id'];
                $dataPoints[$level]['from_subscriber'] = $this->subscriber['id'];
                $dataPoints[$level]['point'] = $referalValue;
            }
            $oPT->updateSubscriberPoints($dataPoints);
        }

		$this->mOrder->save($order);

		$order['id'] = $this->mOrder->getInsertID();

		if ($cashback && empty($this->currentShopConfig['main']['cbonpayment']))
		{
			$trans = [
				'user_id' => $this->subscriber['id'],
				'order_id' => $order['id'],
				'amount' => $cashback,
				'type' => 0,
				'date' => df()
			];
			$this->mTrans->save( $trans );
		}

		if ($cashbackUsed)
		{
			$trans = [
				'user_id' => $this->subscriber['id'],
				'order_id' => $order['id'],
				'amount' => $cashbackUsed,
				'type' => 1,
				'date' => df()
			];
			$this->mTrans->save( $trans );
		}



		$productsText = [];

		foreach ($products['products'] as $p)
		{
			$op = [
				'order_id' => $order['id'],
				'product_id' => $p['id'],
				'variant_id' => $p['variant_id'] ?? 0,
				'price' => $p['variant_price'] ?? $p['price'],
				'count' => $p['count'] ?? 0,
			];


			$productsText[] = $p['title'] . (!empty($p['variant_title']) ? ' ('.$p['variant_title'].') ' : '') . ' / ' . ($p['variant_sku'] ?? $p['sku']) . ' / ' . $op['price'] . ' * ' . $op['count'];

			$this->mOrderProduct->save( $op );
		}


		$params = ['param' => $this->currentShop['hash'], 'subscriber_id' => $this->subscriber['id']];

		$products['user'] = $user ?? [];
        $vars = [
        	'trigger_shop_id' =>  $this->currentShop['hash'],
        	'trigger_order_id' => alphaID($order['id'], false, 4, $this->currentShop['hash']),
        	'trigger_order_sku' => join(';', $products['sku'] ?? []),
        	'trigger_order_date' => df(),
        	'trigger_order_total' => $products['total'] ?? 0,
        	'trigger_order_count' => $products['count'] ?? 0,
        	'trigger_order_user_name' => $user['name'] ?? '',
        	'trigger_order_user_phone' => $user['phone'] ?? '',
        	'trigger_order_user_email' => $user['email'] ?? '',
        	'trigger_order_user_address' => $user['address'] ?? '',
        	'trigger_order_comments' => hst($user['comments'] ?? ''),
        	'trigger_order_json' => _json_encode($products),
        	'trigger_order_text' => join("\r\n", $productsText),
        ];


        // p($params, 0); p($vars, 0); p($products, 0);

        // p($updateSubscriber, 0);

		// p($this->subscriber);

		$params['or'] = ['param' => ''];
        $triggered = $oFS->checkTriggers('store_order', $params, $vars);

        // p($triggered);



        $paymentLink = '';

        $paymentMethod = $this->currentShopConfig['cart']['payment_method'] ?? '';
		if ($paymentMethod)
		{
			$payment = $this->mService->getById( $paymentMethod );

			if ($payment)
			{
				$invoice = [
					'price' => $order['total'],
					'service' => $payment['hash'],
					'subscriber' => extIdEncode($this->subscriber['id']),
					'order' => $vars['trigger_order_id'],
					'sku' => join(';', $products['sku'] ?? []),
					'title' => sprintf(__('Заказ %s'), $vars['trigger_order_id']),
				];

				$invoice = invoiceParams($invoice);

				if ($invoice)
				{
					$paymentLink = 'https://' . PROJECT_HOST . '/invoice?' . http_build_query($invoice);
				}

			}
		}


		// не удаляет из-за префикса, хотя для get он не учитывается
		//delete_cookie('_slst_shop_cart_' . $this->currentShop['hash']);
        setcookie('_slst_shop_cart_' . $this->currentShop['hash'], '', 0, '/');

		if (!empty($this->currentShopConfig['cart']['imcheckout']) && $paymentLink)
		{
			return ['redirect' => $paymentLink];
		}


		$content = view( 'Services\Views\apps/shop-checkout', compact([
			'paymentLink'
		]));

		return ['update' => ['selector' => '#main', 'content' => $content]];



	}


	public function add2cart($query = [])
	{
		if (empty($query['id']))
			return; 

		// l($query, 'cart');

		$product = $this->mProduct->find( $query['id'] );


		$params = ['param' => $this->currentShop['hash'], 'subscriber_id' => $this->subscriber['id']];

		$vars = [
        	'trigger_shop_id' =>  $this->currentShop['hash'],
        	'trigger_order_sku' => $product['sku'],
        	'trigger_order_price' => $query['pp'],
        	'trigger_order_title' => $product['title'] ?? 0
        	
        ];

		$params['or'] = ['param' => ''];

		$oFS = new \Flows\Models\FlowSubscriber();

        $triggered = $oFS->checkTriggers('store_cart', $params, $vars);


	}


	public function cart($params = [])
	{
		helper('cookie');



		$cartCookie = $cart = get_cookie('_slst_shop_cart_' . $this->currentShop['hash']);


		$order = [];

		if (!empty($params['order']))
		{
			if ($params['order'] == 'last')
			{
				$order = $this->mOrder->getLastOrder( $this->subscriber['id'] );

				$cookie = [];
				if (!empty($order['products']['products']))
				{
					foreach ($order['products']['products'] as $p)
					{
						if (!empty($p['variant_id']))
						{
							$cookie[] = 'v' . $p['variant_id'] . ':' . $p['count'] . ':' . $p['variant_price'];
						}
						else
						{
							$cookie[] = 'v' . $p['id'] . ':' . $p['count'] . ':' . $p['price'];
						}
					}

					$cookie = join('|', $cookie);
				}

				if ($cookie)
				{
					$cart = $cartCookie = $cookie;
				}
			}
		}

		// p($cookie, 0);
		// p($order, 0);				
		// p($params, 0);
		// p($cart);



		$products = $this->mProduct->getCart( $cart );
		// p($products);

        $totalPrice = 0;
        foreach ($products as $product){
            $totalPrice += ($product['price'] * $product['count']);
        }

        $paymentWithReferal = $this->mReferralSetting->get_settings_value_by_name('payment_with_referal');
        
        // Получить активных и полученных баллы
        $currentSubscriber = $this->mSubscriber->where(['ref_link' => $_GET['ref']])->first();
		$oPT = new \Referal\Models\PointTransaction();
        $currentPoints = $oPT->getTotalPoints($currentSubscriber['id']);
        $max_active_points  = $currentPoints['active_points'] ?? 0;
        $max_able_points = 0;

        // Максимальный процент оплата баллами
        if (!empty($this->currentShopConfig['main']['cashback_max']) && $this->currentShopConfig['main']['cashback_max'] > 0 && $max_active_points > 0){
            $max_able_points = round(($totalPrice * $this->currentShopConfig['main']['cashback_max']) / 100);
            if ($max_able_points>$max_active_points){
                $max_able_points = $max_active_points;
            }
        }

		$content = view( 'Services\Views\apps/shop-cart', compact([
			'products',
			'cartCookie',
            'max_active_points',
            'max_able_points',
            'paymentWithReferal',
            'totalPrice'
		]));

		return ['update' => ['selector' => '#main', 'content' => $content]];
	}


	public function page($params)
	{
		$id = $params['id'] ?? 0;

		return ['update' => ['selector' => '#main', 'content' => 'page content #id-' . $id]];
	}





	public function index()
	{

		if (empty($this->currentApp['id']))
		{
			return e404();
		}

		$catalog = $this->mCategory->getTree($this->currentApp['id'], 0, 1);

		if (!empty($_GET['p']))
		{
			// p($this->currentShopConfig);
		}


		$products = $this->mProduct->select('shop_products.*')->where([
			'shop_products.active' => 1,
			'shop_products.is_main' => 1,
			'shop_products.shop_id' => $this->currentApp['id'],
		])
		->orderBy('shop_products.priority DESC, shop_products.id DESC')
		->findAll();

		foreach ($products as $k=>$v)
		{
			$products[$k]['variantes'] = $this->mVariant->where(['active' => 1, 'product_id' => $v['id']])->orderBy('order, title')->findAll();
			$products[$k]['url'] = $this->messanger->parseVars( $products[$k]['url'] ?? '' );
		}

        // Получить текучего подписчика
        $currentSubscriber = $this->mSubscriber->where(['ref_link' => $_GET['ref']])->first();

		if ($this->request->isAJAX())
		{
			$content = view( 'Services\Views\apps/shop-index', compact([
				'catalog', 'products', 'currentSubscriber'
			]));

			return ['update' => ['selector' => '#main', 'content' => $content]];
		}
		else
		{
			$content = view( 'Services\Views\apps/shop', compact([
				'catalog', 'products', 'currentSubscriber'
			]));

			echo $content;
		}


	}


	/**
     * Show referal webapp - stats
     */
    public function referal(){
        // Получить микролендинги
        $widgets = $this->mWidget->select(['id', 'handle', 'title', 'settings'])
                        ->where('type', WIDGET_TYPE_MICROLP)
                        ->findAll();

        // Получит все мессенджеры (телеграм)
        $servicesList = [];
        $services = $this->mService->getAllByName('telegram');
        // Получить ссылки
        $messenger = loadLibrary('telegram', 'messanger');
        foreach ($services as $service){
            $messenger->setApp($this->mService, $service);

            // Добавить реферальный код если есть в ссылку
            $redirect = $messenger->getSubscriptionUrl($_GET['ref']);

            $servicesList[] = [
                'redirect' => $redirect,
                'client_params' => json_decode($service['client_params'], true)
            ];
        }

        // Получить статистику реффералов
        $referals = [];
        if (isset($_GET['ref']) && $_GET['ref']!=''){
            $referals = $this->mSubscriber->getReferalStats($_GET['ref'], 3);
        }

        $currentSubscriber = $this->mSubscriber->where(['ref_link' => $_GET['ref']])->first();
        foreach ($referals as $k => $ref){
            $referals[$k]['much_points'] = $this->mSubscriber->muchPoints($currentSubscriber['id'], $ref['id']);
        }

        // Получить личные данные
        $referalSettings = $this->mReferralSetting->get_data();
		
		// Текушиий подписчик впервые отображает условия
		$currentSubscriber = [];
        if (isset($_GET['ref']) && $_GET['ref']!=''){
            $currentSubscriber = $this->mSubscriber->where(['ref_link' => $_GET['ref']])->first();
            if(!$currentSubscriber['terms_agreed']){
                return view( 'Services\Views\apps/referal-terms', compact('referalSettings'));
            }
        }
		// Получить личные данные
        $personal_data = json_decode($currentSubscriber['personal_data'], true);
		
		// Получить детали вывода
		$withdrawal_data = json_decode($currentSubscriber['withdrawal_data'], true);
        
		// Получить активных и полученных баллы
		$oPT = new \Referal\Models\PointTransaction();
        $currentPoints = $oPT->getTotalPoints($currentSubscriber['id']);
        $max_active_points  = $currentPoints['active_points'] ?? 0;

        // Доступ на вывод средств
        $ableMoneyBack = $this->mReferralSetting->get_settings_value_by_name('moneyback');
        if (isset($ableMoneyBack) && $ableMoneyBack){
            $ableMoneyBack = true;
        }else{
            $ableMoneyBack = false;
        }

        //echo "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";die();
        
        if ($this->request->isAJAX()){
			/*$content = view( 'Services\Views\apps/shop-index', compact([
				'catalog', 'products'
			]));

			return ['update' => ['selector' => '#main', 'content' => $content]];*/
		} else {
			$content = view( 'Services\Views\apps/referal', compact([
				'widgets', 'referals', 'servicesList', 'referalSettings', 'personal_data', 'withdrawal_data', 'max_active_points', 'ableMoneyBack'
			]));

			echo $content;
		}
    }



}