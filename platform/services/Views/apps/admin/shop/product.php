<?= $this->extend('app') ?>
<?= $this->section('main') ?>


<div class="row">

	<div class="col-md-7">

		<div class="settings-form">

	    	<form class="ajax-form" action="/apps/shop/product?shop_id=<?= $currentShop['hash']; ?>">

                <div class="form-group edit-field-onedit edit-field-oncreate">
                    <label class="col-form-label"><?= __('Название:'); ?></label>
                    <input type="text" class="form-control edit-field-title" name="data[title]" value="<?= hst($product['title'] ?? ''); ?>" required="required">
                </div>

                <div class="form-group edit-field-onedit edit-field-oncreate">
                    <label class="col-form-label"><?= __('Категории:'); ?></label>
                    <?= $tree; ?>
                    <p class="help-label"><?= __('Можно выбрать несколько, зажав клавишу Ctrl. Если указать дочернюю категорию, то в родительской товар не отобразится по умолчанию. Поэтому нужно указать все необходимые категории (дочерние и родительские).'); ?></p>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group edit-field-onedit edit-field-oncreate">
                            <label class="col-form-label"><?= __('Цена:'); ?></label>
                            <input type="text" class="form-control edit-field-title" name="data[price]" value="<?= hst($product['price'] ?? ''); ?>">
                            <p class="help-label"><?= __('Без пробелов.'); ?></p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group edit-field-onedit edit-field-oncreate">
                            <label class="col-form-label"><s><?= __('Старая цена:'); ?></s></label>
                            <input type="text" class="form-control edit-field-title" name="data[price_old]" value="<?= hst($product['price_old'] ?? ''); ?>">
                            <p class="help-label"><?= __('Дроби через точку.'); ?></p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group edit-field-onedit edit-field-oncreate">
                            <label class="col-form-label"><?= __('Артикул:'); ?></label>
                            <input type="text" class="form-control edit-field-title" name="data[sku]" value="<?= hst($product['sku'] ?? ''); ?>">
                            <p class="help-label"><?= __('Если пусто, сгенерируется автоматически.'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="form-group edit-field-onedit edit-field-oncreate">
                    <label class="col-form-label"><?= __('Фото:'); ?></label>
                    <div class="input-group">
                        <input type="text" class="form-control edit-field-title" name="data[photo]" id="product-photo" value="<?= hst($product['photo'] ?? ''); ?>">
                        <div class="input-group-append">
                            <div class="wf-input-tb" style="top: 5px;"><div class="wf-input-tb-action wf-input-tb-upload" data-target="#product-photo"><i class="fa fa-picture-o"></i></div></div>
                        </div>
                    </div>
                </div>

                <div class="clonable mb-3 block-silver">
                    <div class="clonable-container">

                        <div class="row clonable-item">
                            <div class="form-group col-md-9">
                                <label class="clonable-removeafterclone"><?= __('Галерея'); ?></label>
                                <div class="input-group">
                                    <input type="text" class="form-control clonable-clearafterclone clonable-uploadid" name="data[gallery][]" id="product-gallery-0" value="<?= hst($product['gallery'][0] ?? ''); ?>">
                                    <div class="input-group-append">
                                        <div class="wf-input-tb" style="top: 5px;"><div class="wf-input-tb-action wf-input-tb-upload" data-target="#product-gallery-0"><i class="fa fa-picture-o"></i></div></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-1">
                                <label class="d-block clonable-removeafterclone">&nbsp;</label>
                                <button type="button" class="btn btn-delete clonable-clear clonable-removeafterclone"><i class="fa fa-refresh"></i></button>
                                <button type="button" class="btn btn-delete clonable-delete clonable-showafterclone"><i class="fa fa-trash"></i></button>
                            </div>
                        </div>
                        <?php $l = sizeof($product['gallery'] ?? []); ?>
                        <?php if ($l > 1): for ($i = 1; $i < $l; $i++): ?>
                            <div class="row clonable-item">
                                <div class="form-group col-md-9">
                                    <div class="input-group">
                                        <input type="text" class="form-control clonable-clearafterclone clonable-uploadid" name="data[gallery][]" id="product-gallery-<?= $i; ?>" value="<?= hst($product['gallery'][$i] ?? ''); ?>">
                                        <div class="input-group-append">
                                            <div class="wf-input-tb" style="top: 5px;"><div class="wf-input-tb-action wf-input-tb-upload" data-target="#product-gallery-<?= $i; ?>"><i class="fa fa-picture-o"></i></div></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-1">
                                    <button type="button" class="btn btn-delete clonable-delete"><i class="fa fa-trash"></i></button>
                                </div>
                            </div>
                        <?php endfor; endif; ?>

                    </div>
                    
                    <button type="button" class="users-groups-add-btn btn btn-xs clonable-clone clonable-removeafterclone"><?= __('Добавить фото'); ?></button>

                </div>

                <div class="form-group edit-field-onedit edit-field-oncreate">
                    <label class="col-form-label"><?= __('Краткое описание:'); ?></label>
                    <textarea class="form-control edit-field-content" name="data[summary]"><?= hst($product['summary'] ?? ''); ?></textarea>
                    <p class="help-label"><?= __('Для списка товаров при построчном отображении.'); ?></p>
                </div>

                <div class="form-group edit-field-onedit edit-field-oncreate">
                    <label class="col-form-label"><?= __('Содержание:'); ?></label>
                    <textarea class="form-control edit-field-content wysiwyg" name="data[content]"><?= ($product['content'] ?? ''); ?></textarea>
                    <p class="help-label"><?= __('Для страницы товара.'); ?></p>
                </div>

                <div class="form-group edit-field-onedit edit-field-oncreate">
                    <label class="col-form-label"><?= __('Ключевые слова для поиска:'); ?></label>
                    <textarea class="form-control edit-field-content" name="data[search]" style="height: 60px; min-height: 60px;"><?= hst($product['search'] ?? ''); ?></textarea>
                </div>

                <div class="row mb-4">
                    <div class="col-8">
                        <div class="form-group edit-field-onedit edit-field-oncreate">
                            <label class="col-form-label"><?= __('Ссылка:'); ?></label>
                            <input type="text" class="form-control edit-field-title" name="data[url]" value="<?= hst($product['url'] ?? ''); ?>">
                            <p class="help-label"><?= __('Для перехода на внешний сайт вместо корзины. Доступны шорткоды.'); ?></p>
                        </div>
                    </div>
                    <div class="col-4">
                        <input name="data[url_blank]" type="hidden" value="0">
                        <div class="custom-control custom-switch custom-control-inline custom-control-sm mr-0 mt-3 cursor-pointer">
                            <input name="data[url_blank]" type="checkbox" value="1" id="product-url_blank" class="custom-control-input" <?php if (!empty($product['url_blank'])): ?>checked="checked"<?php endif ?>>
                            <label class="custom-control-label cursor-pointer" for="product-url_blank"><?= __('В новом окне'); ?></label>
                        </div>
                        <p class="help-label"><?= __('В Телеграме всегда открывается в новом окне.'); ?></p>
                    </div>
                </div>

                <div class="form-group edit-field-onedit edit-field-oncreate">
                    <input name="data[active]" type="hidden" value="0">
                    <div class="custom-control custom-switch custom-control-inline custom-control-sm mr-0 cursor-pointer">
                        <input name="data[active]" type="checkbox" value="1" id="product-active" class="custom-control-input" <?php if (!empty($product['active']) || empty($product['id'])): ?>checked="checked"<?php endif ?>>
                        <label class="custom-control-label cursor-pointer" for="product-active"><?= __('Активен (публиковать в каталоге)'); ?></label>
                    </div>
                </div>

                <div class="form-group edit-field-onedit edit-field-oncreate">
                    <input name="data[is_main]" type="hidden" value="0">
                    <div class="custom-control custom-switch custom-control-inline custom-control-sm mr-0 cursor-pointer">
                        <input name="data[is_main]" type="checkbox" value="1" id="product-is_main" class="custom-control-input" <?php if (!empty($product['is_main'])): ?>checked="checked"<?php endif ?>>
                        <label class="custom-control-label cursor-pointer" for="product-is_main"><?= __('Разместить на главной странице'); ?></label>
                    </div>
                </div>

                <?php if (empty($variantes)):

                    $variantes = [[]];

                endif ?>

                <div class="table-items shadows borders br-xs">
                    <table class="table table-hover table-sm shop-variantes">
                        <tr>
                            <td width="30"></td>
                            <td width="50"><?= __('№'); ?></td>
                            <td><?= __('Модификация'); ?></td>
                            <td width="100"><?= __('Цена'); ?></td>
                            <td width="100"><s><?= __('Цена'); ?></s></td>
                            <td width="100"><?= __('Артикул'); ?></td>
                            <td width="30"><button type="button" class="btn btn-sm btn-success shop-add-variant"><i class="la la-plus"></i></button></td>
                        </tr>

                        <tbody>
                            <?php foreach ($variantes as $n=>$variant): ?>
                            <tr class="table-item-<?php if ($n > 0): ?><?= $variant['id'] ?? ''; ?><?php endif ?> <?php if ($n == 0): ?>clonable<?php endif ?>">
                                <td>
                                    <div class="form-group m-0">
                                        <input name="data[variantes][<?= $n; ?>][id]" type="hidden" value="<?= $variant['id'] ?? ''; ?>">
                                        <input name="data[variantes][<?= $n; ?>][active]" type="hidden" value="0">
                                        <div class="custom-control custom-switch custom-control-inline custom-control-sm mr-0 cursor-pointer">
                                            <input name="data[variantes][<?= $n; ?>][active]" type="checkbox" value="1" id="product-variant-<?= $n; ?>-active" class="custom-control-input" <?php if (!empty($variant['active']) || empty($variant['id'])): ?>checked="checked"<?php endif ?>>
                                            <label class="custom-control-label cursor-pointer" for="product-variant-<?= $n; ?>-active"></label>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group m-0">
                                        <input type="text" class="form-control" name="data[variantes][<?= $n; ?>][order]" value="<?= hst($variant['order'] ?? ''); ?>">
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group m-0">
                                        <input type="text" class="form-control" name="data[variantes][<?= $n; ?>][title]" value="<?= hst($variant['title'] ?? ''); ?>">
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group m-0">
                                        <input type="text" class="form-control" name="data[variantes][<?= $n; ?>][price]" value="<?= hst($variant['price'] ?? ''); ?>">
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group m-0">
                                        <input type="text" class="form-control" name="data[variantes][<?= $n; ?>][price_old]" value="<?= hst($variant['price_old'] ?? ''); ?>">
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group m-0">
                                        <input type="text" class="form-control" name="data[variantes][<?= $n; ?>][sku]" value="<?= hst($variant['sku'] ?? ''); ?>">
                                    </div>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger shop-delete-variant edit-item-delete"  data-id="<?= $variant['id'] ?? '-'; ?>" data-url="/apps/shop/product?delete_variant=<?= $variant['id'] ?? '-'; ?>" data-confirm='<?= sprintf(__('Удалить вариант %s?'), $variant['title'] ?? ''); ?>'><i class="la la-times"></i></button>
                                </td>
                            </tr>
                            <?php endforeach ?>

                        </tbody>

                    </table>
                </div>

                <input type="hidden" class="form-control edit-field-id" name="data[id]" value="<?= hst($product['id'] ?? ''); ?>">

                <!-- Если магазин 2.0 -->
                <?php if (isset($config['main']['is_shop2']) && $config['main']['is_shop2']): ?>
                    <div class="form-group edit-field-onedit edit-field-oncreate">
                        <label class="col-form-label"><?= __('Отзывы:'); ?></label>
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                            <div class="input-group mb-1">
                                <input type="text" class="form-control edit-field-title" name="data[reviews][]" id="product-review<?=$i?>" value="<?= hst($product['reviews'][$i] ?? ''); ?>">
                                <div class="input-group-append">
                                    <div class="wf-input-tb" style="top: 5px;"><div class="wf-input-tb-action wf-input-tb-upload" data-target="#product-review<?=$i?>"><i class="fa fa-picture-o"></i></div></div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

                <div class="mt-4">
                    <button class="btn btn-primary"><?= __('Сохранить'); ?></button>
                    <?php if (!empty($product['id'])): ?>
                        <a href="/apps/shop/product/<?= $product['id']; ?>?copy=1&shop_id=<?= $currentShop['hash']; ?>" class="btn btn-lighter"><?= __('Копировать'); ?></a>
                    <?php endif ?>
                    <div class="pull-right">
                        <a href="/apps/shop?shop_id=<?= $currentShop['hash']; ?>" class="btn btn-light"><?= __('Выйти'); ?></a>
                    </div>
                </div>

	        </form>
		</div>
	</div>
</div>




<?= $this->endSection() ?>