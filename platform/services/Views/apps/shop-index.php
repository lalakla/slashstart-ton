<!-- Если магазин 2.0 -->
<?php if (isset($config['main']['is_shop2']) && $config['main']['is_shop2']): ?>
    <link rel="stylesheet" href="/assets/css/widgets.css">
    <div class="shop-page-demo-shop2">
        <div class="shop2-avatar">
            <div class="shop2-user">
                <div class="shop2-uimg">
                    <?php if ($currentSubscriber['photo']!=''): ?>
                        <img src="<?=$currentSubscriber['photo']?>" width="40px" height="40px" />
                    <?php else: ?>
                        <i class="fa fa-user"></i>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="shop2-uname"><?=$currentSubscriber['name']?>  <i class="fa fa-angle-right"></i></div>
                    <div class="shop2-ulevel">Новичок</div>
                </div>
            </div>
            <a class="shop2-channel" href="<?=h($config['shop2']['channel']['href'])?>" style="background-color: <?= h($config['styles']['shop2_channel_bg'] ?? '#efefef'); ?>; color: <?= h($config['styles']['shop2_channel_color'] ?? 'inherit'); ?>;">
                <div class="shop2-timg">
                    <img src="<?=(isset($config['shop2']['channel']['photo'])?h($config['shop2']['channel']['photo']):'/assets/services/shop2/telegram.svg')?>" width="40" height="40" />
                </div>
                <div class="shop2-tdescr-block">
                    <div class="shop2-tnick"><?=(isset($config['shop2']['channel']['title'])?$config['shop2']['channel']['title']:'@telegram')?></div>
                    <div class="shop2-tdescr"><?=(isset($config['shop2']['channel']['description'])?$config['shop2']['channel']['description']:'наш Telegram-канал')?></div>
                </div>
            </a>
        </div>

        <div class="shop2-search">
            <input type="text" class="form-control" placeholder="Поиск..." />
        </div>

        <?php 
            // Проверка ленты
            $showStories = false; 
            if (isset($config['shop2']['story'])):
                foreach ($config['shop2']['story'] as $story):
                    if ($story['photo']==''){ continue; }
                    $showStories = true;
                endforeach;
            endif;
        ?>

        <?php if ($showStories): ?>
            <div class="shop2-stories">
                <?php if (isset($config['shop2']['story'])): ?>
                    <?php foreach ($config['shop2']['story'] as $story): ?>
                        <?php if ($story['photo']==''){ continue; } ?>
                        <a class="shop2-story-item" href="<?=$story['href']?>"> <!-- saw -->
                            <img class="shop2-story-bg" src="<?=(isset($story['photo'])?h($story['photo']):'/assets/services/shop2/w1.jpg')?>" width="100%" height="100%" />
                            <!--div class="shop2-story-title">Демо текст</div--> 
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif ?>
    </div>
<?php endif; ?>

<?php if (!empty($config['slider']['enabled'])): $count = $config['slider']['banners_cols'] ?? 1; ?>
	<div class="shop-home-slider">
		<?php if (empty($config['slider']['bannermode'])): ?>
			<div id="app-carousel" class="app-carousel carousel slide" data-bs-ride="carousel">
			  <div class="carousel-indicators">
			  	<?php $n = 0; foreach ($config['slider']['items'] as $i): ?>
					<?php if (!empty($i['photo'])): $n++; ?>
			    		<button type="button" data-bs-target="#app-carousel" data-bs-slide-to="<?= $n-1; ?>" <?php if ($n == 1): ?>class="active"<?php endif ?> aria-current="true" aria-label=""></button>
			    	<?php endif ?>
				<?php endforeach ?>
			  </div>
			  <div class="carousel-inner">
			  	<?php $n = 0; foreach ($config['slider']['items'] as $i): ?>
					<?php if (!empty($i['photo'])): $n++; ?>
					    <div class="carousel-item <?php if ($n == 1): ?>active<?php endif ?>">
					    	<?php if (empty($i['link'])): ?>
					    		<?php if (!empty($i['href'])): ?>
					    			<a href="<?= $i['href']; ?>"><img src="<?= $i['photo']; ?>" class="d-block w-100 rounded" alt=""></a>
					    		<?php else: ?>
					    			<img src="<?= $i['photo']; ?>" class="d-block w-100 rounded" alt="">	
					    		<?php endif ?>
					      	<?php else: ?>
					      		<a href="#" class="app-query" data-method="<?= $i['link'][0] == 'p' ? 'product' : 'catalog' ?>" data-param-id="<?= str_replace('p', '', $i['link']); ?>"><img src="<?= $i['photo']; ?>" class="d-block w-100 rounded" alt=""></a>
							<?php endif ?>
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

		<?php else: ?>
			<div class="shop-banners shop-banners-<?= $count; ?>">
				<?php if (!empty($config['slider']['bannermode'])):  ?>
					<div class="row">
						<?php $n = 0; foreach ($config['slider']['items'] as $i): ?>
							<?php if (!empty($i['photo'])): $n++; ?>
								<div class="col">
									<?php if (preg_match('#\.(mp4|mov)$#si', $i['photo'])): ?>
										<div class="video-responsive">
											<video autoplay muted width="100%">
											    <source src="<?= $i['photo']; ?>" type="video/mp4" />
											</video>
										</div>
									<?php else: ?>
									

										<?php if (!empty($i['link'])): ?>
											<a href="#" class="app-query" data-method="<?= $i['link'][0] == 'p' ? 'product' : 'catalog' ?>" data-param-id="<?= str_replace('p', '', $i['link']); ?>"><img src="<?= $i['photo']; ?>" alt="" class="w-100 rounded"></a>
										<?php else: ?>
											<?php if (!empty($i['href'])): ?>
								    			<a href="<?= $i['href']; ?>"><img src="<?= $i['photo']; ?>" alt="" class="w-100 rounded"></a>
								    		<?php else: ?>
								    			<img src="<?= $i['photo']; ?>" alt="" class="w-100 rounded">
								    		<?php endif ?>
											
										<?php endif ?>
									<?php endif ?>
			    				</div>
			    				<?php if ($n%$count == 0): ?>
			    					</div>
			    					<div class="row">
			    				<?php endif ?>
							<?php endif ?>
						<?php endforeach ?>
					</div>
				<?php endif ?>
			</div>
		<?php endif ?>
	</div>
