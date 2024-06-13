<?= $this->extend('app') ?>
<?= $this->section('main') ?>


<div class="row">

	<div class="col-md-12">


		<?php
			// $pagerDetails = $pager->getDetails();
		?>

		<?php if ($categories): ?>



		<form action="/apps/shop/categories?shop_id=<?= $currentShop['hash']; ?>" method="post">
		<div class="table-items shadows borders br-xs shop-list">
			<table class="table">
	            <thead>
	              <tr>
	              	<th width="5" class="p-0"></th>
	              	<th width="30"><?= __('ID'); ?></th>
	              	<th width="70"><?= __('Фото'); ?></th>
	              	<th><?= __('Название'); ?></th>
	              	<th width="50"><?= __('Приоритет'); ?></th>
	                <th width="50"><?= __('Действия'); ?></th>
	              </tr>
	            </thead>
	            <tbody>


					<?php foreach ($categories ?? [] as $item): ?>
						<tr class="table-item-<?= hst($item['id']); ?> <?php if (!$item['active']): ?>opacity-5<?php endif ?>">
							<td class="p-0 <?php if ($item['active']): ?>bg-success<?php else: ?>bg-secondary<?php endif ?>"></td>
							<td>
			                	<?= hst($item['id']); ?>
			                </td>
			                <td>
			                	<?php if (!empty($item['photo'])): ?>
			                		<a href="/apps/shop/category/<?= hst($item['id']); ?>?shop_id=<?= $currentShop['hash']; ?>"><div class="chat-avatar" style="background-image: url(<?= $item['photo']; ?>);"></div></a>
			                	<?php endif ?>
			                </td>
			                <td>
			                	<a href="/apps/shop/category/<?= hst($item['id']); ?>?shop_id=<?= $currentShop['hash']; ?>"><?= hst($item['title']); ?></a>
			                </td>
			                <td>
			                	<input type="text" class="form-control" name="data[priority][<?= hst($item['id']); ?>]" value="<?= hst($item['priority'] ?? ''); ?>"></td>
			                </td>
			                <td>
			                	<a href="/apps/shop/category/<?= hst($item['id']); ?>?shop_id=<?= $currentShop['hash']; ?>" class="btn btn-sm btn-lighter"><i class="fa fa-pencil"></i></a>
			                	<a href="#delete" data-id="<?= hst($item['id']); ?>" data-url="/apps/shop/category?delete=<?= hst($item['id']); ?>" data-confirm='<?= sprintf(__('Вы действительно хотите удалить "%s"?'), hst($item['title'])); ?>' class="edit-item-delete btn btn-sm btn-lighter"><i class="fa fa-trash"></i></a>

			                </td>
			              </tr>

					<?php endforeach; ?>
						<tr>
							<td class="p-0"></td>
							<td colspan="3"></td>
							<td><button type="submit" name="data[action]" value="priority" class="btn btn-sm btn-light"><?= __('Сохранить'); ?></button></td>
							<td colspan="2"></td>
						</tr>



				</tbody>
	          </table>
	      </div>
	    </form>

	    <?php else: ?>
	    	<div class="alert alert-info">
	    		<?= __('Еще нет категорий. Создайте первую справа вверху.'); ?>
	    	</div>

	    <?php endif ?>

	</div>
</div>






<?= $this->endSection() ?>