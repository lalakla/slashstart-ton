<?php

$view = $config['products']['view'] ?? 'list';


?>

<?php if (!empty($category['title'])): ?>
	<div class="breadcrumbs">
		<div class="bc-item"><a class="app-query app-query-index-0" data-method="index" data-param-id="0" href="#"><?= __('Главная'); ?></a></div>
		<?php if (!empty($parentCategory)): ?>
			<div class="bc-item"><a class="app-query app-query-catalog-<?= $parentCategory['id']; ?>" data-method="catalog" data-param-id="<?= $parentCategory['id']; ?>" href="#"><?= hst(html_entity_decode($parentCategory['title'])); ?></a></div>
		<?php endif ?>
		<div class="bc-item"><h1><?= hst(html_entity_decode($category['title'])); ?> </h1></div>
	</div>
<?php endif ?>


<?php if (!empty($childrenCategories)):


$cstyle = $config['categories']['style'] ?? '';
$cclasses = [];
if ($cstyle)
	$cclasses[] = 'style-' . $cstyle;

if (!empty($config['categories']['noround']))
	$cclasses[] = 'style-noround';

if (!empty($config['categories']['titlemargin']))
	$cclasses[] = 'style-titlemargin';


?>
<div class="shop-home-catalog">
	<div class="view-grid3">
		<div class="row category-items-row">
			<?php $n = 0; foreach ($childrenCategories as $k=>$v): $n++; ?>

				<div class="col-4 product-item category-item <?= join(' ', $cclasses); ?>">
					<div class="product-photo">
						<?php if ($v['photo']): ?>
							<a href="#<?= hst($v['id']); ?>" class="product-photo-image app-query" data-method="catalog" data-param-id="<?= $v['id']; ?>" style="background-image: url(<?= hst($v['photo']); ?>);"></a>
						<?php endif ?>
					</div>

					<div class="product-title item-title"><a href="#<?= hst($v['id']); ?>" class="product-photo-image app-query" data-method="catalog" data-param-id="<?= $v['id']; ?>"><?= hst(html_entity_decode($v['title'])); ?></a></div>
				</div>

				<?php if ($n%3 == 0): ?>
    				</div>
    				<div class="row category-items-row">
    			<?php endif ?>

			<?php endforeach ?>
		</div>
	</div>
</div>
<br>
<?php endif ?>

<?php if (empty($products) && empty($childrenCategories)): ?>
	<!-- <div class="alert alert-info small"><?= __('Раздел заполняется. Пока здесь пусто.'); ?></div> -->
