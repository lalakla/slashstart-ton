<?php

$stories = [];
$showStories = false;
// Если магазин 2.0 
if (isset($config['main']['is_shop2']) && $config['main']['is_shop2']){

    foreach ($config['shop2']['story'] as $story):
        if ($story['photo']==''){ continue; }

        // Если категория выбрана
        if (!empty($category)){
            if ($story['ref'] == $category['id']){
                $stories[] = $story;
                $showStories = true;
            }
        }

        // Если продукт выбран
        if (('p'.$product['id']) == $story['ref']){
            $stories[] = $story;
            $showStories = true;
        }
    endforeach;
}


if (!empty($product['gallery']))
	$product['gallery'] = (array)json_decode($product['gallery'], true);

?>

<div class="breadcrumbs">
	<div class="bc-item"><a class="app-query app-query-index-0" data-method="index" data-param-id="0" href="#"><?= __('Главная'); ?></a></div>
	<?php if (!empty($category)): ?>
		<div class="bc-item"><a class="app-query app-query-catalog-<?= $category['id']; ?>" data-method="catalog" data-param-id="<?= $category['id']; ?>" href="#"><?= hst($category['title']); ?></a></div>
	<?php endif ?>
	<div class="bc-item"><h1><?= hst($product['title']); ?></h1></div>
</div>




<?php 
	
	$pstyle = $config['product']['style'] ?? '';

	$pclasses = [];

	if (!empty($config['product']['nopanels']))
		$pclasses[] = 'product-nopanels';

	if (!empty($config['product']['resize_photo']))
		$pclasses[] = 'product-rsphoto';



