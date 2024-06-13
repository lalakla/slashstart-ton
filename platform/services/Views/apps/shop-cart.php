<?php
	$view = !empty($config['cart']['disable']) ? 'one' : 'many';
?>

<?php if (!empty($cartCookie)): ?>
	<script type="text/javascript">
		SlstApp.Cart.init('<?= $cartCookie ?? ''; ?>');
	</script>
<?php endif ?>


<?php if (!empty($config['cart']['order_table_title'])): ?>
	<h1><?= hst($config['cart']['order_table_title'] ?? ''); ?></h1>
<?php endif ?>

<div id="checkout-result"></div>

<?php if (empty($products)): ?>
	<div class="alert alert-info mt-4"><?= __('Нет продуктов для заказа.'); ?></div>
<?php else: ?>
	<div class="alert alert-info mt-4 cart-isempty"><?= __('Нет продуктов для заказа.'); ?></div>

	<div class="cart-isnotempty">
		
		<form action="" data-minorder="<?= intval($config['main']['minorder'] ?? 0); ?>" data-cblevels='<?= json_encode($config['cashback']['levels']??[]) ?>' class="app-query-form app-cart-form" data-method="checkout" data-callback="SlstAppShopOnOrder">

						<?php if (!empty($config['cart']['order_table_content'])): ?>
							<div class="cart-page-content text-color"><?= hst($config['cart']['order_table_content']); ?></div>
						<?php endif ?>




						<div class="cart-panel">
							<?php if ($view == 'many'): ?>


							<div class="settings_cart_mode_cart">

								<table class="table table-stripped cart-table">
									<?php foreach ($products as $product):

										$count = (int)$product['count'];
										$price = $product['variant_price'] ?? $product['price'];
										$id = !empty($product['variant_id']) ? 'v' . $product['variant_id'] : $product['id'];

									?>
										<tr class="cart-product">
											<td class="cart-photo">
												<div class="product-photo">
													<?php if (!empty($product['photo'])): ?>
														<div href="#<?= hst($product['id']); ?>" class="product-photo-image product-photo-image-small" style="background-image: url(<?= hst($product['photo']); ?>);"></div>
													<?php endif ?>
												</div>
											</td>
											<td class="cart-name">
												<div class="cart-product-title">
													<?= hst($product['title']); ?>
												</div>
												<?php if (!empty($product['variant_title'])): ?>
													<div class="small"><?= hst($product['variant_title']); ?></div>
												<?php endif ?>

												<div class="cart-count-change">
													<a class="btn btn-sm btn-warning cart-count-changer" data-action="m">&minus;</a>
													<span class="cart-count-product" data-count="<?= $count; ?>" data-id="<?= $id ?>" data-price="<?= $price; ?>">
                                                        <?php if (!empty($config['products']['shop_show_qty_inp'])): ?>
                                                                <input type="text" value="<?= hst($count); ?>" class="cart-pr-count">
                                                        <?php else: ?>
                                                                <?= hst($count); ?>
                                                        <?php endif ?>    
                                                    </span>
													<a class="btn btn-sm btn-success cart-count-changer" data-action="p">&plus;</a>
												</div>

											</td>
											<!-- <td class="cart-count">

											</td> -->
											<td class="cart-price">
												<?php if (!empty($config['products']['currency_left'])): ?>
												<span class="price-label"><?= hst($config['products']['currency']) ?? ''; ?></span>
												<?php endif ?>


												<span class="price-value cart-product-total-<?= !empty($product['variant_id']) ? 'v' . $product['variant_id'] : $product['id']; ?>"><?= pf($price * $count, $config['products']['currency_dec'] ?? 0, $config['products']['currency_dsep'] ?? null, $config['products']['currency_tsep'] ?? null); ?></span>

												<?php if (empty($config['products']['currency_left'])): ?>
												<span class="price-label"><?= hst(($config['products']['currency'] ?? '')) ?? ''; ?></span>
												<?php endif ?>



											</td>
											<td class="cart-delete">
												<a class="btn btn-sm btn-danger cart-product-delete" data-id="<?= $id; ?>" data-confirm='<?= sprintf(__('Удалить %s?'), addslashes(hst($product['title']))); ?>'>&times;</a>
											</td>
										</tr>
									<?php endforeach ?>

									<tr>
										<td colspan="2">
											<div class="row">
												<div class="col">
													<?php if (!empty($balance && empty($config['main']['cashback_disabled']))): ?>

														<div class="cart-cashback">
															<label>
																<input type="checkbox" id="cart-use-cashback-trigger" name="data[use_cashback]" value="1">
																<?= sprintf(__('Кэшбэк (до <span class="cart-cashback-max-badge"></span> из %s)'), $balance); ?>
															</label><span id="cart-use-cashback-value-wrr"> <input type="number" name="data[cashback_used]" id="cart-use-cashback-value" data-max="<?= $config['main']['cashback_max']; ?>" data-balance="<?= $balance; ?>" style=""></span>
														</div>
														
													<?php endif ?>
												</div>
												<div class="col text-end">
													<?= __('Итого:'); ?>
												</div>
											</div>
										</td>
										<td class="text-end">
											<?php if (!empty($config['products']['currency_left'])): ?>
											<span class="price-label"><?= hst($config['products']['currency']  ?? ''); ?></span>
											<?php endif ?>

											<b class="price-value cart-price-badge"></b>

											<?php if (empty($config['products']['currency_left'])): ?>
											<span class="price-label"><?= hst($config['products']['currency'] ?? ''); ?></span>
											<?php endif ?>
										</td>
									</tr>

                                    <?php if (empty($config['main']['cashback_disabled']) && !empty($config['cashback']['levels'][0]['price'])): ?>
										<tr>
											<td class="text-end" colspan="3">
												<span class="help-label p-1 alert-warning cblevels-alert" data-label="<?= __('Закажите еще на <b>{s}</b>, чтобы получить дополнительно <b>{b}</b> кэшбэка.'); ?>"></span>
											</td>
										</tr>
									<?php endif ?>

                                    <tr>
										<td colspan="2">
											<div class="row">
												<div class="col">
                                                    <?php if ($paymentWithReferal): ?>
                                                        <input type="checkbox" id="referal-use-trigger" name="data[use_referal]" value="1" data-balance="<?=$max_active_points?>" data-max="<?= $config['main']['cashback_max']; ?>"> оплатить баллами
                                                        <div class="cart-referal">
                                                            <label>
                                                                (<?php if ($max_active_points<=0){ ?>
                                                                    <?= __('у Вас еще нет баллов'); ?>
                                                                <?php } else { ?>
                                                                    <?= __('можно оплатить до <span class="able-points">'.$max_able_points.'</span> <br>балланс баллов '.$max_active_points, ); ?>
                                                                <?php } ?>)
                                                            </label>

                                                            <div class="referal-total" style="display:none">
                                                                списать <input type="number" step="0.1" id="referal-use-total" name="data[use_referal_total]" value="0" max="<?=$max_able_points?>" />
                                                            </div>
                                                        </div>

                                                    <?php endif ?>
												</div>
												<div class="col text-end referal-total referal-total-lable" style="display:none;align-items: end;vertical-align: bottom;">
													<?= __('К оплате:'); ?>
												</div>
											</div>
										</td>
										<td class="text-end referal-total" style="display:none;vertical-align: bottom">
											<?php if (!empty($config['products']['currency_left'])): ?>
											<span class="price-label"><?= hst($config['products']['currency']  ?? ''); ?></span>
											<?php endif ?>

											<b class="price-value cart-price-badge"></b>

											<?php if (empty($config['products']['currency_left'])): ?>
											<span class="price-label"><?= hst($config['products']['currency'] ?? ''); ?></span>
											<?php endif ?>
										</td>
									</tr>

								</table>
							</div>

							<?php endif ?>

							<?php if ($view == 'one'):

								$product = array_shift($products);

							?>
							<div class="settings_cart_mode_one">

								<table class="table table-stripped cart-table">
									<tr class="cart-product">
										<td class="cart-photo">
											<div class="product-photo">
												<?php if (!empty($product['photo'])): ?>
													<div href="#<?= hst($product['id']); ?>" class="product-photo-image product-photo-image-small" style="background-image: url(<?= hst($product['photo']); ?>);"></div>
												<?php endif ?>
											</div>
										</td>
										<td class="cart-name">
											<div class="cart-product-title">
												<?= hst($product['title']); ?>
											</div>
											<?php if (!empty($product['variant_title'])): ?>
												<div class="small"><?= hst($product['variant_title']); ?></div>
											<?php endif ?>
										</td>
										<td class="cart-price">

											<?php if (!empty($config['products']['currency_left'])): ?>
											<span class="price-label"><?= hst($config['products']['currency']) ?? ''; ?></span>
											<?php endif ?>


											<span class="price-value cart-product-total-<?= !empty($product['variant_id']) ? 'v' . $product['variant_id'] : $product['id']; ?>"><?= pf($product['variant_price'] ?? $product['price'], $config['products']['currency_dec'] ?? 0, $config['products']['currency_dsep'] ?? null, $config['products']['currency_tsep'] ?? null); ?></span>

											<?php if (empty($config['products']['currency_left'])): ?>
											<span class="price-label"><?= hst($config['products']['currency']) ?? ''; ?></span>
											<?php endif ?>

										</td>
									</tr>
								</table>
							</div>
							<?php endif ?>



						</div>


						<?php if (!empty($config['cart']['order_form_title'])): ?>
						<div><b class="cart-order-title text-color"><?= $config['cart']['order_form_title']; ?></b></div>
						<?php endif; ?>

						<div class="cart-order-min-notice help-label alert alert-info">
							<?= sprintf(__('Минимальная сумма заказа - %s руб.'), intval($config['main']['minorder'] ?? 0)); ?>
						</div>

						<div class="cart-panel cart-order">

								<?php if (!empty($config['cart']['require_name'])): ?>
								<div class="form-group cart-form-name">
							        <label><?= __('Ваше имя:'); ?></label>
							        <input type="text" value="" name="data[name]" required="required" class="form-control">
							    </div>
							    <?php endif; ?>

							    <?php if (!empty($config['cart']['require_phone'])): ?>
							    <div class="form-group cart-form-phone">
							        <label><?= __('Телефон:'); ?></label>
							        <input type="text" value="" name="data[phone]" required="required"  class="form-control">
							    </div>
							    <?php endif; ?>

							    <?php if (!empty($config['cart']['require_email'])): ?>
							    <div class="form-group cart-form-email">
							        <label><?= __('E-mail:'); ?></label>
							        <input type="email" value="" name="data[email]" required="required"  class="form-control">
							    </div>
							    <?php endif; ?>


							    <?php if (!empty($config['cart']['require_address'])): ?>
							    <div class="form-group cart-form-email">
							        <label><?= __('Адрес доставки:'); ?></label>
							        <input type="text" value="" name="data[address]" required="required"  class="form-control">
							    </div>
							    <?php endif; ?>


							    



							    <?php if (empty($config['cart']['disable_comment'])): ?>
								    <div class="form-group cart-form-comment">
								        <label class="cart-form-comment-label"><?= !empty($config['cart']['order_comment_title']) ? $config['cart']['order_comment_title'] : __('Комментарий к заказу:'); ?></label>
								        <textarea name="data[comments]" value="" class="form-control mb-0"></textarea>
								        <p class="mt-1 help-label cart-form-comment-help"><?= !empty($config['cart']['order_comment_help']) ? $config['cart']['order_comment_help'] : __('Дополнительные пожелания к заказу'); ?></p>
								    </div>
							    <?php endif; ?>



							    <div class="cart-submit">
							    	<button type="submit" class="btn btn-success cart-submit-label w-100 checkout-btn-styles"><?= !empty($config['cart']['order_btn']) ? $config['cart']['order_btn'] : __('Заказать'); ?></button>
							    </div>

							

							<div class="form-policy">Отправляя форму, вы соглашаетесь с <a href="https://slashstart.ru/privacy?utm_source=<?= HOST_CURRENT; ?>&utm_campaign=widget&utm_content=shop" target="_blank">политикой конфиденциальности</a></div>

						</div>


		</form>


	</div>


<?php endif ?>