<?php else: ?>



	<?php if ($view == 'list'): ?>

		<div class="container-fluid">

			<?php foreach ($products ?? [] as $k=>$product): ?>
				<div class="row product-item">
					<div class="col-4 p-0">
						<div class="product-photo cb-container">
							<?php if (empty($config['main']['cashback_disabled'])): ?>
								<?php if (!empty($product['cashback_total'])): ?>
									<div class="cb-label"><span><?= $product['cashback_total']; ?></span></div>
								<?php endif ?>
							<?php endif ?>
							<?php if ($product['photo']): ?>
								<?php if (empty($config['products']['product_page_disable']) || !empty($config['products']['cart_btn_disable'])): ?>
									<a href="#<?= hst($product['id']); ?>" class="product-photo-image app-query" data-method="product" data-param-id="<?= $product['id']; ?>" style="background-image: url(<?= hst($product['photo']); ?>);"></a>
								<?php else: ?>
									<div href="#<?= hst($product['id']); ?>" class="product-photo-image" style="background-image: url(<?= hst($product['photo']); ?>);"></div>
								<?php endif ?>
							<?php endif ?>
						</div>
					</div>
					<div class="col-8">
						<div class="product-title">
							<?php if (empty($config['products']['product_page_disable']) || !empty($config['products']['cart_btn_disable'])): ?>
								<a href="#<?= hst($product['id']); ?>" class="app-query" data-method="product" data-param-id="<?= $product['id']; ?>"><?= hst($product['title']); ?></a>
							<?php else: ?>
								<?= hst($product['title']); ?>
							<?php endif ?>
						</div>
						<div class="product-summary"><?= hst($product['summary']); ?></div>

						<?php if (empty($config['products']['cart_btn_disable'])): ?>
						<?php if (!empty($product['variantes'])): ?>
							<div class="product-variantes" data-target=".cart-add-<?= $product['id']; ?>" data-target-price=".price-value-<?= $product['id']; ?>">
								<?php foreach ($product['variantes'] as $k2=>$v): ?>
									<div class="form-check">
									  <input class="form-check-input product-variant product-variant-v<?= $v['id']; ?>" type="radio" data-price="<?= $v['price']; ?>" data-id="<?= $v['id']; ?>" name="variant-<?= $product['id']; ?>" id="variant-p<?= $v['id']; ?>" >
									  <label class="form-check-label" for="variant-p<?= $v['id']; ?>">
									    <?= hst($v['title']); ?>

									    &mdash;

									    <span class="variant-price">
											<?php if ($v['price_old']): ?>
												<span class="price-old">
													<?php if (!empty($config['products']['currency_left'])): ?>
													<span class="price-label"> <?= hst($config['products']['currency']) ?? ''; ?> </span>
													<?php endif ?>

													<?= pf($v['price_old'], $config['products']['currency_dec'] ?? 0, $config['products']['currency_dsep'] ?? null, $config['products']['currency_tsep'] ?? null); ?>

													<?php if (empty($config['products']['currency_left'])): ?>
													<span class="price-label"> <?= hst($config['products']['currency']) ?? ''; ?> </span>
													<?php endif ?>

												</span>
											<?php endif ?>

											<?php if ($v['price']): ?>

												<?php if (!empty($config['products']['currency_left'])): ?>
												<span class="price-label"> <?= hst($config['products']['currency']) ?? ''; ?> </span>
												<?php endif ?>


												<span class="price-value"><?= pf($v['price'], $config['products']['currency_dec'] ?? 0, $config['products']['currency_dsep'] ?? null, $config['products']['currency_tsep'] ?? null); ?></span>

												<?php if (empty($config['products']['currency_left'])): ?>
												<span class="price-label"> <?= hst($config['products']['currency']) ?? ''; ?> </span>
												<?php endif ?>

											<?php endif ?>
										</span>

										<span class="product-cart-count-badge product-cart-count-badge-v<?= $v['id']; ?>"></span>


									  </label>
									</div>



								<?php endforeach ?>
							</div>
						<?php endif ?>
						<?php endif ?>

						<div class="product-order">

							<?php if (empty($config['products']['prices_hide'])): ?>


								<div class="product-price">
									<?php if ($product['price_old']): ?>
										<div class="price-old">
											<?php if (!empty($config['products']['currency_left'])): ?>
											<span class="price-label"> <?= hst($config['products']['currency']) ?? ''; ?> </span>
											<?php endif ?>

											<?= pf($product['price_old'], $config['products']['currency_dec'] ?? 0, $config['products']['currency_dsep'] ?? null, $config['products']['currency_tsep'] ?? null); ?>

											<?php if (empty($config['products']['currency_left'])): ?>
											<span class="price-label"> <?= hst($config['products']['currency']) ?? ''; ?> </span>
											<?php endif ?>

										</div>
									<?php endif ?>


									<?php if ($product['price']): ?>

										<?php if (!empty($config['products']['currency_left'])): ?>
										<span class="price-label"> <?= hst($config['products']['currency'] ?? '') ?? ''; ?> </span>
										<?php endif ?>


										<span class="price-value price-value-<?= $product['id']; ?>"><?= pf($product['price'], $config['products']['currency_dec'] ?? 0, $config['products']['currency_dsep'] ?? null, $config['products']['currency_tsep'] ?? null); ?></span>

										<?php if (empty($config['products']['currency_left'])): ?>
										<span class="price-label"> <?= hst($config['products']['currency'] ?? '') ?? ''; ?> </span>
										<?php endif ?>

									<?php endif ?>
								</div>
							<?php endif ?>

							<?php if (empty($config['products']['cart_btn_disable'])): ?>

								<?php if (!empty($product['url'])): ?>
									<a class="btn btn-success btn-xs" href="<?= $product['url']; ?>"<?php if (!empty($product['url'])): ?> target="_blank"<?php endif ?>><?= $config['products']['cart_btn_label'] ?? __('Подробнее'); ?></a>
								<?php else: ?>
									<?php if (!empty($config['products']['shop_show_qty_inp'])): ?>
										<div class="cart-pr-cat-count-wrr">
											<span>&times;</span>
											<input type="text" value="" placeholder="1" class="cart-pr-cat-count cart-pr-cat-count-<?= $product['id']; ?>">
										</div>
									<?php endif ?>

									<button class="btn btn-success btn-xs product-tocart cart-add cart-add-<?= $product['id']; ?>" data-product-id="<?= $product['id']; ?>" data-product-price="<?= $product['price']; ?>" data-count=".cart-pr-cat-count-<?= $product['id']; ?>"><?= $config['products']['cart_btn_label'] ?? __('В корзину'); ?></button>
								<?php endif ?>
								
							<?php endif ?>
							<span class="product-cart-count-badge product-cart-count-badge-<?= $product['id']; ?>"></span>

						</div>
					</div>
				</div>

			<?php endforeach ?>


		</div>
	<?php elseif ($view == 'grid' || $view == 'grid2'):

		$count = $view == 'grid2' ? 2 : 3;


		$pstyle = $config['products']['style'] ?? '';
		$pclasses = [];
		if ($pstyle)
			$pclasses[] = 'style-' . $pstyle;

		if (!empty($config['products']['nopaddings']))
			$pclasses[] = 'style-nopadd';

		if (!empty($config['products']['noround']))
			$pclasses[] = 'style-noround';

		if (!empty($config['products']['nopbgr']))
			$pclasses[] = 'style-nopbgr';


		

		


	?>

		<div class="view-grid<?= $count; ?>">

				<div class="row">
					<?php $n = 0; foreach ($products as $k=>$product): $n++; ?>

						<div class="col-<?= $count == 2 ? 6 : 4; ?> product-item item-product <?= join(' ', $pclasses); ?>">
							<div class="product-photo-title">
								<div class="product-photo cb-container">
									<?php if (empty($config['main']['cashback_disabled'])): ?>
										<?php if (!empty($product['cashback_total'])): ?>
											<div class="cb-label"><span><?= $product['cashback_total']; ?></span></div>
										<?php endif ?>
									<?php endif ?>
									<?php if ($product['photo']): ?>
										<?php if (empty($config['products']['product_page_disable']) || !empty($config['products']['cart_btn_disable'])): ?>
											<a href="#<?= hst($product['id']); ?>" class="product-photo-image app-query" data-method="product" data-param-id="<?= $product['id']; ?>" style="background-image: url(<?= hst($product['photo']); ?>);"></a>
										<?php else: ?>
											<div href="#<?= hst($product['id']); ?>" class="product-photo-image" style="background-image: url(<?= hst($product['photo']); ?>);"></div>
										<?php endif ?>
									<?php endif ?>
								</div>
							</div>


							<div class="product-summ">
								<div class="product-title">
									<?php if (empty($config['products']['product_page_disable']) || !empty($config['products']['cart_btn_disable'])): ?>
										<a href="#<?= hst($product['id']); ?>" class="app-query" data-method="product" data-param-id="<?= $product['id']; ?>"><?= hst($product['title']); ?></a>
									<?php else: ?>
										<?= hst($product['title']); ?>
									<?php endif ?>
								</div>


								<div class="product-order">

									<?php if (empty($config['products']['prices_hide'])): ?>


										<div class="product-price">
											<?php if ($product['price_old']): ?>
												<div class="price-old">
													<?php if (!empty($config['products']['currency_left'])): ?>
													<span class="price-label"> <?= hst($config['products']['currency']) ?? ''; ?> </span>
													<?php endif ?>

													<?= pf($product['price_old'], $config['products']['currency_dec'] ?? 0, $config['products']['currency_dsep'] ?? null, $config['products']['currency_tsep'] ?? null); ?>

													<?php if (empty($config['products']['currency_left'])): ?>
													<span class="price-label"> <?= hst($config['products']['currency']) ?? ''; ?> </span>
													<?php endif ?>

												</div>
											<?php endif ?>

											<?php if ($product['price']): ?>

												<?php if (!empty($config['products']['currency_left'])): ?>
												<span class="price-label"> <?= hst($config['products']['currency']) ?? ''; ?> </span>
												<?php endif ?>


												<span class="price-value price-value-<?= $product['id']; ?>"><?= pf($product['price'], $config['products']['currency_dec'] ?? 0, $config['products']['currency_dsep'] ?? null, $config['products']['currency_tsep'] ?? null); ?></span>

												<?php if (empty($config['products']['currency_left'])): ?>
												<span class="price-label"> <?= hst($config['products']['currency']) ?? ''; ?> </span>
												<?php endif ?>

											<?php endif ?>
										</div>
									<?php endif ?>

									<?php if (empty($config['products']['cart_btn_disable'])): ?>
										<?php if (!empty($config['products']['shop_show_qty_inp'])): ?>
											<div class="cart-pr-cat-count-wrr">
												<input type="text" value="" placeholder="1" class="cart-pr-cat-count cart-pr-cat-count-<?= $product['id']; ?>">
											</div>
										<?php endif ?>

										<?php if (!empty($product['url'])): ?>
											<a class="btn btn-success btn-xs" href="<?= $product['url']; ?>"<?php if (!empty($product['url'])): ?> target="_blank"<?php endif ?>><?= $config['products']['cart_btn_label'] ?? __('Подробнее'); ?></a>
										<?php else: ?>
											<button class="btn btn-success btn-xs product-tocart cart-add cart-add-<?= $product['id']; ?>" data-product-id="<?= $product['id']; ?>" data-product-price="<?= $product['price']; ?>" data-count=".cart-pr-cat-count-<?= $product['id']; ?>">
												<?= $config['products']['cart_btn_label'] ?? __('В корзину'); ?>
												<span class="product-cart-count-badge product-cart-count-badge-<?= $product['id']; ?>"></span>
											</button>
										<?php endif ?>
									<?php endif ?>


								</div>

							</div>

						</div>

						<?php if ($n % $count == 0): ?>
		    				</div>
		    				<div class="row">
		    			<?php endif ?>

					<?php endforeach ?>
				</div>

		</div>


	<?php endif; ?>


<?php endif ?>