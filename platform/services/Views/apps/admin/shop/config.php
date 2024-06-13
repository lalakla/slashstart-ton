<?= $this->extend('app') ?>
<?= $this->section('main') ?>

<link rel="stylesheet" href="/assets/css/widgets.css">
<div class="row mb-5 pb-5">
    <div class="col-md-6">


        <form action="/apps/shop/config" method="post" class="ajax-form" id="settingsForm" data-message="<?= sprintf(__('Параметры магазина <b>%s</b> успешно сохранены.'), $currentShop['title']); ?>">
            <input type="hidden" name="data[id]" value="<?= $currentShop['hash']; ?>">

            <div class="accordion settings-form">

                <div class="pull-right">
                    <button type="submit" class="btn btn-success btn-sm"><?= __('Сохранить'); ?></button>
                </div>


                <div class="sections-inline">

                    <div class="section-title">
                        <button class="btn btn-block btn-sm text-left app-shop-page-trigger" data-page="index" type="button" data-toggle="collapse" data-target="#shop-menu" aria-expanded="true" aria-controls="shop-menu">
                            <?= __('Главная'); ?>
                        </button>
                    </div>

                    <div class="section-title">
                        <button class="btn btn-block btn-sm text-left collapsed app-shop-page-trigger" data-page="index" type="button" data-toggle="collapse" data-target="#shop-categories" aria-controls="shop-categories">
                            <?= __('Категории'); ?>
                        </button>
                    </div>

                    <div class="section-title">
                        <button class="btn btn-block btn-sm text-left collapsed app-shop-page-trigger" data-page="catalog" type="button" data-toggle="collapse" data-target="#shop-catalog" aria-controls="shop-catalog">
                            <?= __('Товары'); ?>
                        </button>
                    </div>

                    <div class="section-title">
                        <button class="btn btn-block btn-sm text-left collapsed app-shop-page-trigger" data-page="catalog" type="button" data-toggle="collapse" data-target="#shop-product" aria-controls="shop-product">
                            <?= __('Товар'); ?>
                        </button>
                    </div>

                    <div class="section-title">
                        <button class="btn btn-block btn-sm text-left collapsed app-shop-page-trigger" data-page="cart" type="button" data-toggle="collapse" data-target="#shop-cart" aria-controls="shop-cart">
                            <?= __('Заказ'); ?>
                        </button>
                    </div>

                    <div class="section-title">
                        <button class="btn btn-block btn-sm text-left collapsed app-shop-page-trigger" data-page="thanks" type="button" data-toggle="collapse" data-target="#shop-thanks" aria-controls="shop-thanks">
                            <?= __('Спасибо'); ?>
                        </button>
                    </div>

                    <div class="section-title">
                        <button class="btn btn-block btn-sm text-left collapsed app-shop-page-trigger" data-page="catalog" type="button" data-toggle="collapse" data-target="#shop-styles" aria-controls="shop-styles">
                            <?= __('Стили'); ?>
                        </button>
                    </div>

                    <div class="section-title">
                        <button class="btn btn-block btn-sm text-left collapsed app-shop-page-trigger" data-page="index" type="button" data-toggle="collapse" data-target="#shop-cashback" aria-controls="shop-cashback">
                            <?= __('Кэшбэк'); ?>
                        </button>
                    </div>

                    <div class="section-title">
                        <button class="btn btn-block btn-sm text-left collapsed app-shop-page-trigger" data-page="shop2" type="button" data-toggle="collapse" data-target="#shop-shop2" aria-controls="shop-shop2">
                            <?= __('Магазин 2.0'); ?>
                        </button>
                    </div>

                </div>

                <div id="shop-menu" class="section-content collapse show" aria-labelledby="shop-menu" data-parent="#settingsForm">
                    <div class="form-group settings_menu_title">
                        <label><?= __('Текст кнопки каталога'); ?></label>
                        <input type="text" value="<?= h($config['menu']['title'] ?? ''); ?>" name="data[settings][menu][title]" class="form-control" data-keyup='{"s" : ".rplto-widget .menu-label-text", "p" : "innerHTML"}'>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" value="1" id="disable-menu" <?= (!empty($config['menu']['disable']) ? 'checked="checked"' : ''); ?> name="data[settings][menu][disable]" data-change='[{"s" : ".rplto-widget .navbar-toggler, .settings_menu_title", "p" : "hideiftrue"}]' class="custom-control-input">
                            <label class="custom-control-label" for="disable-menu"><?= __('Скрыть кнопку каталога'); ?></label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" value="1" id="disable-home-cats" <?= (!empty($config['main']['disable_home_cats']) ? 'checked="checked"' : ''); ?> name="data[settings][main][disable_home_cats]" data-change='[{"s" : ".home_cats_demo", "p" : "hideiftrue"}]' class="custom-control-input">
                            <label class="custom-control-label" for="disable-home-cats"><?= __('Скрыть категории'); ?></label>
                        </div>
                    </div>

                    <label><?= __('Элементы меню'); ?></label>
                    <p class="help-label"><?= __('С каталогом в строку вмещается до 1-3 элементов.'); ?></p>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <select class="form-control select2 shop-app-menu-items" value="<?= h($config['menu']['items'][0] ?? ''); ?>" name="data[settings][menu][items][0]">
                                    <option value=""><?= __('1 - Нет'); ?></option>
                                    <optgroup label="<?= __('Категории'); ?>">
                                        <?= $categoriesTree; ?>
                                    </optgroup>
                                    <optgroup label="<?= __('Товары'); ?>">
                                        <?php foreach ($products as $pr) : ?>
                                            <option value="p<?= $pr['id']; ?>" data-title="<?= hst($pr['title']); ?>"><?= $pr['title']; ?> #<?= $pr['sku']; ?></option>
                                        <?php endforeach ?>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <select class="form-control select2 shop-app-menu-items" value="<?= h($config['menu']['items'][1] ?? ''); ?>" name="data[settings][menu][items][1]">
                                    <option value=""><?= __('2 - Нет'); ?></option>
                                    <optgroup label="<?= __('Категории'); ?>">
                                        <?= $categoriesTree; ?>
                                    </optgroup>
                                    <optgroup label="<?= __('Товары'); ?>">
                                        <?php foreach ($products as $pr) : ?>
                                            <option value="p<?= $pr['id']; ?>" data-title="<?= hst($pr['title']); ?>"><?= $pr['title']; ?> #<?= $pr['sku']; ?></option>
                                        <?php endforeach ?>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <select class="form-control select2 shop-app-menu-items" value="<?= h($config['menu']['items'][2] ?? ''); ?>" name="data[settings][menu][items][2]">
                                    <option value=""><?= __('3 - Нет'); ?></option>
                                    <optgroup label="<?= __('Категории'); ?>">
                                        <?= $categoriesTree; ?>
                                    </optgroup>
                                    <optgroup label="<?= __('Товары'); ?>">
                                        <?php foreach ($products as $pr) : ?>
                                            <option value="p<?= $pr['id']; ?>" data-title="<?= hst($pr['title']); ?>"><?= $pr['title']; ?> #<?= $pr['sku']; ?></option>
                                        <?php endforeach ?>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <select class="form-control select2 shop-app-menu-items" value="<?= h($config['menu']['items'][3] ?? ''); ?>" name="data[settings][menu][items][3]">
                                    <option value=""><?= __('4 - Нет'); ?></option>
                                    <optgroup label="<?= __('Категории'); ?>">
                                        <?= $categoriesTree; ?>
                                    </optgroup>
                                    <optgroup label="<?= __('Товары'); ?>">
                                        <?php foreach ($products as $pr) : ?>
                                            <option value="p<?= $pr['id']; ?>" data-title="<?= hst($pr['title']); ?>"><?= $pr['title']; ?> #<?= $pr['sku']; ?></option>
                                        <?php endforeach ?>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <div class="form-group">
                                <select class="form-control select2 shop-app-menu-items" value="<?= h($config['menu']['items'][4] ?? ''); ?>" name="data[settings][menu][items][4]">
                                    <option value=""><?= __('5 - Нет'); ?></option>
                                    <optgroup label="<?= __('Категории'); ?>">
                                        <?= $categoriesTree; ?>
                                    </optgroup>
                                    <optgroup label="<?= __('Товары'); ?>">
                                        <?php foreach ($products as $pr) : ?>
                                            <option value="p<?= $pr['id']; ?>" data-title="<?= hst($pr['title']); ?>"><?= $pr['title']; ?> #<?= $pr['sku']; ?></option>
                                        <?php endforeach ?>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <select class="form-control select2 shop-app-menu-items" value="<?= h($config['menu']['items'][5] ?? ''); ?>" name="data[settings][menu][items][5]">
                                    <option value=""><?= __('6 - Нет'); ?></option>
                                    <optgroup label="<?= __('Категории'); ?>">
                                        <?= $categoriesTree; ?>
                                    </optgroup>
                                    <optgroup label="<?= __('Товары'); ?>">
                                        <?php foreach ($products as $pr) : ?>
                                            <option value="p<?= $pr['id']; ?>" data-title="<?= hst($pr['title']); ?>"><?= $pr['title']; ?> #<?= $pr['sku']; ?></option>
                                        <?php endforeach ?>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                    </div>

                    <label><?= __('Слайдер'); ?></label>
                    <p class="help-label"><?= __('Рекомендуемая ширина фото - до 700px. В демо виден только первый слайд.'); ?></p>

                    <div class="row g-1">
                        <div class="col-1">
                            <div class="shop-slider-photo-demo photo-demo-0"><img src="" alt=""></div>
                        </div>
                        <div class="col-3">
                            <div class="input-group">
                                <input type="text" class="form-control" name="data[settings][slider][items][0][photo]" id="slider-photo-0" value="<?= h($config['slider']['items'][0]['photo'] ?? ''); ?>" data-keyup='[{"s" : ".carousel-image img", "a" : "src", "p" : "attr"}, {"s" : ".photo-demo-0 img", "a" : "src", "p" : "attr"}, {"s" : ".photo-demo-0, .carousel-image", "p" : "showiftrue"}]'>
                                <div class="input-group-append">
                                    <div class="wf-input-tb" style="top: 5px;">
                                        <div class="wf-input-tb-action wf-input-tb-upload" data-target="#slider-photo-0"><i class="fa fa-picture-o"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <select class="form-control select2" value="<?= h($config['slider']['items'][0]['link'] ?? ''); ?>" name="data[settings][slider][items][0][link]">
                                    <option value=""><?= __('Ссылка справа'); ?></option>
                                    <optgroup label="<?= __('Категории'); ?>">
                                        <?= $categoriesTree; ?>
                                    </optgroup>
                                    <optgroup label="<?= __('Товары'); ?>">
                                        <?php foreach ($products as $pr) : ?>
                                            <option value="p<?= $pr['id']; ?>" data-title="<?= hst($pr['title']); ?>"><?= $pr['title']; ?> #<?= $pr['sku']; ?></option>
                                        <?php endforeach ?>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="data[settings][slider][items][0][href]" id="slider-href-0" value="<?= h($config['slider']['items'][0]['href'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row g-1">
                        <div class="col-1">
                            <div class="shop-slider-photo-demo photo-demo-1"><img src="" alt=""></div>
                        </div>
                        <div class="col-3">
                            <div class="input-group">
                                <input type="text" class="form-control" name="data[settings][slider][items][1][photo]" id="slider-photo-1" value="<?= h($config['slider']['items'][1]['photo'] ?? ''); ?>" data-keyup='[{"s" : ".photo-demo-1 img", "a" : "src", "p" : "attr"}, {"s" : ".photo-demo-1", "p" : "showiftrue"}]'>
                                <div class="input-group-append">
                                    <div class="wf-input-tb" style="top: 5px;">
                                        <div class="wf-input-tb-action wf-input-tb-upload" data-target="#slider-photo-1"><i class="fa fa-picture-o"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <select class="form-control select2" value="<?= h($config['slider']['items'][1]['link'] ?? ''); ?>" name="data[settings][slider][items][1][link]">
                                    <option value=""><?= __('Ссылка справа'); ?></option>
                                    <optgroup label="<?= __('Категории'); ?>">
                                        <?= $categoriesTree; ?>
                                    </optgroup>
                                    <optgroup label="<?= __('Товары'); ?>">
                                        <?php foreach ($products as $pr) : ?>
                                            <option value="p<?= $pr['id']; ?>" data-title="<?= hst($pr['title']); ?>"><?= $pr['title']; ?> #<?= $pr['sku']; ?></option>
                                        <?php endforeach ?>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="data[settings][slider][items][1][href]" id="slider-href-1" value="<?= h($config['slider']['items'][1]['href'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row g-1">
                        <div class="col-1">
                            <div class="shop-slider-photo-demo photo-demo-2"><img src="" alt=""></div>
                        </div>
                        <div class="col-3">
                            <div class="input-group">
                                <input type="text" class="form-control" name="data[settings][slider][items][2][photo]" id="slider-photo-2" value="<?= h($config['slider']['items'][2]['photo'] ?? ''); ?>" data-keyup='[{"s" : ".photo-demo-2 img", "a" : "src", "p" : "attr"}, {"s" : ".photo-demo-2", "p" : "showiftrue"}]'>
                                <div class="input-group-append">
                                    <div class="wf-input-tb" style="top: 5px;">
                                        <div class="wf-input-tb-action wf-input-tb-upload" data-target="#slider-photo-2"><i class="fa fa-picture-o"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <select class="form-control select2" value="<?= h($config['slider']['items'][2]['link'] ?? ''); ?>" name="data[settings][slider][items][2][link]">
                                    <option value=""><?= __('Ссылка справа'); ?></option>
                                    <optgroup label="<?= __('Категории'); ?>">
                                        <?= $categoriesTree; ?>
                                    </optgroup>
                                    <optgroup label="<?= __('Товары'); ?>">
                                        <?php foreach ($products as $pr) : ?>
                                            <option value="p<?= $pr['id']; ?>" data-title="<?= hst($pr['title']); ?>"><?= $pr['title']; ?> #<?= $pr['sku']; ?></option>
                                        <?php endforeach ?>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="data[settings][slider][items][2][href]" id="slider-href-2" value="<?= h($config['slider']['items'][2]['href'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row g-1">
                        <div class="col-1">
                            <div class="shop-slider-photo-demo photo-demo-3"><img src="" alt=""></div>
                        </div>
                        <div class="col-3">
                            <div class="input-group">
                                <input type="text" class="form-control" name="data[settings][slider][items][3][photo]" id="slider-photo-3" value="<?= h($config['slider']['items'][3]['photo'] ?? ''); ?>" data-keyup='[{"s" : ".photo-demo-3 img", "a" : "src", "p" : "attr"}, {"s" : ".photo-demo-3", "p" : "showiftrue"}]'>
                                <div class="input-group-append">
                                    <div class="wf-input-tb" style="top: 5px;">
                                        <div class="wf-input-tb-action wf-input-tb-upload" data-target="#slider-photo-3"><i class="fa fa-picture-o"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <select class="form-control select2" value="<?= h($config['slider']['items'][3]['link'] ?? ''); ?>" name="data[settings][slider][items][3][link]">
                                    <option value=""><?= __('Ссылка справа'); ?></option>
                                    <optgroup label="<?= __('Категории'); ?>">
                                        <?= $categoriesTree; ?>
                                    </optgroup>
                                    <optgroup label="<?= __('Товары'); ?>">
                                        <?php foreach ($products as $pr) : ?>
                                            <option value="p<?= $pr['id']; ?>" data-title="<?= hst($pr['title']); ?>"><?= $pr['title']; ?> #<?= $pr['sku']; ?></option>
                                        <?php endforeach ?>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="data[settings][slider][items][3][href]" id="slider-href-3" value="<?= h($config['slider']['items'][3]['href'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row g-1">
                        <div class="col-1">
                            <div class="shop-slider-photo-demo photo-demo-4"><img src="" alt=""></div>
                        </div>
                        <div class="col-3">
                            <div class="input-group">
                                <input type="text" class="form-control" name="data[settings][slider][items][4][photo]" id="slider-photo-4" value="<?= h($config['slider']['items'][4]['photo'] ?? ''); ?>" data-keyup='[{"s" : ".photo-demo-4 img", "a" : "src", "p" : "attr"}, {"s" : ".photo-demo-4", "p" : "showiftrue"}]'>
                                <div class="input-group-append">
                                    <div class="wf-input-tb" style="top: 5px;">
                                        <div class="wf-input-tb-action wf-input-tb-upload" data-target="#slider-photo-4"><i class="fa fa-picture-o"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <select class="form-control select2" value="<?= h($config['slider']['items'][4]['link'] ?? ''); ?>" name="data[settings][slider][items][4][link]">
                                    <option value=""><?= __('Ссылка справа'); ?></option>
                                    <optgroup label="<?= __('Категории'); ?>">
                                        <?= $categoriesTree; ?>
                                    </optgroup>
                                    <optgroup label="<?= __('Товары'); ?>">
                                        <?php foreach ($products as $pr) : ?>
                                            <option value="p<?= $pr['id']; ?>" data-title="<?= hst($pr['title']); ?>"><?= $pr['title']; ?> #<?= $pr['sku']; ?></option>
                                        <?php endforeach ?>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="data[settings][slider][items][4][href]" id="slider-href-4" value="<?= h($config['slider']['items'][4]['href'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row g-1">
                        <div class="col-1">
                            <div class="shop-slider-photo-demo photo-demo-5"><img src="" alt=""></div>
                        </div>
                        <div class="col-3">
                            <div class="input-group">
                                <input type="text" class="form-control" name="data[settings][slider][items][5][photo]" id="slider-photo-5" value="<?= h($config['slider']['items'][5]['photo'] ?? ''); ?>" data-keyup='[{"s" : ".photo-demo-5 img", "a" : "src", "p" : "attr"}, {"s" : ".photo-demo-5", "p" : "showiftrue"}]'>
                                <div class="input-group-append">
                                    <div class="wf-input-tb" style="top: 5px;">
                                        <div class="wf-input-tb-action wf-input-tb-upload" data-target="#slider-photo-5"><i class="fa fa-picture-o"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <select class="form-control select2" value="<?= h($config['slider']['items'][5]['link'] ?? ''); ?>" name="data[settings][slider][items][5][link]">
                                    <option value=""><?= __('Ссылка справа'); ?></option>
                                    <optgroup label="<?= __('Категории'); ?>">
                                        <?= $categoriesTree; ?>
                                    </optgroup>
                                    <optgroup label="<?= __('Товары'); ?>">
                                        <?php foreach ($products as $pr) : ?>
                                            <option value="p<?= $pr['id']; ?>" data-title="<?= hst($pr['title']); ?>"><?= $pr['title']; ?> #<?= $pr['sku']; ?></option>
                                        <?php endforeach ?>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="data[settings][slider][items][5][href]" id="slider-href-5" value="<?= h($config['slider']['items'][5]['href'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>


                    <div class="form-group mt-4">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" value="1" id="slider-banners" <?= (!empty($config['slider']['bannermode']) ? 'checked="checked"' : ''); ?> name="data[settings][slider][bannermode]" data-change='[{"s" : ".slider-view-slider", "p" : "hideiftrue"}, {"s" : ".slider-view-banners, .settings_banners_cols", "p" : "showiftrue"}]' class="custom-control-input">
                            <label class="custom-control-label" for="slider-banners"><?= __('Отображать слайды как баннеры'); ?></label>
                        </div>
                        <div class="help-label"><?= __('Слайдер отключится. В демо показывается 3 баннера из первого фото слайдера.'); ?></div>
                    </div>

                    <div class="form-group settings_banners_cols">
                        <label><?= __('Количество баннеров в строке'); ?></label>
                        <select class="form-control select2" value="<?= h($config['slider']['banners_cols'] ?? 1); ?>" name="data[settings][slider][banners_cols]" data-change='[{"s" : ".slider-view-banners-1, .slider-view-banners-2, .slider-view-banners-3", "p" : "hideiftrue"}, {"p" : "showbyvalue"}]'>
                            <option value="1" data-value=".slider-view-banners-1">1</option>
                            <option value="2" data-value=".slider-view-banners-2">2</option>
                            <option value="3" data-value=".slider-view-banners-3">3</option>
                        </select>

                        <div class="help-label"><?= __('Для 2-3 баннеров в строку лучше выглядят квадратные или вертикальные баннеры. Горизонтальные слишком узкие. Если 3 в строку, а баннеров 5, то в последней строке будет 2 баннера. Если 2 в строку и баннеров 3, то в последней будет 1. И т.п. Это стоит учитывать при выборе размеров и расстановке баннеров.'); ?></div>
                    </div>



                    <div class="form-group">
                        <label><?= __('Заголовок товаров на главной'); ?></label>
                        <input type="text" value="<?= h($config['products']['home_title'] ?? ''); ?>" name="data[settings][products][home_title]" placeholder="<?= __('Новинки') ?>" class="form-control">
                    </div>

                    <div class="form-group mt-2">
                        <label><?= __('HTML код внизу страницы'); ?></label>
                        <textarea name="data[settings][home_html]" class="form-control"><?= h($config['home_html'] ?? ''); ?></textarea>
                        <div class="help-label"><?= __('Произвольный HTML код для программистов. Отображается только на главной после всех блоков.'); ?></div>
                    </div>

                    <!--div class="row">
						<div class="col">
							<div class="form-group">
						        <label><?= __('Кэшбэк для всех товаров, в %'); ?></label>
						        <div class="input-group">
									<input type="text" value="<?= h($config['main']['cashback'] ?? ''); ?>" name="data[settings][main][cashback]" placeholder="0" class="form-control">
								</div>
						    </div>
						</div>
						<div class="col">
							<div class="form-group">
						        <label><?= __('Максимальный % для заказа'); ?></label>
						        <div class="input-group">
									<input type="text" value="<?= h($config['main']['cashback_max'] ?? ''); ?>" name="data[settings][main][cashback_max]" placeholder="100" class="form-control">
								</div>
						    </div>
						</div>
					</div-->

                    <!--div class="row">
						<div class="col">
						   	<input type="hidden" name="data[settings][main][cbonpayment]" value="0">
						    <div class="mt-4 custom-control custom-switch custom-control-inline custom-control-sm mr-0 mt-3 cursor-pointer">
								<input name="data[settings][main][cbonpayment]" type="checkbox" value="1" id="product-cbonpayment" class="custom-control-input" <?php if (!empty($config['main']['cbonpayment'])) : ?>checked="checked"<?php endif ?>>
							   	<label class="custom-control-label cursor-pointer" for="product-cbonpayment"><?= __('Начислять кэшбэк при оплате'); ?></label>
							</div>
						</div>
						<div class="col">
						   	<input type="hidden" name="data[settings][main][cashback_disabled]" value="0">
						    <div class="mt-4 custom-control custom-switch custom-control-inline custom-control-sm mr-0 mt-3 cursor-pointer">
								<input name="data[settings][main][cashback_disabled]" type="checkbox" value="1" id="product-cashback_disabled" class="custom-control-input" <?php if (!empty($config['main']['cashback_disabled'])) : ?>checked="checked"<?php endif ?>>
							   	<label class="custom-control-label cursor-pointer" for="product-cashback_disabled"><?= __('Отключить кэшбэк в магазине'); ?></label>
							</div>
						</div>
					</div-->


                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label><?= __('Минимальная сумма заказа'); ?></label>
                                <div class="input-group">
                                    <input type="text" value="<?= h($config['main']['minorder'] ?? ''); ?>" name="data[settings][main][minorder]" placeholder="0" class="form-control">
                                </div>
                                <div class="help-label"><?= __('Пусто или 0 - нет ограничений.'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="shop-categories" class="section-content collapse hide" aria-labelledby="shop-categories" data-parent="#settingsForm">
                    <div class="form-group">
                        <label><?= __('Стиль отображения'); ?></label>
                        <select class="form-control select2" value="<?= h($config['categories']['style'] ?? ''); ?>" name="data[settings][categories][style]" data-change='[{"s" : ".rplto-widget .category-item", "p" : "classes", "c" : "style-default style-bgrtitle style-nobgr"}]'>
                            <option value="" data-value="style-default"><?= __('На фоне'); ?></option>
                            <option value="bgrtitle" data-value="style-bgrtitle"><?= __('Фон для заголовка'); ?></option>
                            <option value="nobgr" data-value="style-nobgr"><?= __('Без фона'); ?></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" value="1" id="disable-categories_noround" <?= (!empty($config['categories']['noround']) ? 'checked="checked"' : ''); ?> name="data[settings][categories][noround]" data-change='[{"s" : ".rplto-widget .category-item", "p" : "classes", "c" : "style-noround"}]' class="custom-control-input">
                            <label class="custom-control-label" for="disable-categories_noround"><?= __('Убрать закругления'); ?></label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" value="1" id="disable-categories_titlemargin" <?= (!empty($config['categories']['titlemargin']) ? 'checked="checked"' : ''); ?> name="data[settings][categories][titlemargin]" data-change='[{"s" : ".rplto-widget .category-item", "p" : "classes", "c" : "style-titlemargin"}]' class="custom-control-input">
                            <label class="custom-control-label" for="disable-categories_titlemargin"><?= __('Добавить отступ заголовку'); ?></label>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label><?= __('Фон'); ?></label>
                                <input type="text" value="<?= h($config['styles']['category_bg'] ?? '#ffffff'); ?>" data-default-value="#ffffff" name="data[settings][styles][category_bg]" class="form-control colorpicker" data-change='[{"s" : ".rplto-widget .category-item, .rplto-widget .category-item .item-title", "a" : "background-color", "p" : "css"}]'>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label><?= __('Фон заголовка'); ?></label>
                                <input type="text" value="<?= h($config['styles']['category_title_bg'] ?? '#ffffff'); ?>" data-default-value="#ffffff" name="data[settings][styles][category_title_bg]" class="form-control colorpicker" data-change='[{"s" : ".rplto-widget .category-item .item-title span", "a" : "background-color", "p" : "css"}]'>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label><?= __('Текста заголовка'); ?></label>
                                <input type="text" value="<?= h($config['styles']['category_title_text'] ?? '#000000'); ?>" data-default-value="#000000" name="data[settings][styles][category_title_text]" class="form-control colorpicker" data-change='[{"s" : ".rplto-widget .category-item .item-title span", "a" : "color", "p" : "css"}]'>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="shop-product" class="section-content collapse hide" aria-labelledby="shop-product" data-parent="#settingsForm">
                    <div class="form-group">
                        <label><?= __('Стиль отображения'); ?></label>
                        <select class="form-control select2" value="<?= h($config['product']['style'] ?? ''); ?>" name="data[settings][product][style]" data-change='[{"s": ".prm-cslider, .prm-cslider", "p" : "css", "a" : "display", "v" : "none"}, {"p" : "showbyvalue"}]'>
                            <option value="" data-value=".prm-cslider"><?= __('По блокам'); ?></option>
                            <option value="photol" data-value="photol"><?= __('Обтякаемое фото слева (1 фото)'); ?></option>
                            <option value="cslider" data-value=".prm-cslider"><?= __('Слайдер или фото в центре'); ?></option>
                            <option value="spreview" data-value=".prm-cslider"><?= __('Слайдер с маленькими превью внизу'); ?></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" value="1" id="disable-product_nopanels" <?= (!empty($config['product']['nopanels']) ? 'checked="checked"' : ''); ?> name="data[settings][product][nopanels]" class="custom-control-input">
                            <label class="custom-control-label" for="disable-product_nopanels"><?= __('Убрать подложки блоков'); ?></label>
                        </div>
                    </div>

                    <div class="prm-cslider prm-cslider hidden">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" value="1" id="disable-product_resize_photo" <?= (!empty($config['product']['resize_photo']) ? 'checked="checked"' : ''); ?> name="data[settings][product][resize_photo]" class="custom-control-input">
                                <label class="custom-control-label" for="disable-product_resize_photo"><?= __('Растянуть фото на весь экран'); ?></label>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="shop-catalog" class="section-content collapse hide" aria-labelledby="shop-catalog" data-parent="#settingsForm">
                    <div class="form-group">
                        <label><?= __('Стиль товаров'); ?></label>
                        <select class="form-control select2" value="<?= h($config['products']['style'] ?? ''); ?>" name="data[settings][products][style]" data-change='[{"s" : ".rplto-widget .item-product", "p" : "classes", "c" : "style-default style-roundphoto"}]'>
                            <option value="" data-value="style-default"><?= __('Стандартный'); ?></option>
                            <option value="roundphoto" data-value="style-roundphoto"><?= __('Круглое фото'); ?></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" value="1" id="disable-products_nopaddings" <?= (!empty($config['products']['nopaddings']) ? 'checked="checked"' : ''); ?> name="data[settings][products][nopaddings]" data-change='[{"s" : ".rplto-widget .item-product", "p" : "classes", "c" : "style-nopadd"}]' class="custom-control-input">
                            <label class="custom-control-label" for="disable-products_nopaddings"><?= __('Убрать отступы'); ?></label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" value="1" id="disable-products_noround" <?= (!empty($config['products']['noround']) ? 'checked="checked"' : ''); ?> name="data[settings][products][noround]" data-change='[{"s" : ".rplto-widget .item-product", "p" : "classes", "c" : "style-noround"}]' class="custom-control-input">
                            <label class="custom-control-label" for="disable-products_noround"><?= __('Убрать закругления'); ?></label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" value="1" id="disable-products_nopbgr" <?= (!empty($config['products']['nopbgr']) ? 'checked="checked"' : ''); ?> name="data[settings][products][nopbgr]" data-change='[{"s" : ".rplto-widget .item-product", "p" : "classes", "c" : "style-nopbgr"}]' class="custom-control-input">
                            <label class="custom-control-label" for="disable-products_nopbgr"><?= __('Фон только для описания'); ?></label>
                        </div>
                    </div>


                    <div class="form-group">
                        <label><?= __('Отображение товаров'); ?></label>
                        <select class="form-control select2" value="<?= h($config['products']['view'] ?? ''); ?>" name="data[settings][products][view]" data-change='[{"s" : ".rplto-widget .shop-page-demo-catalog .view-list, .rplto-widget .shop-page-demo-catalog .view-grid3, .rplto-widget .shop-page-demo-catalog .view-grid2", "p" : "hideiftrue"},{"p" : "showbyvalue"}]'>
                            <option value="list" data-value=".view-list"><?= __('Список (1 в строку)'); ?></option>
                            <option value="grid" data-value=".view-grid3"><?= __('Плитка (3 в строку)'); ?></option>
                            <option value="grid2" data-value=".view-grid2"><?= __('Плитка (2 в строку)'); ?></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" value="1" id="disable-settings_prices" <?= (!empty($config['products']['prices_hide']) ? 'checked="checked"' : ''); ?> name="data[settings][products][prices_hide]" data-change='[{"s" : ".rplto-widget .price, .settings_prices", "p" : "hideiftrue"}, {"s" : ".rplto-widget .price-label-left", "p" : "showiftrue"}]' class="custom-control-input">
                            <label class="custom-control-label" for="disable-settings_prices"><?= __('Не показывать цены'); ?></label>
                        </div>
                    </div>

                    <div class="settings_prices">


                        <div class="form-group">
                            <label><?= __('Валюта'); ?></label>
                            <input type="text" value="<?= h($config['products']['currency'] ?? __('руб.')); ?>" name="data[settings][products][currency]" class="form-control" placeholder="<?= __('руб.'); ?>" data-keyup='{"s" : ".rplto-widget .price-label", "p" : "innerHTML"}'>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" value="1" id="currency_left" <?= (!empty($config['products']['currency_left']) ? 'checked="checked"' : ''); ?> name="data[settings][products][currency_left]" data-change='[{"s" : ".rplto-widget .price-label-right", "p" : "hideiftrue"}, {"s" : ".rplto-widget .price-label-left", "p" : "showiftrue"}]' class="custom-control-input">
                                <label class="custom-control-label" for="currency_left"><?= __('Валюта слева от цены'); ?></label>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label><?= __('Тысячи'); ?></label>
                                    <select class="form-control select2 shop-app-currency-format" data-cf="tsep" value="<?= h($config['products']['currency_tsep'] ?? ''); ?>" name="data[settings][products][currency_tsep]">
                                        <option value="s"><?= __('1 000 000'); ?></option>
                                        <option value="c"><?= __('1,000,000'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label><?= __('Точность'); ?></label>
                                    <select class="form-control select2 shop-app-currency-format" data-cf="dec" value="<?= h($config['products']['currency_dec'] ?? ''); ?>" name="data[settings][products][currency_dec]" data-change='[{"s" : ".settings_currency_dsep", "p" : "showiftrue"}]'>
                                        <option value=""><?= __('0'); ?></option>
                                        <option value="1"><?= __('0.0'); ?></option>
                                        <option value="2"><?= __('0.00'); ?></option>
                                        <option value="3"><?= __('0.000'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col settings_currency_dsep">
                                <div class="form-group">
                                    <label><?= __('Разделитель'); ?></label>
                                    <select class="form-control select2 shop-app-currency-format" data-cf="dsep" value="<?= h($config['products']['currency_dsep'] ?? ''); ?>" name="data[settings][products][currency_dsep]">
                                        <option value="p"><?= __('.'); ?></option>
                                        <option value="c"><?= __(','); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>



                    <div class="form-group mt-4">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" value="1" id="cart_btn_disable" <?= (!empty($config['products']['cart_btn_disable']) ? 'checked="checked"' : ''); ?> name="data[settings][products][cart_btn_disable]" data-change='[{"s" : ".product-cart-label, .catalog_disable_cart, .product_page_disable", "p" : "hideiftrue"}]' class="custom-control-input">
                            <label class="custom-control-label" for="cart_btn_disable"><?= __('Отключить заказ в списке продуктов'); ?></label>
                        </div>
                        <div class="help-label"><?= __('Можно будет оформить заказ только со страницы продукта'); ?></div>
                    </div>

                    <div class="form-group catalog_disable_cart">
                        <label><?= __('Текст кнопок корзины'); ?></label>
                        <input type="text" value="<?= h($config['products']['cart_btn_label'] ?? __('В корзину')); ?>" name="data[settings][products][cart_btn_label]" class="form-control" placeholder="<?= __('В корзину'); ?>" data-keyup='[{"s" : ".rplto-widget .product-cart-label", "p" : "innerHTML"}]'>
                    </div>



                    <div class="form-group product_page_disable">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" value="1" id="sett-product_page_disable" <?= (!empty($config['products']['product_page_disable']) ? 'checked="checked"' : ''); ?> name="data[settings][products][product_page_disable]" class="custom-control-input">
                            <label class="custom-control-label" for="sett-product_page_disable"><?= __('Отключить переход на страницу продукта'); ?></label>
                        </div>
                        <div class="help-label"><?= __('Можно будет оформить заказ только из каталога'); ?></div>
                    </div>



                    <div class="form-group">
                        <label><?= __('Текст перехода к заказу'); ?></label>
                        <input type="text" value="<?= h($config['main']['checkout_label'] ?? __('Оформить заказ')); ?>" name="data[settings][main][checkout_label]" class="form-control" placeholder="<?= __('Оформить заказ'); ?>" data-keyup='{"s" : ".rplto-widget .shop-footer-cart-label", "p" : "innerHTML"}'>
                    </div>






                    <!-- <div class="form-group">
				        <label><?= __('Коды внизу страницы'); ?></label>
				        <textarea name="data[settings][footer][codes]" data-change='{"s" : ".microlp .footer .codes", "p" : "innerHTML"}' class="form-control"><?= h($config['footer']['codes'] ?? ''); ?></textarea>
				    </div>

				    <div class="alert small alert-info alert-bordered rte-text">
				    	<?= sprintf(__('Здесь коды для текущей страницы, например, виджет комментариев или видео. Общие коды счетчиков и специальные мета-теги для HEAD/BODY на всех страницах вы можете указать в <a href="%s" target="_blank">Общих настройках</a>.'), '/settings'); ?>
			    	</div> -->

                </div>

                <div id="shop-cart" class="section-content collapse hide" aria-labelledby="shop-cart" data-parent="#settingsForm">

                    <div class="form-group">
                        <label><?= __('Заголовок корзины'); ?></label>
                        <input type="text" value="<?= h($config['cart']['order_table_title'] ?? __('Ваш заказ')); ?>" name="data[settings][cart][order_table_title]" class="form-control" data-keyup='[{"s" : ".rplto-widget .cart-table-title", "p" : "innerHTML"}, {"s" : ".rplto-widget .cart-table-title", "p" : "showiftrue"}]'>
                    </div>
                    <div class="form-group">
                        <label><?= __('Вспомогательный текст'); ?></label>
                        <input type="text" value="<?= h($config['cart']['order_table_content'] ?? ''); ?>" name="data[settings][cart][order_table_content]" class="form-control" data-keyup='[{"s" : ".rplto-widget .cart-page-content", "p" : "innerHTML"}, {"s" : ".rplto-widget .cart-page-content", "p" : "showiftrue"}]'>
                    </div>

                    <div class="form-group mt-4">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" value="1" id="shop_show_qty_inp" <?= (!empty($config['products']['shop_show_qty_inp']) ? 'checked="checked"' : ''); ?> name="data[settings][products][shop_show_qty_inp]" data-change='[{"s" : ".product-cart-qty", "p" : "showiftrue"}]' class="custom-control-input">
                            <label class="custom-control-label" for="shop_show_qty_inp"><?= __('Добавить ввод количества товаров'); ?></label>
                        </div>
                    </div>


                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" value="1" id="disable-settings_cart" <?= (!empty($config['cart']['disable']) ? 'checked="checked"' : ''); ?> name="data[settings][cart][disable]" data-change='[{"s" : ".settings_cart_mode_cart", "p" : "hideiftrue"}, {"s" : ".settings_cart_mode_one, .settings_cart_disabled", "p" : "showiftrue"}]' class="custom-control-input">
                            <label class="custom-control-label" for="disable-settings_cart"><?= __('Отключить корзину. Покупка 1 товара за раз.'); ?></label>
                        </div>
                        <div class="help-label"><?= __('Можно заказать только один продукт за раз.'); ?></div>
                    </div>


                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" value="1" id="sett-checkoutonorder" <?= (!empty($config['cart']['imcheckout']) ? 'checked="checked"' : ''); ?> name="data[settings][cart][imcheckout]" class="custom-control-input" data-change='[{"s" : ".settings_imcheckout", "p" : "showiftrue"}, {"s" : ".settings_not_imcheckout", "p" : "hideiftrue"}]'>
                            <label class="custom-control-label" for="sett-checkoutonorder"><?= __('Сразу оформлять заказ и переходить к оплате'); ?></label>
                        </div>
                        <div class="help-label"><?= __('Если корзина отключена, при заказе товара, сразу будет переход на оплату. Если включена, то переход на оплату будет при клике на общую кнопку оформления заказа. Для перехода на оплату нужно, чтобы в разделе "Спасибо" была выбрана платежная система.'); ?></div>
                    </div>



                    <div class="settings_not_imcheckout">

                        <div class="form-group">
                            <label><?= __('Заголовок формы заказа'); ?></label>
                            <input type="text" value="<?= h($config['cart']['order_form_title'] ?? __('Оформить заказ')); ?>" name="data[settings][cart][order_form_title]" class="form-control" data-keyup='[{"s" : ".rplto-widget .cart-order-title", "p" : "innerHTML"}, {"s" : ".rplto-widget .cart-order-title-wrap", "p" : "showiftrue"}]'>
                        </div>


                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" value="1" id="sett-require_name" <?= (!empty($config['cart']['require_name']) ? 'checked="checked"' : ''); ?> name="data[settings][cart][require_name]" data-change='[{"s" : ".settings_require_name_checked", "p" : "showiftrue"}]' class="custom-control-input">
                                <label class="custom-control-label" for="sett-require_name"><?= __('Запрашивать имя при заказе'); ?></label>
                            </div>
                        </div>

                        <div class="settings_require_name_checked ps-4 mb-4">
                            <div class="form-group">
                                <label><?= __('Сохранить имя в атрибут'); ?></label>
                                <select class="form-control select2" value="<?= h($config['cart']['name_attr'] ?? ''); ?>" name="data[settings][cart][name_attr]" data-create-url="/flows/create/attributes">
                                    <option value=""><?= __('Выберите (или напечатайте новое название)'); ?></option>
                                    <?php if (!empty($attributes)) : foreach ($attributes as $group) : ?>
                                            <option value="<?= $group['id']; ?>"><?= h($group['title']); ?></option>
                                    <?php endforeach;
                                    endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" value="1" id="sett-name_noreplace" <?= (!empty($config['cart']['name_noreplace']) ? 'checked="checked"' : ''); ?> name="data[settings][cart][name_noreplace]" class="custom-control-input">
                                    <label class="custom-control-label" for="sett-name_noreplace"><?= __('Не обновлять, если заполнено'); ?></label>
                                </div>
                            </div>
                        </div>



                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" value="1" id="sett-require_phone" <?= (!empty($config['cart']['require_phone']) ? 'checked="checked"' : ''); ?> name="data[settings][cart][require_phone]" data-change='[{"s" : ".settings_require_phone_checked", "p" : "showiftrue"}]' class="custom-control-input">
                                <label class="custom-control-label" for="sett-require_phone"><?= __('Запрашивать телефон при заказе'); ?></label>
                            </div>
                        </div>

                        <div class="settings_require_phone_checked ps-4 mb-4">
                            <div class="form-group">
                                <label><?= __('Сохранить телефон в атрибут'); ?></label>
                                <select class="form-control select2" value="<?= h($config['cart']['phone_attr'] ?? ''); ?>" name="data[settings][cart][phone_attr]" data-create-url="/flows/create/attributes">
                                    <option value=""><?= __('Выберите (или напечатайте новое название)'); ?></option>
                                    <?php if (!empty($attributes)) : foreach ($attributes as $group) : ?>
                                            <option value="<?= $group['id']; ?>"><?= h($group['title']); ?></option>
                                    <?php endforeach;
                                    endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" value="1" id="sett-phone_noreplace" <?= (!empty($config['cart']['phone_noreplace']) ? 'checked="checked"' : ''); ?> name="data[settings][cart][phone_noreplace]" class="custom-control-input">
                                    <label class="custom-control-label" for="sett-phone_noreplace"><?= __('Не обновлять, если заполнено'); ?></label>
                                </div>
                            </div>
                        </div>



                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" value="1" id="sett-require_email" <?= (!empty($config['cart']['require_email']) ? 'checked="checked"' : ''); ?> name="data[settings][cart][require_email]" data-change='[{"s" : ".settings_require_email_checked", "p" : "showiftrue"}]' class="custom-control-input">
                                <label class="custom-control-label" for="sett-require_email"><?= __('Запрашивать email при заказе'); ?></label>
                            </div>
                        </div>

                        <div class="settings_require_email_checked ps-4 mb-4">
                            <div class="form-group">
                                <label><?= __('Сохранить email в атрибут'); ?></label>
                                <select class="form-control select2" value="<?= h($config['cart']['email_attr'] ?? ''); ?>" name="data[settings][cart][email_attr]" data-create-url="/flows/create/attributes">
                                    <option value=""><?= __('Выберите (или напечатайте новое название)'); ?></option>
                                    <?php if (!empty($attributes)) : foreach ($attributes as $group) : ?>
                                            <option value="<?= $group['id']; ?>"><?= h($group['title']); ?></option>
                                    <?php endforeach;
                                    endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" value="1" id="sett-email_noreplace" <?= (!empty($config['cart']['email_noreplace']) ? 'checked="checked"' : ''); ?> name="data[settings][cart][email_noreplace]" class="custom-control-input">
                                    <label class="custom-control-label" for="sett-email_noreplace"><?= __('Не обновлять, если заполнено'); ?></label>
                                </div>
                            </div>
                        </div>




                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" value="1" id="sett-require_address" <?= (!empty($config['cart']['require_address']) ? 'checked="checked"' : ''); ?> name="data[settings][cart][require_address]" data-change='[{"s" : ".settings_require_address_checked", "p" : "showiftrue"}]' class="custom-control-input">
                                <label class="custom-control-label" for="sett-require_address"><?= __('Запрашивать адрес при заказе'); ?></label>
                            </div>
                        </div>

                        <div class="settings_require_address_checked ps-4 mb-4">
                            <div class="form-group">
                                <label><?= __('Сохранить адрес в атрибут'); ?></label>
                                <select class="form-control select2" value="<?= h($config['cart']['address_attr'] ?? ''); ?>" name="data[settings][cart][address_attr]" data-create-url="/flows/create/attributes">
                                    <option value=""><?= __('Выберите (или напечатайте новое название)'); ?></option>
                                    <?php if (!empty($attributes)) : foreach ($attributes as $group) : ?>
                                            <option value="<?= $group['id']; ?>"><?= h($group['title']); ?></option>
                                    <?php endforeach;
                                    endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" value="1" id="sett-address_noreplace" <?= (!empty($config['cart']['address_noreplace']) ? 'checked="checked"' : ''); ?> name="data[settings][cart][address_noreplace]" class="custom-control-input">
                                    <label class="custom-control-label" for="sett-address_noreplace"><?= __('Не обновлять, если заполнено'); ?></label>
                                </div>
                            </div>
                        </div>



                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" value="1" id="sett-disable_comment" <?= (!empty($config['cart']['disable_comment']) ? 'checked="checked"' : ''); ?> name="data[settings][cart][disable_comment]" data-change='[{"s" : ".settings_disabled_comments", "p" : "hideiftrue"}]' class="custom-control-input">
                                <label class="custom-control-label" for="sett-disable_comment"><?= __('Отключить поле комментария к заказу'); ?></label>
                            </div>
                        </div>
                        <div class="settings_disabled_comments pl-4">
                            <div class="form-group">
                                <label><?= __('Заголовок поля комментария'); ?></label>
                                <input type="text" value="<?= h($config['cart']['order_comment_title'] ?? __('Комментарий к заказу:')); ?>" name="data[settings][cart][order_comment_title]" class="form-control" placeholder="<?= __('Комментарий к заказу:'); ?>" data-keyup='[{"s" : ".rplto-widget .cart-form-comment-label", "p" : "innerHTML"}]'>
                            </div>
                            <div class="form-group">
                                <label><?= __('Подпись к полю комментария'); ?></label>
                                <input type="text" value="<?= h($config['cart']['order_comment_help'] ?? ''); ?>" name="data[settings][cart][order_comment_help]" class="form-control" placeholder="<?= __('Дополнительные пожелания к заказу'); ?>" data-keyup='[{"s" : ".rplto-widget .cart-form-comment-help", "p" : "innerHTML"}]'>
                            </div>

                        </div>




                        <div class="form-group mt-3">
                            <label><?= __('Текст кнопки заказа'); ?></label>
                            <input type="text" value="<?= h($config['cart']['order_btn'] ?? __('Заказать')); ?>" name="data[settings][cart][order_btn]" class="form-control" placeholder="<?= __('Заказать'); ?>" data-keyup='[{"s" : ".rplto-widget .cart-submit-label", "p" : "innerHTML"}]'>
                        </div>

                    </div>
                </div>

                <div id="shop-thanks" class="section-content collapse hide" aria-labelledby="shop-thanks" data-parent="#settingsForm">
                    <div class="form-group">
                        <label><?= __('Заголовок страницы'); ?></label>
                        <input type="text" value="<?= h($config['cart']['order_thanks_title'] ?? __('Спасибо! Ваш заказ оформлен')); ?>" name="data[settings][cart][order_thanks_title]" class="form-control" data-keyup='[{"s" : ".rplto-widget .cart-thanks-title", "p" : "innerHTML"}, {"s" : ".rplto-widget .cart-thanks-title", "p" : "showiftrue"}]'>
                    </div>
                    <div class="form-group">
                        <label><?= __('Вспомогательный текст'); ?></label>
                        <input type="text" value="<?= h($config['cart']['order_thanks_content'] ?? ''); ?>" name="data[settings][cart][order_thanks_content]" class="form-control" data-keyup='[{"s" : ".rplto-widget .cart-thanks-content", "p" : "innerHTML"}, {"s" : ".rplto-widget .cart-thanks-content", "p" : "showiftrue"}]'>
                    </div>


                    <div class="form-group">
                        <label><?= __('Текст кнопки'); ?></label>
                        <input type="text" value="<?= h($config['cart']['thanks_btn'] ?? ''); ?>" name="data[settings][cart][thanks_btn]" class="form-control" placeholder="<?= __(''); ?>" data-keyup='[{"s" : ".rplto-widget .cart-thanks-btn-label", "p" : "innerHTML"}, {"s" : ".rplto-widget .cart-thanks-btn-label", "p" : "showiftrue"}]'>
                    </div>



                    <div class="form-group">
                        <label><?= __('Что делать при клике на кнопку?'); ?></label>
                        <select class="form-control select2" value="<?= h($config['cart']['thanks_btn_action'] ?? ''); ?>" name="data[settings][cart][thanks_btn_action]" data-change='[{"s" : ".thanks-link, .thanks-payment", "p" : "hideiftrue"}, {"s" : ".thanks-link", "p" : "showbyvalue"}, {"s" : ".thanks-payment", "p" : "showbyvalue"}]'>
                            <option value="close" data-value=".thanks-close"><?= __('Закрыть окно и вернуться в чат по возможности'); ?></option>
                            <option value="link" data-value=".thanks-link"><?= __('Перейти по ссылке'); ?></option>
                            <option value="payment" data-value=".thanks-payment"><?= __('Перейти к оплате'); ?></option>
                        </select>
                        <div class="help-label"><?= __('Некоторые браузеры не поддерживают закрытие окон и возврат в мессенджер. Telegram поддерживает.'); ?></div>
                    </div>

                    <div class="form-group thanks-link hide">
                        <label><?= __('Ссылка кнопки'); ?></label>
                        <input type="text" value="<?= h($config['cart']['thanks_btn_link'] ?? ''); ?>" name="data[settings][cart][thanks_btn_link]" class="form-control" placeholder="https://...">
                        <div class="help-label"><?= __('Вместо закрытия окна будет переход по ссылке.'); ?></div>
                    </div>


                    <div class="form-group thanks-payment hide">
                        <label><?= __('Платежная система'); ?></label>
                        <select class="form-control select2" value="<?= h($config['cart']['payment_method'] ?? ''); ?>" name="data[settings][cart][payment_method]">
                            <option value=""><?= __('Не переходить к оплате.'); ?></option>
                            <?php if (!empty($_payments)) : foreach ($_payments as $p) : ?>
                                    <?php
                                    $clientParams = json_decode($p['client_params'], true);
                                    ?>
                                    <option value="<?= $p['id']; ?>"><?= h($p['title'] . ' / ' . ($clientParams['account'] ?? '')); ?></option>
                            <?php endforeach;
                            endif; ?>
                        </select>
                        <div class="help-label"><?= __('Если указано, после заказа сразу будет переход на оплату. Платежные системы подключаются в разделе "Интеграции".'); ?></div>
                    </div>
                </div>

                <div id="shop-styles" class="section-content collapse hide" aria-labelledby="shop-styles" data-parent="#settingsForm">
                    <div class="row mb-2">
                        <div class="col">
                            <div class="form-group">
                                <label><?= __('Фон меню'); ?></label>
                                <input type="text" value="<?= h($config['styles']['menu_bg'] ?? '#343a40'); ?>" data-default-value="#343a40" name="data[settings][styles][menu_bg]" class="form-control colorpicker" data-change='[{"s" : ".rplto-widget .navbar", "a" : "background-color", "p" : "css"}]'>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label><?= __('Ссылки меню'); ?></label>
                                <input type="text" value="<?= h($config['styles']['menu_a_color'] ?? '#b3b3b3'); ?>" data-default-value="#b3b3b3" name="data[settings][styles][menu_a_color]" class="form-control colorpicker" data-change='[{"s" : ".rplto-widget .navbar-dark .navbar-nav .nav-link", "a" : "color", "p" : "css"}]'>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label><?= __('Активные'); ?></label>
                                <input type="text" value="<?= h($config['styles']['menu_a_color_active'] ?? '#efefef'); ?>" data-default-value="#efefef" name="data[settings][styles][menu_a_color_active]" class="form-control colorpicker" data-change='[{"s" : ".rplto-widget .navbar-dark .navbar-nav .nav-link.active", "a" : "color", "p" : "css"}]'>
                            </div>
                        </div>
                    </div>



                    <div class="row mb-2">
                        <div class="col">
                            <div class="form-group">
                                <label><?= __('Фотография фона'); ?></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="data[settings][styles][page_bg_img]" id="page-page_bg_img" value="<?= h($config['styles']['page_bg_img'] ?? ''); ?>" data-change='[{"s" : ".browser .shop-page-demo", "a" : "background-image", "p" : "css"}]'>
                                    <div class="input-group-append">
                                        <div class="wf-input-tb" style="top: 5px;">
                                            <div class="wf-input-tb-action wf-input-tb-upload" data-target="#page-page_bg_img"><i class="fa fa-picture-o"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label><?= __('Растягивание'); ?></label>
                                <select class="form-control select2" value="<?= h($config['styles']['page_bg_imgsize'] ?? ''); ?>" name="data[settings][styles][page_bg_imgsize]" data-change='[{"s" : ".rplto-widget .shop-page-demo", "a" : "background-size", "p" : "css"}]'>
                                    <option value="" data-value="auto"><?= __('По умолчанию'); ?></option>
                                    <option value="cover" data-value="cover"><?= __('Обложка'); ?></option>
                                    <option value="contain" data-value="contain"><?= __('По содержанию'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col">
                            <div class="form-group">
                                <label><?= __('Фон страницы'); ?></label>
                                <input type="text" value="<?= h($config['styles']['page_bg'] ?? '#f5f5f5'); ?>" data-default-value="#f5f5f5" name="data[settings][styles][page_bg]" class="form-control colorpicker" data-change='[{"s" : ".browser .content", "a" : "background-color", "p" : "css"}]'>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label><?= __('Текст на страницах'); ?></label>
                                <input type="text" value="<?= h($config['styles']['page_text_color'] ?? '#212529'); ?>" data-default-value="#212529" name="data[settings][styles][page_text_color]" class="form-control colorpicker" data-change='[{"s" : ".browser .app-page-title, .browser .title, .cart-thanks-content, .cart-page-content", "a" : "color", "p" : "css"}]'>
                            </div>
                        </div>
                    </div>



                    <div class="row mb-2">
                        <div class="col">
                            <div class="form-group">
                                <label><?= __('Кнопки корзины'); ?></label>
                                <input type="text" value="<?= h($config['styles']['cart_btn_bg'] ?? '#28a745'); ?>" data-default-value="#28a745" name="data[settings][styles][cart_btn_bg]" class="form-control colorpicker" data-change='[{"s" : ".rplto-widget .product-item .btn-success", "a" : "background-color", "p" : "css"}, {"s" : ".rplto-widget .product-item .btn-success", "a" : "border-color", "p" : "css"}]'>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label><?= __('Цвет текста'); ?></label>
                                <input type="text" value="<?= h($config['styles']['cart_btn_text'] ?? '#ffffff'); ?>" data-default-value="#ffffff" name="data[settings][styles][cart_btn_text]" class="form-control colorpicker" data-change='[{"s" : ".rplto-widget .product-item .btn-success", "a" : "color", "p" : "css"}]'>
                            </div>
                        </div>
                    </div>



                    <div class="row mb-2">
                        <div class="col">
                            <div class="form-group">
                                <label><?= __('Кнопка заказа'); ?></label>
                                <input type="text" value="<?= h($config['styles']['cart_checkout_bg'] ?? '#28a745'); ?>" data-default-value="#28a745" name="data[settings][styles][cart_checkout_bg]" class="form-control colorpicker" data-change='[{"s" : ".rplto-widget .shop-footer-cart .btn-success, .cart-submit .btn-success", "a" : "background-color", "p" : "css"}, {"s" : ".rplto-widget .shop-footer-cart .btn-success, .cart-submit .btn-success", "a" : "border-color", "p" : "css"}]'>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label><?= __('Цвет текста'); ?></label>
                                <input type="text" value="<?= h($config['styles']['cart_checkout_text'] ?? '#ffffff'); ?>" data-default-value="#ffffff" name="data[settings][styles][cart_checkout_text]" class="form-control colorpicker" data-change='[{"s" : ".rplto-widget .shop-footer-cart .btn-success, .cart-submit .btn-success", "a" : "color", "p" : "css"}]'>
                            </div>
                        </div>
                    </div>


                    <div class="">
                        <button type="button" class="btn btn-sm btn-light widget-reset-values" data-container="#shop-styles"><?= __('Восстановить стандартные стили'); ?></button>
                    </div>


                    <div class="form-group mt-2">
                        <label><?= __('Дополнительные коды для HEAD'); ?></label>
                        <textarea name="data[settings][head][codes]" class="form-control"><?= h($config['head']['codes'] ?? ''); ?></textarea>
                    </div>
                    <div class="alert help-label alert-info">
                        <?= __('Здесь коды для текушего виджета, например, стили или скрипты. При использовании html/css/javascript работоспособность виджета не гарантируется.'); ?>
                    </div>


                </div>

                <div id="shop-cashback" class="section-content collapse hide" aria-labelledby="shop-cashback" data-parent="#settingsForm">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label><?= __('Кэшбэк для всех товаров, в %'); ?></label>
                                <div class="input-group">
                                    <input type="text" value="<?= h($config['main']['cashback'] ?? ''); ?>" name="data[settings][main][cashback]" placeholder="0" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label><?= __('Максимальный % для заказа'); ?></label>
                                <div class="input-group">
                                    <input type="text" value="<?= h($config['main']['cashback_max'] ?? ''); ?>" name="data[settings][main][cashback_max]" placeholder="100" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <input type="hidden" name="data[settings][main][cbonpayment]" value="0">
                            <div class="mt-4 custom-control custom-switch custom-control-inline custom-control-sm mr-0 mt-3 cursor-pointer">
                                <input name="data[settings][main][cbonpayment]" type="checkbox" value="1" id="product-cbonpayment" class="custom-control-input" <?php if (!empty($config['main']['cbonpayment'])) : ?>checked="checked" <?php endif ?>>
                                <label class="custom-control-label cursor-pointer" for="product-cbonpayment"><?= __('Начислять кэшбэк при оплате'); ?></label>
                            </div>
                        </div>
                        <div class="col">
                            <input type="hidden" name="data[settings][main][cashback_disabled]" value="0">
                            <div class="mt-4 custom-control custom-switch custom-control-inline custom-control-sm mr-0 mt-3 cursor-pointer">
                                <input name="data[settings][main][cashback_disabled]" type="checkbox" value="1" id="product-cashback_disabled" class="custom-control-input" <?php if (!empty($config['main']['cashback_disabled'])) : ?>checked="checked" <?php endif ?>>
                                <label class="custom-control-label cursor-pointer" for="product-cashback_disabled"><?= __('Отключить кэшбэк в магазине'); ?></label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <b><?= __('Уровни кэшбэка'); ?></b>
                    </div>
                    <div class="row g-1">
                        <div class="col-6"><?= __('Сумма заказа'); ?></div>
                        <div class="col-6"><?= __('Баллы / проценты'); ?></div>
                    </div>
                    <?php for ($i = 0; $i < 5; $i++) : ?>
                        <div class="row g-1">
                            <div class="col-6">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="data[settings][cashback][levels][<?= $i; ?>][price]" id="cashback-levels-<?= $i; ?>-price" value="<?= h($config['cashback']['levels'][$i]['price'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="data[settings][cashback][levels][<?= $i; ?>][bonus]" id="cashback-levels-<?= $i; ?>-bonus" value="<?= h($config['cashback']['levels'][$i]['bonus'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" value="1" id="cashback-levels-<?= $i; ?>-pcnt" <?= (!empty($config['cashback']['levels'][$i]['is_pcnt']) ? 'checked="checked"' : ''); ?> name="data[settings][cashback][levels][<?= $i; ?>][is_pcnt]" class="custom-control-input">
                                        <label class="custom-control-label" for="cashback-levels-<?= $i; ?>-pcnt">%</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <div id="shop-shop2" class="section-content collapse hide" aria-labelledby="shop-shop2" data-parent="#settingsForm">
                    
                    <div class="row">
                        <div class="col">
                            <input type="hidden" name="data[settings][main][is_shop2]" value="0">
                            <div class="mt-4 mb-4 custom-control custom-switch custom-control-inline custom-control-sm mr-0 mt-3 cursor-pointer">
                                <input name="data[settings][main][is_shop2]" type="checkbox" value="1" id="is-shop2" class="custom-control-input" <?php if (!empty($config['main']['is_shop2'])) : ?>checked="checked" <?php endif ?>>
                                <label class="custom-control-label cursor-pointer" for="is-shop2"><?= __('Магазин 2.0'); ?></label>
                            </div>
                        </div>
                    </div>

                    <label><?= __('Канал'); ?></label>
                    <p class="help-label"><?= __('Рекомендуемая ширина фото - до 60px.'); ?></p>

                    <div class="row g-1 mb-4">

                        <div class="col-1">
                            <div class="shop-slider-photo-demo photo-shop2-demo-0"><img src="<?= h($config['shop2']['channel']['photo'] ?? ''); ?>" alt=""></div>
                        </div>
                        <div class="col-5">
                            <div class="input-group">
                                <input type="text" class="form-control" name="data[settings][shop2][channel][photo]" id="channel-photo" value="<?= h($config['shop2']['channel']['photo'] ?? ''); ?>" data-keyup='[{"s" : ".photo-shop2-demo-0 img", "a" : "src", "p" : "attr"}, {"s" : ".shop-page-demo-shop2 .shop2-avatar .shop2-timg img", "a" : "src", "p" : "attr"}]'>
                                <div class="input-group-append">
                                    <div class="wf-input-tb" style="top: 5px;">
                                        <div class="wf-input-tb-action wf-input-tb-upload" data-target="#channel-photo"><i class="fa fa-picture-o"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="input-group">
                                <input type="text" class="form-control" name="data[settings][shop2][channel][href]" id="channel-href" value="<?= h($config['shop2']['channel']['href'] ?? ''); ?>" placeholder="Ссылка">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label><?= __('Заголовок'); ?></label>
                                <input type="text" value="<?= h($config['shop2']['channel']['title'] ?? ''); ?>" name="data[settings][shop2][channel][title]" placeholder="<?= __('@telegram') ?>" class="form-control shop2-channel-title">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label><?= __('Описание'); ?></label>
                                <input type="text" value="<?= h($config['shop2']['channel']['description'] ?? ''); ?>" name="data[settings][shop2][channel][description]" placeholder="<?= __('наш Telegram-канал') ?>" class="form-control shop2-channel-description">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label><?= __('Фон'); ?></label>
                                <input type="text" value="<?= h($config['styles']['shop2_channel_bg'] ?? '#efefef'); ?>" data-default-value="#efefef" name="data[settings][styles][shop2_channel_bg]" class="form-control colorpicker" data-change='[{"s" : ".rplto-widget .shop-page-demo .shop2-avatar .shop2-channel", "a" : "background-color", "p" : "css"}]'>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label><?= __('Текста'); ?></label>
                                <input type="text" value="<?= h($config['styles']['shop2_channel_color'] ?? 'inherit'); ?>" data-default-value="inherit" name="data[settings][styles][shop2_channel_color]" class="form-control colorpicker" data-change='[{"s" : ".rplto-widget .shop-page-demo .shop2-avatar .shop2-channel", "a" : "color", "p" : "css"}]'>
                            </div>
                        </div>
                    </div>

                    <label><?= __('Лента'); ?></label>
                    <p class="help-label"><?= __('Рекомендуемая соотношения сторон 1x1.'); ?></p>

                    <div class="row g-1 mb-4">
                        <?php for($i = 1; $i <= 15; $i++): ?>
                            <div class="col-1">
                                <div class="shop2-story-photo-demo-<?=$i?>"><img src="<?= h($config['shop2']['story'][$i]['photo'] ?? ''); ?>" alt=""></div>
                            </div>
                            <div class="col-3">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="data[settings][shop2][story][<?=$i?>][photo]" id="story-photo-<?=$i?>" value="<?= h($config['shop2']['story'][$i]['photo'] ?? ''); ?>" data-keyup='[{"s" : ".shop2-story-photo-demo-<?=$i?> img", "a" : "src", "p" : "attr"}, {"s" : ".shop-2-story-item-<?=$i?> img", "a": "src", "p" : "attr"}]'>
                                    <div class="input-group-append">
                                        <div class="wf-input-tb" style="top: 5px;">
                                            <div class="wf-input-tb-action wf-input-tb-upload" data-target="#story-photo-<?=$i?>"><i class="fa fa-picture-o"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <select class="form-control select2 shop-app-menu-items" value="<?= h($config['shop2']['story'][$i]['ref'] ?? ''); ?>" name="data[settings][shop2][story][<?=$i?>][ref]">
                                    <option value=""></option>
                                    <optgroup label="<?= __('Категории'); ?>">
                                        <?= $categoriesTree; ?>
                                    </optgroup>
                                    <optgroup label="<?= __('Товары'); ?>">
                                        <?php foreach ($products as $pr) : ?>
                                            <option value="p<?= $pr['id']; ?>" data-title="<?= hst($pr['title']); ?>"><?= $pr['title']; ?> #<?= $pr['sku']; ?></option>
                                        <?php endforeach ?>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="data[settings][shop2][story][<?=$i?>][href]" id="story-href-<?=$i?>" value="<?= h($config['shop2']['story'][$i]['href'] ?? ''); ?>" placeholder="Ссылка">
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <label><?= __('Баллы'); ?></label>
                    <p class="help-label"><?= __('Рекомендуемая соотношения сторон 4x3.'); ?></p>

                    <div class="row g-1 mb-4">
                        <div class="col-1">
                            <div class="shop2-points-photo-demo"><img src="<?= h($config['shop2']['points']['photo'] ?? ''); ?>" alt=""></div>
                        </div>
                        <div class="col-5">
                            <div class="input-group">
                                <input type="text" class="form-control" name="data[settings][shop2][points][photo]" id="points-photo" value="<?= h($config['shop2']['points']['photo'] ?? ''); ?>" data-keyup='[{"s" : ".shop2-points-photo-demo img", "a" : "src", "p" : "attr"}, {"s" : ".shop2-buttons .shop2-points", "a" : "background-image", "p": "css"}]'>
                                <div class="input-group-append">
                                    <div class="wf-input-tb" style="top: 5px;">
                                        <div class="wf-input-tb-action wf-input-tb-upload" data-target="#points-photo"><i class="fa fa-picture-o"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="input-group">
                                <input type="text" class="form-control" name="data[settings][shop2][points][href]" id="points-href" value="<?= h($config['shop2']['points']['href'] ?? ''); ?>" placeholder="Ссылка">
                            </div>
                        </div>

                        <div class="col-4">
                            <label><?= __('Текст'); ?></label>
                            <input type="text" class="form-control" name="data[settings][shop2][points][title]" id="points-title" value="<?= h($config['shop2']['points']['title'] ?? 'Баллы'); ?>" placeholder="Баллы">
                        </div>
                        <div class="col-4">
                            <label><?= __('Валюта'); ?></label>
                            <select class="form-control" name="data[settings][shop2][points][currency]" id="points-title" placeholder="Баллы">
                                <?php foreach (['₽', '$', '€', 'Б'] as $currency): ?>
                                    <option <?= h($config['shop2']['points']['currency'] ?? '')==$currency?'selected':''; ?>><?=$currency?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label><?= __('Текста'); ?></label>
                                <input type="text" value="<?= h($config['styles']['shop2_points_color'] ?? 'inherit'); ?>" data-default-value="inherit" name="data[settings][styles][shop2_points_color]" class="form-control colorpicker" data-change='[{"s" : ".rplto-widget .shop-page-demo .shop2-buttons .shop2-points", "a" : "color", "p" : "css"}]'>
                            </div>
                        </div>
                    </div>

                    <label><?= __('Блок 1'); ?></label>
                    <p class="help-label"><?= __('Рекомендуемая соотношения сторон 4x3.'); ?></p>

                    <div class="row g-1 mb-4">
                        <div class="col-1">
                            <div class="shop2-referal-photo-demo"><img src="<?= h($config['shop2']['referal']['photo'] ?? ''); ?>" alt=""></div>
                        </div>
                        <div class="col-5">
                            <div class="input-group">
                                <input type="text" class="form-control" name="data[settings][shop2][referal][photo]" id="referal-photo" value="<?= h($config['shop2']['referal']['photo'] ?? ''); ?>" data-keyup='[{"s" : ".shop2-referal-photo-demo img", "a" : "src", "p" : "attr"}, {"s" : ".shop2-referal", "a" : "background-image", "p": "css"}]'>
                                <div class="input-group-append">
                                    <div class="wf-input-tb" style="top: 5px;">
                                        <div class="wf-input-tb-action wf-input-tb-upload" data-target="#referal-photo"><i class="fa fa-picture-o"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="input-group">
                                <input type="text" class="form-control" name="data[settings][shop2][invite][href]" id="invite-href" value="<?= h($config['shop2']['invite']['href'] ?? ''); ?>" placeholder="Ссылка">
                            </div>
                        </div>
                    </div>

                    <label><?= __('Блок 2'); ?></label>
                    <p class="help-label"><?= __('Рекомендуемая соотношения сторон 3x4.'); ?></p>

                    <div class="row g-1 mb-4">
                        <div class="col-1">
                            <div class="shop2-order-photo-demo"><img src="<?= h($config['shop2']['order']['photo'] ?? ''); ?>" alt=""></div>
                        </div>
                        <div class="col-5">
                            <div class="input-group">
                                <input type="text" class="form-control" name="data[settings][shop2][order][photo]" id="order-photo" value="<?= h($config['shop2']['order']['photo'] ?? ''); ?>" data-keyup='[{"s" : ".shop2-order-photo-demo img", "a" : "src", "p" : "attr"}, {"s" : ".shop2-order", "a" : "background-image", "p" : "css"}]'>
                                <div class="input-group-append">
                                    <div class="wf-input-tb" style="top: 5px;">
                                        <div class="wf-input-tb-action wf-input-tb-upload" data-target="#order-photo"><i class="fa fa-picture-o"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="input-group">
                                <input type="text" class="form-control" name="data[settings][shop2][order][href]" id="order-href" value="<?= h($config['shop2']['order']['href'] ?? ''); ?>" placeholder="Ссылка">
                            </div>
                        </div>
                    </div>

                    <label><?= __('Баннер'); ?></label>
                    <p class="help-label"></p>

                    <div class="row g-1 mb-4">
                        <div class="col-1">
                            <div class="shop2-calculator-photo-demo"><img src="<?= h($config['shop2']['calculator']['photo'] ?? ''); ?>" alt=""></div>
                        </div>
                        <div class="col-5">
                            <div class="input-group">
                                <input type="text" class="form-control" name="data[settings][shop2][calculator][photo]" id="calculator-photo" value="<?= h($config['shop2']['calculator']['photo'] ?? ''); ?>" data-keyup='[{"s" : ".shop2-calculator-photo-demo img", "a" : "src", "p" : "attr"}, {"s" : ".shop2-calculator img", "a" : "src", "p" : "attr"}]'>
                                <div class="input-group-append">
                                    <div class="wf-input-tb" style="top: 5px;">
                                        <div class="wf-input-tb-action wf-input-tb-upload" data-target="#calculator-photo"><i class="fa fa-picture-o"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="input-group">
                                <input type="text" class="form-control" name="data[settings][shop2][calculator][href]" id="calculator-href" value="<?= h($config['shop2']['calculator']['href'] ?? ''); ?>" placeholder="Ссылка">
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <div class="my-4">
                <button type="submit" class="btn btn-success"><?= __('Сохранить'); ?></button>
                <div class="pull-right"><a href="/apps/shop?shop_id=<?= $currentShop['hash']; ?>" class="btn btn-light"><?= __('Выйти'); ?></a></div>
            </div>

        </form>





    </div>

    <div class="col-md-5 offset-md-1 widget-preview">


        <div class="browser browser-sm">
            <?php if (isset($config['main']['is_shop2']) && $config['main']['is_shop2']): ?>
                <h6>Магазин 2.0</h6>
            <?php endif; ?>
            <div class="header"><b class="dots"><b></b><b></b><b></b></b></div>
            <div class="content" style="background: #f5f5f5;">

                <div class="rplto-widget">

                    <?php if (!isset($config['main']['is_shop2']) || (isset($config['main']['is_shop2']) && !$config['main']['is_shop2'])): ?>
                        <nav class="px-0 navbar navbar-dark" aria-label="">
                            <div class="container-fluid">

                                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample01" aria-controls="navbarsExample01" aria-expanded="false" aria-label="Toggle navigation">
                                    <span class="navbar-toggler-icon"></span> <span class="menu-label-text"></span>
                                </button>

                                <ul class="navbar-nav horizontal-nav">
                                    <!-- <li class="nav-item"><a class="nav-link" href="#">Элемент 1</a></li> -->

                                </ul>



                                <div class="collapse navbar-collapse" id="navbarsExample01">
                                    <ul class="navbar-nav me-auto mb-2">
                                        <li class="nav-item">
                                            <a class="nav-link active" aria-current="page" href="#">Текущая категория 1</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#">Категория 2</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#">Категория 3</a>
                                        </li>
                                    </ul>

                                </div>
                            </div>
                        </nav>
                    <?php endif; ?>

                    <div class="shop-page-demo shop-page-demo-index">


                        <div class="px-3 pt-3 slider-view-slider">
                            <div class="carousel-image"><img src="" class="d-block w-100 rounded" alt=""></div>
                        </div>

                        <div class="slider-view-banners">

                            <div class="px-3 pt-3 slider-view-banners-1">
                                <div class="carousel-image"><img src="" class="d-block w-100 rounded" alt=""></div>
                                <div class="carousel-image mt-3"><img src="" class="d-block w-100 rounded" alt=""></div>
                                <div class="carousel-image mt-3"><img src="" class="d-block w-100 rounded" alt=""></div>
                            </div>

                            <div class="px-3 pt-3 slider-view-banners-2">
                                <div class="row px-1">
                                    <div class="col px-2">
                                        <div class="carousel-image"><img src="" class="d-block w-100 rounded" alt=""></div>
                                    </div>
                                    <div class="col px-2">
                                        <div class="carousel-image"><img src="" class="d-block w-100 rounded" alt=""></div>
                                    </div>
                                </div>
                                <div class="row px-1 mt-3">
                                    <div class="col px-2">
                                        <div class="carousel-image"><img src="" class="d-block w-100 rounded" alt=""></div>
                                    </div>
                                    <div class="col px-2">
                                        <div class="carousel-image"><img src="" class="d-block w-100 rounded" alt=""></div>
                                    </div>
                                </div>
                                <div class="row px-1 mt-3">
                                    <div class="col px-2">
                                        <div class="carousel-image"><img src="" class="d-block w-100 rounded" alt=""></div>
                                    </div>
                                    <div class="col px-2">
                                        <div class="carousel-image"><img src="" class="d-block w-100 rounded" alt=""></div>
                                    </div>
                                </div>
                            </div>

                            <div class="px-3 pt-3 slider-view-banners-3">
                                <div class="row px-1">
                                    <div class="col px-2">
                                        <div class="carousel-image"><img src="" class="d-block w-100 rounded" alt=""></div>
                                    </div>
                                    <div class="col px-2">
                                        <div class="carousel-image"><img src="" class="d-block w-100 rounded" alt=""></div>
                                    </div>
                                    <div class="col px-2">
                                        <div class="carousel-image"><img src="" class="d-block w-100 rounded" alt=""></div>
                                    </div>
                                </div>
                                <div class="row px-1 mt-3">
                                    <div class="col px-2">
                                        <div class="carousel-image"><img src="" class="d-block w-100 rounded" alt=""></div>
                                    </div>
                                    <div class="col px-2">
                                        <div class="carousel-image"><img src="" class="d-block w-100 rounded" alt=""></div>
                                    </div>
                                    <div class="col px-2">
                                        <div class="carousel-image"><img src="" class="d-block w-100 rounded" alt=""></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="view-grid3 px-3 pb-3">
                            <div class="home_cats_demo">
                                <div class="container-fluid">

                                    <div class="row">
                                        <div class="col-sm-4 product-item category-item text-center">
                                            <div class="product-photo"><img src="/assets/services/shop.svg" width="70" height="70"></div>

                                            <div class="help-label mt-2 text-dark item-title"><span>Название категории 1</span></div>
                                        </div>

                                        <div class="col-sm-4 product-item category-item text-center">
                                            <div class="product-photo"><img src="/assets/services/shop.svg" width="70" height="70"></div>

                                            <div class="help-label mt-2 text-dark item-title"><span>Категория 2</span></div>
                                        </div>

                                        <div class="col-sm-4 product-item category-item text-center">
                                            <div class="product-photo"><img src="/assets/services/shop.svg" width="70" height="70"></div>

                                            <div class="help-label mt-2 text-dark item-title"><span>Третья категория в каталоге</span></div>
                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-sm-4 product-item category-item text-center">
                                            <div class="product-photo"><img src="/assets/services/shop.svg" width="70" height="70"></div>

                                            <div class="help-label mt-2 text-dark item-title"><span>Категория 4</span></div>
                                        </div>

                                        <div class="col-sm-4 product-item category-item text-center">
                                            <div class="product-photo"><img src="/assets/services/shop.svg" width="70" height="70"></div>

                                            <div class="help-label mt-2 text-dark item-title"><span>Название категории пять</span></div>
                                        </div>

                                        <div class="col-sm-4 product-item category-item text-center">
                                            <div class="product-photo"><img src="/assets/services/shop.svg" width="70" height="70"></div>

                                            <div class="help-label mt-2 text-dark item-title"><span>6-ая категория в каталоге</span></div>
                                        </div>
                                    </div>




                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="shop-page-demo shop-page-demo-catalog hidden">

                        <div class="app-page-title pt-3 px-3 font-weight-bold">Заголовок страницы</div>

                        <div class="view-list p-3 pb-0">
                            <div class="container-fluid">

                                <div class="row product-item item-product">
                                    <div class="col-sm-3">
                                        <div class="product-photo"><img src="/assets/services/shop.svg" width="70" height="70"></div>
                                    </div>
                                    <div class="col-sm-9">
                                        <div class="product-summ">
                                            <div><strong>Название первого товара</strong></div>
                                            <div class="small">Краткое описание первого товара</div>
                                            <div class="small">
                                                <p class="m-0"><input type="radio" name="pv1" checked="checked"> Вариант 1 &mdash; <span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span></p>
                                                <p class="m-0"><input type="radio" name="pv1"> Второй вариант товара &mdash; <span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span></p>
                                            </div>
                                            <div class="d-flex align-items-end">
                                                <div class="price mr-2"><span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span></div>
                                                <div class="btn btn-xs mt-2 btn-success product-cart-label">В корзину</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row product-item item-product">
                                    <div class="col-sm-3">
                                        <div class="product-photo"><img src="/assets/services/shop.svg" width="70" height="70"></div>
                                    </div>
                                    <div class="col-sm-9">
                                        <div class="product-summ">
                                            <div><strong>Название второго товара</strong></div>
                                            <div class="small">Краткое описание второго товара</div>
                                            <div class="d-flex align-items-end">
                                                <div class="price mr-2"><span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span></div>
                                                <div class="btn btn-xs mt-2 btn-success product-cart-label">В корзину</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row product-item item-product">
                                    <div class="col-sm-3">
                                        <div class="product-photo"><img src="/assets/services/shop.svg" width="70" height="70"></div>
                                    </div>
                                    <div class="col-sm-9">
                                        <div class="product-summ">
                                            <div><strong>Название третьего товара</strong></div>
                                            <div class="small">Краткое описание третьего товара</div>
                                            <div class="d-flex align-items-end">
                                                <div class="price mr-2"><span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span></div>
                                                <div class="btn btn-xs mt-2 btn-success product-cart-label">В корзину</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>


                        <div class="view-grid3 px-3 pb-3">
                            <div class="container-fluid">

                                <div class="row">
                                    <div class="col-sm-4 product-item item-product text-center">
                                        <div class="product-photo"><img src="/assets/services/shop.svg" width="70" height="70"></div>

                                        <div class="product-summ">
                                            <div class="help-label mt-2 text-dark">Название первого товара</div>

                                            <div class="price mt-1"><span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span></div>
                                            <div class="btn btn-xs mt-2 btn-success product-cart-label">В корзину</div>
                                        </div>
                                    </div>

                                    <div class="col-sm-4 product-item item-product text-center">
                                        <div class="product-photo"><img src="/assets/services/shop.svg" width="70" height="70"></div>

                                        <div class="product-summ">
                                            <div class="help-label mt-2 text-dark">Название первого товара</div>

                                            <div class="price mt-1"><span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span></div>
                                            <div class="btn btn-xs mt-2 btn-success product-cart-label">В корзину</div>
                                        </div>
                                    </div>

                                    <div class="col-sm-4 product-item item-product text-center">
                                        <div class="product-photo"><img src="/assets/services/shop.svg" width="70" height="70"></div>

                                        <div class="product-summ">
                                            <div class="help-label mt-2 text-dark">Название первого товара</div>

                                            <div class="price mt-1"><span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span></div>
                                            <div class="btn btn-xs mt-2 btn-success product-cart-label">В корзину</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-4 product-item item-product text-center">
                                        <div class="product-photo"><img src="/assets/services/shop.svg" width="70" height="70"></div>

                                        <div class="product-summ">
                                            <div class="help-label mt-2 text-dark">Название первого товара</div>

                                            <div class="price mt-1"><span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span></div>
                                            <div class="btn btn-xs mt-2 btn-success product-cart-label">В корзину</div>
                                        </div>
                                    </div>

                                    <div class="col-sm-4 product-item item-product text-center">
                                        <div class="product-photo"><img src="/assets/services/shop.svg" width="70" height="70"></div>

                                        <div class="product-summ">
                                            <div class="help-label mt-2 text-dark">Название первого товара</div>

                                            <div class="price mt-1"><span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span></div>
                                            <div class="btn btn-xs mt-2 btn-success product-cart-label">В корзину</div>
                                        </div>
                                    </div>

                                    <div class="col-sm-4 product-item item-product text-center">
                                        <div class="product-photo"><img src="/assets/services/shop.svg" width="70" height="70"></div>

                                        <div class="product-summ">
                                            <div class="help-label mt-2 text-dark">Название первого товара</div>

                                            <div class="price mt-1"><span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span></div>
                                            <div class="btn btn-xs mt-2 btn-success product-cart-label">В корзину</div>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>

                        <div class="view-grid2 px-3 pb-3">
                            <div class="container-fluid">

                                <div class="row">
                                    <div class="col-sm-6 product-item item-product text-center">
                                        <div class="product-photo"><img src="/assets/services/shop.svg" width="140" height="140"></div>

                                        <div class="product-summ">
                                            <div class="mt-2 text-dark">Название первого товара</div>

                                            <div class="price mt-1"><span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span></div>
                                            <div class="btn btn-xs mt-2 btn-success product-cart-label">В корзину</div>
                                        </div>
                                    </div>

                                    <div class="col-sm-6 product-item item-product text-center">
                                        <div class="product-photo"><img src="/assets/services/shop.svg" width="140" height="140"></div>

                                        <div class="product-summ">
                                            <div class="mt-2 text-dark">Название первого товара</div>

                                            <div class="price mt-1"><span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span></div>
                                            <div class="btn btn-xs mt-2 btn-success product-cart-label">В корзину</div>
                                        </div>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-sm-6 product-item item-product text-center">
                                        <div class="product-photo"><img src="/assets/services/shop.svg" width="140" height="140"></div>

                                        <div class="product-summ">
                                            <div class="mt-2 text-dark">Название первого товара</div>

                                            <div class="price mt-1"><span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span></div>
                                            <div class="btn btn-xs mt-2 btn-success product-cart-label">В корзину</div>
                                        </div>
                                    </div>

                                    <div class="col-sm-6 product-item item-product text-center">
                                        <div class="product-photo"><img src="/assets/services/shop.svg" width="140" height="140"></div>

                                        <div class="product-summ">
                                            <div class="mt-2 text-dark">Название первого товара</div>

                                            <div class="price mt-1"><span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span></div>
                                            <div class="btn btn-xs mt-2 btn-success product-cart-label">В корзину</div>
                                        </div>
                                    </div>

                                </div>


                            </div>
                        </div>

                        <div class="shop-footer-cart">
                            <a href="#" class="btn btn-success br-0">
                                <span class="price"><span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span> &mdash; </span>

                                <span class="shop-footer-cart-label"><?= __('Оформить заказ'); ?></span>
                            </a>
                        </div>

                    </div>

                    <div class="shop-page-demo shop-page-demo-cart hidden">

                        <div class="title pt-3 px-3 cart-table-title font-weight-bold">Ваш заказ</div>
                        <div class="px-3 small cart-page-content">Сопроводительный текст к форме заказа.</div>

                        <div class="cart-panel">
                            <div class="settings_cart_mode_cart">

                                <table class="table table-stripped cart-table">
                                    <tr class="cart-product">
                                        <td class="cart-photo">
                                            <div class="product-photo"><img src="/assets/services/shop.svg" width="50" height="50"></div>
                                        </td>
                                        <td class="cart-name">
                                            <div class="text-sm ">Название первого товара</div>
                                            <div class="small">Краткое описание первого товара</div>
                                        </td>
                                        <td class="cart-count">
                                            <div class="cart-count-change">
                                                <a class="btn btn-sm btn-warning"><i class="la la-minus"></i></a>
                                                <span class="cart-count-product">1</span>
                                                <a class="btn btn-sm btn-success"><i class="la la-plus"></i></a>
                                            </div>
                                        </td>
                                        <td class="cart-price">
                                            <span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span>
                                        </td>
                                        <td class="cart-delete">
                                            <a class="btn btn-sm btn-danger"><i class="la la-times"></i></a>
                                        </td>
                                    </tr>
                                    <tr class="cart-product">
                                        <td class="cart-photo">
                                            <div class="product-photo"><img src="/assets/services/shop.svg" width="50" height="50"></div>
                                        </td>
                                        <td class="cart-name">
                                            <div class="text-sm ">Название первого товара</div>
                                            <div class="small">Краткое описание первого товара</div>
                                        </td>
                                        <td class="cart-count">
                                            <div class="cart-count-change">
                                                <a class="btn btn-sm btn-warning"><i class="la la-minus"></i></a>
                                                <span class="cart-count-product">1</span>
                                                <a class="btn btn-sm btn-success"><i class="la la-plus"></i></a>
                                            </div>
                                        </td>
                                        <td class="cart-price">
                                            <span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span>
                                        </td>
                                        <td class="cart-delete">
                                            <a class="btn btn-sm btn-danger"><i class="la la-times"></i></a>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div class="settings_cart_mode_one">

                                <table class="table table-stripped cart-table">
                                    <tr class="cart-product">
                                        <td class="cart-photo">
                                            <div class="product-photo"><img src="/assets/services/shop.svg" width="50" height="50"></div>
                                        </td>
                                        <td class="cart-name">
                                            <div class="text-sm ">Название первого товара</div>
                                            <div class="small">Краткое описание первого товара</div>
                                        </td>
                                        <td class="cart-price">
                                            <span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>



                        </div>


                        <div class="settings_not_imcheckout">
                            <div class="title pt-3 px-3 cart-order-title-wrap"><b class="cart-order-title">Оформить заказ</b></div>

                            <div class="cart-panel cart-order">
                                <div class="form-group cart-form-name settings_require_name_checked">
                                    <label><?= __('Ваше имя:'); ?></label>
                                    <input type="text" value="" class="form-control">
                                </div>
                                <div class="form-group cart-form-phone settings_require_phone_checked">
                                    <label><?= __('Телефон:'); ?></label>
                                    <input type="text" value="" class="form-control">
                                </div>
                                <div class="form-group cart-form-email settings_require_email_checked">
                                    <label><?= __('E-mail:'); ?></label>
                                    <input type="text" value="" class="form-control">
                                </div>
                                <div class="form-group cart-form-comment settings_disabled_comments">
                                    <label class="cart-form-comment-label"><?= __('Комментарий к заказу:'); ?></label>
                                    <textarea value="" class="form-control"></textarea>
                                    <p class="mt-1 help-label cart-form-comment-help"><?= __('Дополнительные пожелания к заказу'); ?></p>
                                </div>

                                <div class="cart-submit">
                                    <button class="btn btn-success cart-submit-label w-100"><?= __('Заказать'); ?></button>
                                </div>
                            </div>

                        </div>
                        <div class="settings_imcheckout m-3">
                            <div class="help-label alert alert-info"><?= __('Будет автоматическое перенаправление на оплату, если выбрана платежная система для магазина. Если платежи не подключены в разделе "Спасибо", то заказ также создастся автоматически, но вместо перехода на оплату будет страница "Спасибо".'); ?></div>
                        </div>



                    </div>

                    <div class="shop-page-demo shop-page-demo-thanks hidden text-center">

                        <div class="title pt-3 px-3 mb-2 cart-thanks-title font-weight-bold">Ваш заказ</div>
                        <div class="px-3 small mb-2 cart-thanks-content">Сопроводительный текст к форме заказа.</div>

                        <div class="">
                            <button class="btn btn-success cart-thanks-btn-label"></button>
                        </div>

                        <div class="mt-5"></div>
                    </div>

                    <div class="shop-page-demo shop-page-demo-shop2 hidden px-3 pt-3">
                        <div class="shop2-avatar">
                            <div class="shop2-user">
                                <div class="shop2-uimg">
                                    <i class="fa fa-user"></i>
                                </div>
                                <div>
                                    <div class="shop2-uname">Имя <i class="fa fa-angle-right"></i></div>
                                    <div class="shop2-ulevel">Новичок</div>
                                </div>
                            </div>
                            <a class="shop2-channel" href="<?=h($config['shop2']['channel']['href']);?>" style="background-color: <?= h($config['styles']['shop2_channel_bg'] ?? '#efefef'); ?>; color: <?= h($config['styles']['shop2_channel_color'] ?? 'inherit'); ?>;">
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

                        <div class="shop2-stories">
                            <?php if (isset($config['shop2']['story'])): ?>
                                <?php foreach ($config['shop2']['story'] as $key => $story): ?>
                                    <?php /*if ($story['photo']==''){ continue; }*/ ?>
                                    <a class="shop2-story-item shop-2-story-item-<?=$key+1?>" href="<?=$story['href']?>"> <!-- saw -->
                                        <img class="shop2-story-bg" src="<?=(isset($story['photo'])?h($story['photo']):'/assets/services/shop2/w1.jpg')?>" width="100%" height="100%" />
                                        <!--div class="shop2-story-title">Демо текст</div--> 
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="slider-view-banners">

                            <div class="slider-view-banners-1">
                                <div class="carousel-image"><img src="" class="d-block w-100 rounded" alt=""></div>
                                <div class="carousel-image mt-3"><img src="" class="d-block w-100 rounded" alt=""></div>
                                <div class="carousel-image mt-3"><img src="" class="d-block w-100 rounded" alt=""></div>
                            </div>

                        </div>
                        
                        <div class="shop2-buttons mt-2">
                            <div class="row">
                                <div class="col-6">
                                    <a href="<?=($config['shop2']['points']['href'] ?? '')?>" class="shop2-points" style="background-image: url(<?=h($config['shop2']['points']['photo'] ?? '') ?>); color: <?= h($config['styles']['shop2_points_color'] ?? 'inherit'); ?>;">
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
                                    <a href="<?=($config['shop2']['referal']['href'] ?? '')?>" class="shop2-referal" style="background-image: url(<?=h($config['shop2']['referal']['photo'] ?? '') ?>);">
                                        <?php if (($config['shop2']['referal']['photo'] ?? '') == ''): ?>
                                            Блок 1
                                        <?php endif; ?>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="<?=($config['shop2']['order']['href'] ?? '')?>" class="shop2-order" style="background-image: url(<?=h($config['shop2']['order']['photo'] ?? '') ?>);">
                                        <?php if (($config['shop2']['order']['photo'] ?? '') == ''): ?>
                                            Блок 2
                                        <?php endif; ?>
                                    </a>
                                </div>
                                <div class="col-12 mt-2">
                                    <a href="<?=($config['shop2']['calculator']['href'] ?? '')?>" class="shop2-calculator">
                                        <img src="<?=h($config['shop2']['calculator']['photo'] ?? '') ?>" />
                                        <?php if (($config['shop2']['calculator']['photo'] ?? '') == ''): ?>
                                            Баннер
                                        <?php endif; ?>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="shop2-products">
                            <div class="app-page-title pt-3 px-3 font-weight-bold">Заголовок страницы</div>

                            <div class="view-grid2 px-3 pb-3">
                                <div class="container-fluid">

                                    <div class="row">
                                        <div class="col-sm-6 product-item item-product text-center">
                                            <div class="product-photo"><img src="/assets/services/shop.svg" width="140" height="140"></div>

                                            <div class="product-summ">
                                                <div class="mt-2 text-dark">Название первого товара</div>

                                                <div class="price mt-1"><span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span></div>
                                                <div class="btn btn-xs mt-2 btn-success product-cart-label">В корзину</div>
                                            </div>
                                        </div>

                                        <div class="col-sm-6 product-item item-product text-center">
                                            <div class="product-photo"><img src="/assets/services/shop.svg" width="140" height="140"></div>

                                            <div class="product-summ">
                                                <div class="mt-2 text-dark">Название первого товара</div>

                                                <div class="price mt-1"><span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span></div>
                                                <div class="btn btn-xs mt-2 btn-success product-cart-label">В корзину</div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6 product-item item-product text-center">
                                            <div class="product-photo"><img src="/assets/services/shop.svg" width="140" height="140"></div>

                                            <div class="product-summ">
                                                <div class="mt-2 text-dark">Название первого товара</div>

                                                <div class="price mt-1"><span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span></div>
                                                <div class="btn btn-xs mt-2 btn-success product-cart-label">В корзину</div>
                                            </div>
                                        </div>

                                        <div class="col-sm-6 product-item item-product text-center">
                                            <div class="product-photo"><img src="/assets/services/shop.svg" width="140" height="140"></div>

                                            <div class="product-summ">
                                                <div class="mt-2 text-dark">Название первого товара</div>

                                                <div class="price mt-1"><span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span></div>
                                                <div class="btn btn-xs mt-2 btn-success product-cart-label">В корзину</div>
                                            </div>
                                        </div>

                                    </div>


                                </div>
                            </div>

                            <div class="shop-footer-cart">
                                <a href="#" class="btn btn-success br-0">
                                    <span class="price"><span class="price-label price-label-left"></span> <b class="price-value">1000</b> <span class="price-label price-label-right">руб.</span> &mdash; </span>

                                    <span class="shop-footer-cart-label"><?= __('Оформить заказ'); ?></span>
                                </a>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>


    </div>
</div>






<?= $this->endSection() ?>