<?php endif ?>

<!-- Если магазин 2.0 -->
<?php if (isset($config['main']['is_shop2']) && $config['main']['is_shop2']): ?>
    <div class="shop-page-demo-shop2">
        <div class="shop2-buttons mt-2">
            <div class="row">
                <div class="col-6">
                    <a href="<?=($config['shop2']['points']['link'] ?? '')?>" class="shop2-points" style="background-image: url(<?=h($config['shop2']['points']['photo'] ?? '') ?>); color: <?= h($config['styles']['shop2_points_color'] ?? 'inherit'); ?>;">
                        <div class="d-flex">
                            <div class="shop2-points-title"><?=$config['shop2']['points']['title']??'Баллы'?></div>
                            <div class="shop2-points-title-icon">
                                <i class="fa fa-angle-right"></i>
                            </div>
                        </div>
                        <div class="shop2-points-value">
                            <span class="currency-symbol"><?=$config['shop2']['points']['currency']??'₽'?></span>
                            <span>0</span>
                        </div>
                    </a>
                    <a href="<?=($config['shop2']['referal']['link'] ?? '')?>" class="shop2-referal" style="background-image: url(<?=h($config['shop2']['referal']['photo'] ?? '') ?>);">
                    </a>
                </div>
                <div class="col-6">
                    <a href="<?=($config['shop2']['order']['link'] ?? '')?>" class="shop2-order" style="background-image: url(<?=h($config['shop2']['order']['photo'] ?? '') ?>);">
                    </a>
                </div>
                <?php if (($config['shop2']['calculator']['link'] ?? '')!=''): ?>
                    <div class="col-12 mt-2">
                        <a href="<?=($config['shop2']['calculator']['link'] ?? '')?>" class="shop2-calculator">
                            <img src="<?=h($config['shop2']['calculator']['photo'] ?? '') ?>" />
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Если не магазин 2.0 -->
<?php if (!isset($config['main']['is_shop2']) || (isset($config['main']['is_shop2']) && !$config['main']['is_shop2'])): ?>
    <?php if (!empty($catalog) && empty($config['main']['disable_home_cats'])):
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
                    <?php $n = 0; foreach ($catalog as $k=>$v): $n++; ?>

                        <div class="col-4 product-item category-item <?= join(' ', $cclasses); ?>">
                            <div class="product-photo">
                                <?php if ($v['photo']): ?>
                                    <a href="#<?= hst($v['id']); ?>" class="product-photo-image app-query" data-method="catalog" data-param-id="<?= $v['id']; ?>" style="background-image: url(<?= hst($v['photo']); ?>);"></a>
                                <?php endif ?>
                            </div>

                            <div class="product-title item-title"><a href="#<?= hst($v['id']); ?>" class="product-photo-image app-query" data-method="catalog" data-param-id="<?= $v['id']; ?>"><?= hst($v['title']); ?></a></div>
                        </div>

                        <?php if ($n%3 == 0): ?>
                            </div>
                            <div class="row category-items-row">
                        <?php endif ?>

                    <?php endforeach ?>
                </div>
            </div>
        </div>
    <?php endif ?>
<?php endif; ?>

<?php if (!empty($products)): ?>
	<div class="shop-home-products">
		<?= view('Services\Views\apps/shop-catalog', ['category' => ['title' => $config['products']['home_title'] ?? '']]); ?>
	</div>
<?php endif ?>

<?php if (!empty($config['home_html'])): ?>
	<?= $config['home_html']; ?>	
<?php endif ?>

