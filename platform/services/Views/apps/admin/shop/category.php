<?= $this->extend('app') ?>
<?= $this->section('main') ?>




<div class="row">

	<div class="col-md-6">

		<div class="settings-form">

	    	<form class="ajax-form" action="/apps/shop/category?shop_id=<?= $currentShop['hash']; ?>">



		            	<div class="form-group edit-field-onedit edit-field-oncreate">
				            <label class="col-form-label"><?= __('Название:'); ?></label>
				            <input type="text" class="form-control edit-field-title" name="data[title]" value="<?= ($category['title'] ?? ''); ?>" required="required">
				        </div>

				        <?php
				        	$cid = $category['id'] ?? 0;
				        	$pid = $category['parent_id'] ?? 0;

				        ?>

				        <div class="form-group edit-field-onedit edit-field-oncreate">
				            <label class="col-form-label"><?= __('Родительская категория:'); ?></label>
				            <select class="select2 form-control edit-field-title" name="data[parent_id]">
				            	<option value="0"><?= __('Нет'); ?></option>
				            	<?php foreach ($categories as $c): ?>
				            		<?php if ($c['id'] !== $cid): ?>
					            		<option value="<?= hst($c['id']); ?>" <?php if ($c['id'] == $pid): ?>selected="selected"<?php endif; ?>><?= hst($c['title']); ?></option>
					            		<?php if ($c['children']): ?>
					            			<?php foreach ($c['children'] as $c2): ?>
					            				<?php if ($c2['id'] !== $cid): ?>
								            		<option value="<?= hst($c2['id']); ?>" <?php if ($c2['id'] == $pid): ?>selected="selected"<?php endif; ?>>- - <?= hst($c2['title']); ?></option>
								            		<?php if ($c2['children']): ?>
								            			<?php foreach ($c2['children'] as $c3): ?>
								            				<?php if ($c3['id'] !== $cid): ?>
										            			<option value="<?= hst($c3['id']); ?>" <?php if ($c3['id'] == $pid): ?>selected="selected"<?php endif; ?>>- - - - <?= hst($c3['title']); ?></option>
										            		<?php endif ?>
										            	<?php endforeach ?>
								            		<?php endif ?>
							            		<?php endif ?>
							            	<?php endforeach ?>
					            		<?php endif ?>
				            		<?php endif ?>
				            	<?php endforeach ?>
				            </select>
				        </div>


				        <div class="form-group edit-field-onedit edit-field-oncreate">
				            <label class="col-form-label"><?= __('Фото:'); ?></label>
				            <div class="input-group">
					            <input type="text" class="form-control edit-field-title" name="data[photo]" id="product-photo" value="<?= hst($category['photo'] ?? ''); ?>">
					            <div class="input-group-append">
							    	<div class="wf-input-tb"><div class="wf-input-tb-action wf-input-tb-upload" data-target="#product-photo"><i class="fa fa-picture-o"></i></div></div>
							    </div>
					        </div>
				        </div>

				        <div class="form-group edit-field-onedit edit-field-oncreate">
				            <label class="col-form-label"><?= __('Описание:'); ?></label>
				            <textarea class="form-control edit-field-summary wysiwyg" name="data[content]"><?= hst($category['content'] ?? ''); ?></textarea>
				        </div>

				        <div class="form-group edit-field-onedit edit-field-oncreate">
				        	<input name="data[active]" type="hidden" value="0">
				        	<div class="custom-control custom-switch custom-control-inline mr-0 cursor-pointer">
								<input name="data[active]" type="checkbox" value="1" id="product-active" class="custom-control-input" <?php if (!empty($category['active']) || empty($category['id'])): ?>checked="checked"<?php endif ?>>
						    	<label class="custom-control-label cursor-pointer" for="product-active"><?= __('Активна (публиковать в каталоге)'); ?></label>
						    </div>
						</div>


						<div class="form-group">
					        <label><?= __('Кэшбэк для всех товаров, в %'); ?></label>
					        <div class="input-group">
								<input type="text" name="data[cashback]" value="<?= hst($category['cashback'] ?? ''); ?>" placeholder="0" class="form-control">
							</div>
					    </div>


				        <input type="hidden" class="form-control edit-field-id" name="data[id]" value="<?= hst($category['id'] ?? ''); ?>">



		                <div>
		                    <button class="btn btn-primary"><?= __('Сохранить'); ?></button>
		                    <div class="pull-right"><a href="/apps/shop/categories?shop_id=<?= $currentShop['hash']; ?>" class="btn btn-light"><?= __('Выйти'); ?></a></div>
		                </div>

	        </form>
		</div>
	</div>
</div>




<?= $this->endSection() ?>