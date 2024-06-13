<?= $this->extend('app') ?>
<?= $this->section('main') ?>



<div class="row">

	<div class="col-md-12">


		<?php
			$pagerDetails = $pager->getDetails();
		?>

		<?php if ($orders): ?>

			<div class="table-items-filter">
				<div class="row">
					<div class="col-md-6">

					</div>

					<div class="col-md-6">

						<div class="filter-pager pull-right">
							<?php if ($pagerDetails['pageCount'] > 1): ?>
								<?= $pager->links(); ?>
							<? endif; ?>
						</div>
					</div>
				</div>
			</div>


		<form action="/apps/shop/orders?shop_id=<?= $currentShop['hash']; ?>" method="post">
		<div class="table-items shadows borders br-xs shop-list">
			<table class="table">
	            <thead>
	              <tr>
	              	<th width="5" class="p-0"></th>
	              	<th width="30"><?= __('ID'); ?></th>
	              	<th><?= __('Заказ'); ?></th>
	              	<th width="100"><?= __('Клиент'); ?></th>
	              	<th width="100"><?= __('Адрес доставки'); ?></th>
	              	<th width="100"><?= __('Комментарий'); ?></th>
	                <th width="50"><?= __('Сумма'); ?></th>
	                <th width="140" class="text-end"><?= __('Действия'); ?></th>
	              </tr>
	            </thead>
	            <tbody>


					<?php foreach ($orders ?? [] as $item):

						$item['products'] = json_decode( $item['products'], 1 );

						// p($item);

					?>
						<tr class="table-item-<?= hst($item['id']); ?> <?php if (!$item['active']): ?>opacity-5<?php endif ?>">
							<td class="p-0 <?php if ($item['active']): ?>bg-success<?php else: ?>bg-secondary<?php endif ?>"></td>
							<td>
			                	<?= hst($item['id']); ?>
			                </td>
			                <td>
                                <table>
                                    <thead>
                                        <tr> 
                                            <th><?=__('Наименование')?></th>
                                            <th><?=__('Артикул')?></th>
                                            <th><?=__('Цена')?></th>
                                            <th><?=__('Количество')?></th>
                                            <th><?=__('Сумма')?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($item['products']['products'] as $n=>$pr): ?>
                                            <tr>
                                                <td><?=$pr['title']?></td>
                                                <td><?=$pr['sku']?></td>
                                                <td><?=$pr['price']?></td>
                                                <td><?=$pr['count']?></td>
                                                <td><?=$pr['total']?></td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
			                </td>
			                <td>
			                	<?= hst($item['user_name']); ?>
			                	<?= hst($item['user_phone']); ?>
			                	<?= hst($item['user_email']); ?>
			                </td>
			                <td>
			                	<?= hst($item['user_address']); ?>
			                </td>
			                <td>
			                	<?= hst($item['user_comments']); ?>
			                </td>
			                <td>
			                	<?= hst($item['total']); ?>
			                </td>
			                <td class="text-end">
			                	
			                	<?php if ($item['active']): ?>
			                		<a href="/apps/shop/order/<?= hst($item['id']); ?>?active=0&shop_id=<?= $currentShop['hash']; ?>" class="btn btn-sm btn-lighter"><i class=" ni ni-toggle-on"></i></a>
			                	<?php else: ?>
			                		<a href="/apps/shop/order/<?= hst($item['id']); ?>?active=1&shop_id=<?= $currentShop['hash']; ?>" class="btn btn-sm btn-lighter"><i class="ni ni-toggle-off"></i></a>
			                	<?php endif ?>
			                	
			                	<a href="#delete" data-id="<?= hst($item['id']); ?>" data-url="/apps/shop/order?delete=<?= hst($item['id']); ?>" data-confirm='<?= sprintf(__('Вы действительно хотите удалить заказ №%s?'), hst($item['id'])); ?>' class="edit-item-delete btn btn-sm btn-lighter"><i class="fa fa-trash"></i></a>

			                </td>
			              </tr>

					<?php endforeach; ?>
						

				</tbody>
	          </table>
	      </div>
	    </form>


	    	<div class="table-items-filter mt-2">
				<div class="row">
					<div class="col-md-6">

					</div>

					<div class="col-md-6">

						<div class="filter-pager pull-right">
							<?php if ($pagerDetails['pageCount'] > 1): ?>
								<?= $pager->links(); ?>
							<? endif; ?>
						</div>
					</div>
				</div>
			</div>

	    <?php else: ?>
	    	<div class="alert alert-info"><?= __('Заказов еще нет в этом магазине.'); ?></div>

	    <?php endif ?>

	</div>
</div>






<?= $this->endSection() ?>