?>

	<div class="container-fluid <?= join(' ', $pclasses); ?> page-product-page <?php if ($pstyle == 'photol'): ?>product-page-photol<?php endif ?>">

		<?php if ($pstyle == 'cslider' || $pstyle == 'spreview'): ?>

			<div class="row product-item product-photo-block">

				<div class="col-12">


					<?php if (empty($product['gallery'])): ?>

						<div class="product-page-photo">
							<?php if ($product['photo']): ?>
								<img src="<?= hst($product['photo']); ?>">
							<?php endif ?>
						</div>


					<?php else: ?>

						<div id="app-carousel" class="app-carousel carousel slide" data-bs-ride="carousel">
						  <div class="carousel-indicators">
						  	<?php $n = 0; if ($product['photo']): $n = 1; ?>
								<button type="button" data-bs-target="#app-carousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label=""></button>
							<?php endif ?>
						  	<?php foreach ($product['gallery'] as $i): ?>
								<?php if (!empty($i)): $n++; ?>
						    		<button type="button" data-bs-target="#app-carousel" data-bs-slide-to="<?= $n-1; ?>" <?php if ($n == 1 && empty($product['photo'])): ?>class="active"<?php endif ?> aria-current="true" aria-label=""></button>
						    	<?php endif ?>
							<?php endforeach ?>
						  </div>
						  <div class="carousel-inner">
						  	<?php $n = 0; if ($product['photo']): $n = 1; ?>
								<div class="carousel-item active">
									<img src="<?= hst($product['photo']); ?>">
								</div>
							<?php endif ?>
						  	<?php foreach ($product['gallery'] as $i): ?>
								<?php if (!empty($i)): $n++; ?>
								    <div class="carousel-item <?php if ($n == 1 && empty($product['photo'])): ?>active<?php endif ?>">
								    	<img src="<?= $i; ?>" class="d-block w-100 rounded" alt="">
								    </div>
							    <?php endif ?>
							<?php endforeach ?>
						  </div>
						  <button class="carousel-control-prev" type="button" data-bs-target="#app-carousel" data-bs-slide="prev">
						    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
						    <span class="visually-hidden"></span>
						  </button>
						  <button class="carousel-control-next" type="button" data-bs-target="#app-carousel" data-bs-slide="next">
						    <span class="carousel-control-next-icon" aria-hidden="true"></span>
						    <span class="visually-hidden"></span>
						  </button>
						</div>
						<?php if ($pstyle == 'spreview'): ?>

						  	<div class="gallery-previews inline">
								<?php $n = 0; if ($product['photo']): $n = 1; ?>
									<a class="gallery-preview active" data-bs-target="#app-carousel" data-bs-slide-to="0" style="background-image: url(<?= hst($product['photo']); ?>);" data-src="<?= hst($product['photo']); ?>"></a>
								<?php endif ?>
								<?php foreach ($product['gallery'] as $p): $n++; ?>
									<a class="gallery-preview <?php if ($n == 1 && empty($product['photo'])): ?>active<?php endif ?>" data-bs-target="#app-carousel" data-bs-slide-to="<?= $n-1; ?>" style="background-image: url(<?= hst($p); ?>);" data-src="<?= hst($p); ?>"></a>
								<?php endforeach ?>
							</div>
						  	
						  <?php endif ?>

					<?php endif; ?>



					


					

				</div>

			</div>
		
		<?php elseif ($pstyle == 'photol'): ?>
			
			<div class="row product-item">

				<div class="col-12">

					<div class="product-page-photo">


						<?php if ($product['photo']): ?>
							<img src="<?= hst($product['photo']); ?>">
						<?php endif ?>

						<p class="mb-2 text-color"><strong><?= hst($product['title']); ?></strong></p>

						<p class="text-color"><?= hst($product['summary']); ?></p>

					</div>

				</div>

			</div>

		<?php else: ?>



			<?php if ($product['photo']): ?>
				<div class="row product-item product-page-photo product-photo-block">
					<?php if ($product['gallery']): ?>
						<div class="col-2">
							<div class="gallery-previews">
								<?php if ($product['photo']): ?>
									<a class="gallery-preview" style="background-image: url(<?= hst($product['photo']); ?>);" data-src="<?= hst($product['photo']); ?>"></a>
								<?php endif ?>
								<?php foreach ($product['gallery'] as $p): ?>
									<a class="gallery-preview" style="background-image: url(<?= hst($p); ?>);" data-src="<?= hst($p); ?>"></a>
								<?php endforeach ?>
							</div>
						</div>
						<div class="col-10">
							<div class="product-photo">
								<?php if ($product['photo']): ?>
									<div href="#<?= hst($product['id']); ?>" class="product-photo-image product-photo-image-full" style="background-image: url(<?= hst($product['photo']); ?>);"></div>
								<?php endif ?>
							</div>
						</div>

					<?php else: ?>
						<div class="col-12">
							<div class="product-photo">
								<?php if ($product['photo']): ?>
									<div href="#<?= hst($product['id']); ?>" class="product-photo-image product-photo-image-full" style="background-image: url(<?= hst($product['photo']); ?>);"></div>
								<?php endif ?>
							</div>
						</div>
					<?php endif ?>
					
				</div>
			<?php endif ?>

		<?php endif ?>

        <div class="row product-item">
            <div class="col-12">
                <?php if ($pstyle != 'photol'): ?>
                    <p class="mb-2 text-color"><strong><?= hst($product['title']); ?></strong></p>
                    
                    <div class="product-summary text-color"><?= hst($product['summary']); ?></div>	
                <?php endif ?>

                <?php if (!empty($variantes)): ?>
                    <div class="product-variantes text-color" data-target=".cart-add-<?= $product['id']; ?>" data-target-price=".price-value-<?= $product['id']; ?>">
                        <?php foreach ($variantes as $k=>$v): ?>
                            <div class="form-check">
                                <input class="form-check-input product-variant product-variant-v<?= $v['id']; ?>" type="radio" data-price="<?= $v['price']; ?>" data-id="<?= $v['id']; ?>" name="variant-<?= $product['id']; ?>" id="variant-p<?= $v['id']; ?>" >
                                <label class="form-check-label" for="variant-p<?= $v['id']; ?>">
                                <?= hst($v['title']); ?>

                                &mdash;

                                <span class="variant-price">
                                    <?php if ($v['price_old']): ?>
                                        <span class="price-old">
                                            <?php if (!empty($config['products']['currency_left'])): ?>
                                            <span class="price-label"><?= hst($config['products']['currency']) ?? ''; ?></span>
                                            <?php endif ?>

                                            <?= pf($v['price_old'], $config['products']['currency_dec'] ?? 0, $config['products']['currency_dsep'] ?? null, $config['products']['currency_tsep'] ?? null); ?>

                                            <?php if (empty($config['products']['currency_left'])): ?>
                                            <span class="price-label"><?= hst($config['products']['currency']) ?? ''; ?></span>
                                            <?php endif ?>

                                        </span>
                                    <?php endif ?>


                                    <?php if (!empty($config['products']['currency_left'])): ?>
                                    <span class="price-label"><?= hst($config['products']['currency']) ?? ''; ?></span>
                                    <?php endif ?>


                                    <span class="price-value"><?= pf($v['price'], $config['products']['currency_dec'] ?? 0, $config['products']['currency_dsep'] ?? null, $config['products']['currency_tsep'] ?? null); ?></span>

                                    <?php if (empty($config['products']['currency_left'])): ?>
                                    <span class="price-label"><?= hst($config['products']['currency']) ?? ''; ?></span>
                                    <?php endif ?>
                                </span>

                                <span class="product-cart-count-badge product-cart-count-badge-v<?= $v['id']; ?>"></span>


                                </label>
                            </div>



                        <?php endforeach ?>
                    </div>
                <?php endif ?>

                <div class="product-order">

                    <?php if (empty($config['products']['prices_hide'])): ?>


                        <div class="product-price text-color">
                            <?php if ($product['price_old']): ?>
                                <div class="price-old">
                                    <?php if (!empty($config['products']['currency_left'])): ?>
                                    <span class="price-label"><?= hst($config['products']['currency']) ?? ''; ?></span>
                                    <?php endif ?>

                                    <?= pf($product['price_old'], $config['products']['currency_dec'] ?? 0, $config['products']['currency_dsep'] ?? null, $config['products']['currency_tsep'] ?? null); ?>

                                    <?php if (empty($config['products']['currency_left'])): ?>
                                    <span class="price-label"><?= hst($config['products']['currency']) ?? ''; ?></span>
                                    <?php endif ?>

                                </div>
                            <?php endif ?>


                            <?php if (!empty($config['products']['currency_left'])): ?>
                            <span class="price-label"><?= hst($config['products']['currency']) ?? ''; ?></span>
                            <?php endif ?>


                            <span class="price-value price-value-<?= $product['id']; ?>"><?= pf($product['price'], $config['products']['currency_dec'] ?? 0, $config['products']['currency_dsep'] ?? null, $config['products']['currency_tsep'] ?? null); ?></span>

                            <?php if (empty($config['products']['currency_left'])): ?>
                            <span class="price-label"><?= hst($config['products']['currency']) ?? ''; ?></span>
                            <?php endif ?>
                        </div>
                    <?php endif ?>

                    <?php if (!empty($product['url'])): ?>
                        <a class="btn btn-success btn-xs" href="<?= $product['url']; ?>"<?php if (!empty($product['url'])): ?> target="_blank"<?php endif ?>><?= $config['products']['cart_btn_label'] ?? __('Подробнее'); ?></a>
                    <?php else: ?>
                        <button class="btn btn-success btn-xs product-tocart cart-add cart-add-<?= $product['id']; ?>" data-product-id="<?= $product['id']; ?>" data-product-price="<?= $product['price']; ?>" <?php if (!empty($variantes)): ?>data-<?php endif ?>><?= $config['products']['cart_btn_label'] ?? __('В корзину'); ?></button>
                    <?php endif ?>

                    <span class="product-cart-count-badge product-cart-count-badge-<?= $product['id']; ?>"></span>

                </div>
            </div>
        </div>

        <?php if (!empty($product['content'])): ?>
            <div class="row product-item">
                <div class="col-12 text-color"><?= str_replace(['<iframe', '</iframe>'], ['<div class="video-responsive"><iframe', '</iframe></div>'], $product['content']); ?></div>
            </div>
        <?php endif ?>

        <!-- Если магазин 2.0 -->
        <?php if (isset($config['main']['is_shop2']) && $config['main']['is_shop2']): ?>
            <link rel="stylesheet" href="/assets/css/widgets.css">

            <div class="shop-page-demo-shop2">
                <?php if ($showStories): ?>
                    <div class="shop2-stories">
                        <?php foreach ($stories as $story): ?>
                            <?php if ($story['photo']==''){ continue; } ?>
                            <a class="shop2-story-item" href="<?=$story['href']?>"> <!-- saw -->
                                <img class="shop2-story-bg" src="<?=(isset($story['photo'])?h($story['photo']):'/assets/services/shop2/w1.jpg')?>" width="100%" height="100%" />
                                <!--div class="shop2-story-title">Демо текст</div--> 
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif ?>
            </div>
        <?php endif; ?>

	</div>

