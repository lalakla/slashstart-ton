<?php

$current = $_tplData['nav_from'] ?? 'shop';

$uri = service('uri');
$baseQuery = $uri->getQuery();


?>


<div class="toggle-wrap nk-block-tools-toggle">
    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-menu-alt-r"></em></a>
    <div class="toggle-expand-content" data-content="pageMenu">
        <ul class="nk-block-tools g-3">

				<li>
			        <div class="drodown">
			        	<a href="#" class="dropdown-toggle btn btn-white btn-dim btn-outline-light" data-bs-toggle="dropdown"><em class="d-none d-sm-inline icon ni ni-user-check"></em><span><?= $currentShop['title']; ?></span></a>
			        	<div class="dropdown-menu dropdown-menu-end">
							<ul class="link-list-opt nk-ibx-label no-bdr">
								<?php foreach ($shops as $s): ?>
									<li><a class="nk-fmg-menu-item" href="?shop_id=<?= $s['hash']; ?>"><span class="nk-fmg-menu-text"><?= $s['title']; ?></span></a></li>
								<?php endforeach ?>
								<li class="divider"></li>
								<li>
									<?= sprintf(__('apps.shop_create_new_shop_help', '<a href="%s">Добавить магазин</a>'), '/services?section=available#app-shop'); ?>
								</li>
							</ul>
						</div>
			        </div>
		        </li>
		                        	

			<?php if ($current == 'shop' || $current == 'product'): ?>
				<li>
					<a href="#import" data-bs-toggle="modal" data-bs-target="#shop-import-pp" class="btn btn-outline btn-outline-info"><span><?= __('apps.shop_import_btn', 'Import'); ?></span></a>
				</li>

				<li>
					<a href="/apps/shop/product?shop_id=<?= $currentShop['hash']; ?>" class="btn btn-primary"><em class="icon ni ni-plus"></em><span><?= __('apps.shop_add_product_btn', 'Add a Product'); ?></span></a>
				</li>

			<?php elseif ($current == 'categories' || $current == 'category'): ?>
				<li>
					<a href="/apps/shop/category?shop_id=<?= $currentShop['hash']; ?>" class="btn btn-primary"><em class="icon ni ni-plus"></em><span><?= __('apps.shop_add_category_btn', 'Add a Category'); ?></span></a>
				</li>
			<?php endif ?>

        </ul>
    </div>
</div><!-- .toggle-wrap -->

