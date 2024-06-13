<?php

// $current = $_tplData['nav_from'] ?? 'flows';

$request = service('request');
$current = $request->uri->getSegment(3, 'shop');

?>

<ul class="nav nav-tabs nav-tabs-s2 justify-content-start g">
	<li class="nav-item"><a href="/apps/shop?shop_id=<?= $currentShop['hash']; ?>" class="nav-link<?= $current == 'shop' ? ' active' : ''; ?>"><?= __('app.menu_products', 'Products'); ?></a></li>
	<li class="nav-item"><a href="/apps/shop/orders?shop_id=<?= $currentShop['hash']; ?>" class="nav-link<?= $current == 'orders' ? ' active' : ''; ?>"><?= __('app.menu_orders', 'Orders'); ?></a></li>
	<li class="nav-item"><a href="/apps/shop/categories?shop_id=<?= $currentShop['hash']; ?>" class="nav-link<?= $current == 'categories' ? ' active' : ''; ?>"><?= __('app.menu_categories', 'Categories'); ?></a></li>
	<li class="nav-item"><a href="/apps/shop/config?shop_id=<?= $currentShop['hash']; ?>" class="nav-link<?= $current == 'config' ? ' active' : ''; ?>"><?= __('app.menu_settings', 'Settings'); ?></a></li>
</ul>


