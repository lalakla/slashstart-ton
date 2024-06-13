<?php
	$version = '38';
	if (ENVIRONMENT == 'development')
	{
		$version = time();		
	}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="<?= PROJECT_NAME; ?>">
    <meta name="generator" content="<?= PROJECT_NAME; ?>">
    <title></title>
    <link rel="canonical" href="https://<?= HOST_CURRENT . REQUEST_URI; ?>">
	<link href="<?= STATIC_SERVER_URL ?>assets/bs/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?= STATIC_SERVER_URL ?>assets/css/apps.css?v<?= $version; ?>" rel="stylesheet">
	<!-- <meta name="theme-color" content="#7952b3"> -->
	<link href="<?= STATIC_SERVER_URL ?>assets/fa/css/font-awesome.min.css" rel="stylesheet" >

	<meta http-equiv="cache-control" content="max-age=0" />
	<meta http-equiv="cache-control" content="no-cache" />
	<meta http-equiv="expires" content="0" />
	<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
	<meta http-equiv="pragma" content="no-cache" />

    <script src="https://telegram.org/js/telegram-web-app.js"></script>

    <style type="text/css">
    	body {
    		background-color: <?= hst($config['styles']['page_bg'] ?? '#f5f5f5') ?>;
    		background-position: center center;
    		<?php if (!empty($config['styles']['page_bg_img'])): ?>
    			background-image: url(<?= hst($config['styles']['page_bg_img']); ?>);
    		<?php endif ?>
    		<?php if (!empty($config['styles']['page_bg_imgsize'])): ?>
    			background-size: <?= hst($config['styles']['page_bg_imgsize']); ?>;
    		<?php endif ?>
    	}
    	h1, .text-color, .bc-item a {
    		color: <?= hst($config['styles']['page_text_color'] ?? '#212529') ?>;
    	}
    	.navbar {
    		background-color: <?= hst($config['styles']['menu_bg'] ?? '#343a40') ?>;
    	}
    	.navbar-dark .navbar-nav .nav-link {
    		color: <?= hst($config['styles']['menu_a_color'] ?? '#b3b3b3') ?>;
    	}
    	.navbar-dark .navbar-nav .nav-link.active {
    		color: <?= hst($config['styles']['menu_a_color_active'] ?? '#efefef') ?>;
    	}
    	.cart-add, .btn-success {
    		background-color: <?= hst($config['styles']['cart_btn_bg'] ?? '#198754') ?>;
    		border-color: <?= hst($config['styles']['cart_btn_bg'] ?? '#198754') ?>;
    		color: <?= hst($config['styles']['cart_btn_text'] ?? '#ffffff') ?>;
    	}
    	.checkout-btn-styles {
    		background-color: <?= hst($config['styles']['cart_checkout_bg'] ?? '#198754') ?>;
    		border-color: <?= hst($config['styles']['cart_checkout_bg'] ?? '#198754') ?>;
    		color: <?= hst($config['styles']['cart_checkout_text'] ?? '#ffffff') ?>;
    	}
    	<?php if (!empty($config['styles']['category_bg'])): ?>
    		.category-item, .category-item .item-title {
    			background-color: <?= $config['styles']['category_bg']; ?>
    		}
    	<?php endif ?>
    	<?php if (!empty($config['styles']['category_title_bg'])): ?>
    		.category-item .item-title > span, .category-item .item-title > a {
    			background-color: <?= $config['styles']['category_title_bg']; ?>
    		}
    	<?php endif ?>
    	<?php if (!empty($config['styles']['category_title_text'])): ?>
    		.category-item .item-title > span, .category-item .item-title > a {
    			color: <?= $config['styles']['category_title_text']; ?>
    		}
    	<?php endif ?>

    </style>

    <?= $config['head']['codes'] ?? ''; ?>

    <?php 
    	$action = $_GET['action'] ?? '';
    ?>
  </head>
  <body>
    <div class="loader"><div class="loading"></div></div>

    <main>
        <?php if (!isset($config['main']['is_shop2']) || (isset($config['main']['is_shop2']) && !$config['main']['is_shop2'])): ?>
            <?php if (empty($config['menu']['disable']) || !empty($config['menu']['items'][0])): ?>
                <nav class="navbar navbar-dark" aria-label="">
                    <div class="container-fluid">
                        <?php if (empty($config['menu']['disable'])): ?>
                            <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample01" aria-controls="navbarsExample01" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span> <?= hst($config['menu']['title']??''); ?>
                            </button>
                        <?php endif ?>

                        <ul class="navbar-nav horizontal-nav app-menu-group">
                            <?php foreach ($config['menu']['items'] ?? [] as $k=>$v): if (!$v) continue; ?>
                                <?php if ($v['type'] == 'product'): ?>
                                    <li class="nav-item"><a class="nav-link app-query app-query-product-<?= $v['id']; ?>" data-method="product" data-param-id="<?= $v['id']; ?>" href="#"><?= hst(html_entity_decode($v['title'])); ?></a></li>
                                <?php else: ?>
                                    <li class="nav-item"><a class="nav-link app-query app-query-catalog-<?= $v['id']; ?>" data-method="catalog" data-param-id="<?= $v['id']; ?>" href="#"><?= hst(html_entity_decode($v['title'])); ?></a></li>
                                <?php endif ?>
                            <?php endforeach ?>
                        </ul>

                        <div class="collapse navbar-collapse" id="navbarsExample01">
                            <ul class="navbar-nav me-auto mb-2 app-menu-group">

                                <li class="nav-item">
                                    <form action="" class="app-query-form mt-3 mb-2" data-method="search">
                                        <input type="text" name="q" placeholder="<?= __('Поиск...'); ?>" class="w-100">
                                    </form>
                                </li>

                                <li class="nav-item"><a class="nav-link active app-query app-query-index-0" data-method="index" data-param-id="0" href="#"><?= __('На главную'); ?></a></li>
                                <?php foreach ($catalog as $k=>$v): ?>
                                    <?php if ($v['children']): ?>
                                        
                                        <li class="nav-item dropdown">
                                            <a class="nav-link dropdown-toggle" href="#" id="dropdown0<?= $k; ?>" data-bs-toggle="dropdown" aria-expanded="false"><?= hst(html_entity_decode($v['title'])); ?></a>
                                            <ul class="dropdown-menu" aria-labelledby="dropdown0<?= $k; ?>">
                                                <?php foreach ($v['children'] as $k2=>$v2): ?>
                                                    <li><a class="dropdown-item app-query app-query-catalog-<?= $v2['id']; ?>" href="#" data-method="catalog" data-param-id="<?= $v2['id']; ?>"><?= hst(html_entity_decode($v2['title'])); ?></a></li>
                                                <?php endforeach ?>
                                            </ul>
                                        </li>

                                    <?php else: ?>

                                        <li class="nav-item">
                                            <a class="nav-link app-query app-query-catalog-<?= $v['id']; ?>" aria-current="page" href="#" data-method="catalog" data-param-id="<?= $v['id']; ?>"><?= hst(html_entity_decode($v['title'])); ?></a>
                                        </li>

                                    <?php endif ?>

                                <?php endforeach ?>


                            </ul>

                        </div>
                    </div>
                </nav>
            <?php endif ?>
        <?php endif; ?>

        <div id="main" class="app-main-content">
            <?php if ($action == 'lastorder'): ?>
                <div class="alert alert-info"><?= __('Копирую предыдущий заказ в корзину...'); ?></div>
            <?php else: ?>
                <?= view('Services\Views\apps/shop-index'); ?>
            <?php endif; ?>
        </div>

        <div class="shop-footer-cart cart-isnotempty">
            <a href="#" class="btn btn-success app-shop-tocart checkout-btn-styles">
                <?php if (empty($config['products']['prices_hide'])): ?>

                    <?php if (!empty($config['products']['currency_left'])): ?><span class="price-label"> <?= hst($config['products']['currency']  ?? ''); ?> </span><?php endif ?>

                    <b class="price-value cart-price-badge"></b>

                    <?php if (empty($config['products']['currency_left'])): ?><span class="price-label"> <?= hst($config['products']['currency'] ?? ''); ?> </span><?php endif ?>

                    &mdash;

                <?php endif ?>

                <span class="shop-footer-cart-label"> <?= hst(!empty($config['main']['checkout_label']) ? $config['main']['checkout_label'] : __('Оформить заказ')); ?></span>
            </a>
        </div>

    </main>

    <div class="apps-copy">
        <div class="logo"><a href="https://slashstart.ru/?utm_source=<?= HOST_CURRENT; ?>&utm_campaign=widget&utm_content=shop" target="_blank"><img src="https://slashstart.ru/assets/images/logo-dark.png"></a></div>
        <div class="text">Сделано на платформе <a href="https://slashstart.ru/?utm_source=<?= HOST_CURRENT; ?>&utm_campaign=widget&utm_content=shop" target="_blank">Slashstart</a></div>
    </div>

    <script type="text/javascript">
        var SlstAppConfig = {};
        SlstAppConfig.cartCookieName = '_slst_shop_cart_<?= $currentShop['hash']; ?>';
        SlstAppConfig.cartDisable = <?= $config['cart']['disable'] ?? 0; ?>;
        SlstAppConfig.cartImCheckout = <?= $config['cart']['imcheckout'] ?? 0; ?>;

        SlstAppConfig.Currency = {
            decimals : <?= !empty($config['products']['currency_dec']) ? $config['products']['currency_dec'] : 0 ?>,
            dsep : '<?= str_replace(['p', 's', 'c'], ['.', ' ', ','], $config['products']['currency_dsep'] ?? ''); ?>',
            tsep : '<?= str_replace(['p', 's', 'c'], ['.', ' ', ','], $config['products']['currency_tsep'] ?? ''); ?>'
        };
    </script>
    <script src="<?= STATIC_SERVER_URL ?>assets/bs/js/bootstrap.bundle.min.js"></script>
    <script src="<?= STATIC_SERVER_URL ?>assets/js/jquery.min.js"></script>
    <?php //if (ENVIRONMENT == 'development'):  ?>
	    <!--script src="/platform/services/apps/01_apps.js?v<?= $version; ?>"></script>
	    <script src="/platform/services/apps/02_apps-shop.js?v<?= $version; ?>"></script-->
    <?php //else: ?>
    	<script src="<?= STATIC_SERVER_URL ?>assets/js/apps/apps.js?v<?= $version; ?>"></script>
        <script src="<?= STATIC_SERVER_URL ?>assets/js/apps/apps-shop.js?v<?= $version; ?>"></script>
    <?php //endif; ?>

    <?php if ($action == 'lastorder'): ?>

    	<script type="text/javascript">

            SlstApp.query('cart', {order: 'last'});	
    		
    	</script>

    <?php endif ?>

    <script>
        $(document).on('change', '#referal-use-trigger', function(){
            if ($(this).is(":checked")){
                hideReferalBonus(true);
            }else{
                hideReferalBonus();
            }
        });

        $(document).on('change', '#referal-use-total', function(){
            let price = parseFloat($('.cart-price-badge').html().replace(' ', ''));

            $('.referal-total .cart-price-badge').html(price - $(this).val());
        });

        function hideReferalBonus(rev = false){
            if (rev){
                $('.referal-total').show();
                $('.referal-total-lable').css('display', 'flex');
            }else{
                $('.referal-total').hide();
            }
        }
    </script>

  </body>
</html>