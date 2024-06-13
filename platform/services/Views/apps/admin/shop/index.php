<?= $this->extend('app') ?>
<?= $this->section('main') ?>


    <div class="row">

        <div class="col-md-12">


            <?php
            $pagerDetails = $pager->getDetails();
            ?>

            <?php if ($products): ?>

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


                <form action="/apps/shop?shop_id=<?= $currentShop['hash']; ?>" method="post">
                    <div class="table-items shadows borders br-xs shop-list">
                        <table class="table" id="shop_table">
                            <thead>
                            <tr>
                                <th width="5" class="p-0"></th>
                                <th width="30"><?= __('ID'); ?></th>
                                <th width="70"><?= __('Фото'); ?></th>
                                <th><?= __('Название'); ?></th>
                                <th width="100"><?= __('Артикул'); ?></th>
                                <th width="100"><?= __('Цена'); ?></th>
                                <th width="50"><?= __('Приоритет'); ?></th>
                                <th width="170"><?= __('Действия'); ?></th>
                            </tr>
                            </thead>
                            <tbody>


                            <?php foreach ($products ?? [] as $item): ?>
                                <tr class="table-item-<?= hst($item['id']); ?> <?php if (!$item['active']): ?>opacity-5<?php endif ?>">
                                    <td class="p-0 <?php if ($item['active']): ?>bg-success<?php else: ?>bg-secondary<?php endif ?>"></td>
                                    <td>
                                        <?= hst($item['id']); ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($item['photo'])): ?>
                                            <a href="/apps/shop/product/<?= hst($item['id']); ?>?shop_id=<?= $currentShop['hash']; ?>">
                                                <div class="chat-avatar"
                                                     style="background-image: url(<?= $item['photo']; ?>);"></div>
                                            </a>
                                        <?php endif ?>
                                    </td>
                                    <td>
                                        <a href="/apps/shop/product/<?= hst($item['id']); ?>?shop_id=<?= $currentShop['hash']; ?>"><?= hst($item['title']); ?></a>
                                    </td>
                                    <td>
                                        <a href="/apps/shop/product/<?= hst($item['id']); ?>?shop_id=<?= $currentShop['hash']; ?>"><?= hst($item['sku']); ?></a>
                                    </td>
                                    <td>
                                        <?= hst($item['price']); ?>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control"
                                               name="data[priority][<?= hst($item['id']); ?>]"
                                               value="<?= hst($item['priority'] ?? ''); ?>"></td>
                                    </td>
                                    <td>
                                        <a href="/apps/shop/product/<?= hst($item['id']); ?>?shop_id=<?= $currentShop['hash']; ?>"
                                           class="btn btn-sm btn-lighter"><i class="fa fa-pencil"></i></a>
                                        <!-- <a href="/apps/shop/product/<?= hst($item['id']); ?>?copy=1&shop_id=<?= $currentShop['hash']; ?>" class="btn btn-sm btn-lighter"><i class="fa fa-copy"></i></a> -->
                                        <a href="#delete" data-id="<?= hst($item['id']); ?>"
                                           data-url="/apps/shop/product?delete=<?= hst($item['id']); ?>"
                                           data-confirm='<?= sprintf(__('Вы действительно хотите удалить "%s"?'), hst($item['title'])); ?>'
                                           class="edit-item-delete btn btn-sm btn-lighter"><i
                                                    class="fa fa-trash"></i></a>

                                    </td>
                                </tr>

                            <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="8" class="text-end">
                                    <button type="submit" name="data[action]" value="priority"
                                            class="btn btn-sm btn-light"><?= __('Сохранить'); ?></button>
                                </td>
                            </tr>
                            </tfoot>
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
                <div class="alert alert-info"><?= __('Товаров еще нет. Создайте их справа вверху.'); ?></div>

            <?php endif ?>

        </div>
    </div>


    <div class="modal fade modal-tabs-menu" class="wf-settings-form wf-settings-form-modal" id="shop-import-pp">
        <div class="modal-dialog" role="document">

            <div class="modal-content">
                <div class="modal-header">
                    <ul class="nav nav-tabs hmx-0 nav-tabs-modal" id="v-pills-tab" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab"
                                                href="#si-yml"><?= __('apps.shop_import_yml_tab', 'YML'); ?></a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab"
                                                href="#si-wb"><?= __('apps.shop_import_wb_tab', 'WB'); ?></a></li>
                    </ul>

                    <div class="close" data-bs-dismiss="modal" aria-label="<?= __('app.close_btn', 'Close'); ?>"><em
                                class="icon ni ni-cross"></em></div>
                </div>
                <div class="modal-body">


                    <div class="tab-content mb-2">
                        <div class="tab-pane p-0 fade show active" id="si-yml" role="tabpanel"
                             aria-labelledby="v-si-yml-tab">

                            <form class="ajax-form" action="/apps/shop/import/yml">

                                <div class="form-group">
                                    <label class="col-form-label"><?= __('apps.form_label_yml_link', 'Ссылка на YML экспорт из вашего магазина'); ?></label>
                                    <input type="text" class="form-control edit-field-title" name="data[yml]"
                                           value="<?= getParam('apps_shop_yml_import'); ?>" required="required"
                                           placeholder="<?= __('apps.form_ph_yml_link', 'https://vashmagazin.ru/ssilka-na-xml-vygruzku-v-formate-yml'); ?>">
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" name="data[prices]" value="1" id="si-yml-prices"
                                               class="custom-control-input">
                                        <label class="custom-control-label"
                                               for="si-yml-prices"><?= __('apps.form_label_yml_prices', 'Обновить только цены'); ?></label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" name="data[categories]" value="1" id="si-yml-cats-import"
                                               class="custom-control-input">
                                        <label class="custom-control-label"
                                               for="si-yml-cats-import"><?= __('apps.form_label_yml_cats_import', 'Импортировать категории'); ?></label>
                                    </div>
                                </div>

                                <div class="ajax-form-result"></div>

                                <div class="d-flex justify-content-between mt-5">
                                    <button class="btn btn-light" type="button"
                                            data-bs-dismiss="modal"><?= __('app.cancel_btn', 'Cancel'); ?></button>
                                    <button class="btn btn-primary"><?= __('app.import_btn', 'Import'); ?></button>
                                </div>

                            </form>

                        </div>


                        <div class="tab-pane p-0 fade" id="si-wb" role="tabpanel" aria-labelledby="v-si-wb-tab">


                            <form class="ajax-form" action="/apps/shop/import/wb">

                                <div class="form-group">
                                    <label class="col-form-label"><?= __('apps.form_label_yml_link', 'API токен для интеграции с WB'); ?></label>
                                    <input type="text" class="form-control edit-field-title" name="data[token]"
                                           value="<?= getParam('apps_shop_wb_token'); ?>" required="required"
                                           placeholder="<?= __('apps.form_ph_yml_link', 'https://vashmagazin.ru/ssilka-na-xml-vygruzku-v-formate-yml'); ?>">
                                </div>

                                <!-- <div class="form-group">
									<div class="custom-control custom-switch">
								    	<input type="checkbox" name="data[prices]" value="1" id="si-yml-prices" class="custom-control-input">
								        <label class="custom-control-label" for="si-yml-prices"><?= __('apps.form_label_yml_prices', 'Обновить только цены'); ?></label>
								    </div>
								</div> -->


                                <div class="ajax-form-result"></div>

                                <div class="d-flex justify-content-between mt-5">
                                    <button class="btn btn-light" type="button"
                                            data-bs-dismiss="modal"><?= __('app.cancel_btn', 'Cancel'); ?></button>
                                    <button class="btn btn-primary"><?= __('app.import_btn', 'Import'); ?></button>
                                </div>

                            </form>


                        </div>
                    </div>


                </div>


            </div>

        </div>
    </div>

<?= $this->endSection() ?>