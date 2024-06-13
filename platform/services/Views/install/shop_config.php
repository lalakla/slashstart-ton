<div class="form-group">
	<label for="shop-account" class="form-label"><b><?= __('Название магазина'); ?></b></label>
	<input id="shop-account" value="<?= $service['client']['account'] ?? ''; ?>" class="form-control" type="text" name="data[client][account]" required="required" placeholder="">
	<div class="help-label"><?= __('В верхнем меню раздела Магазина по названию вы увидите, какой магазин редактируете.'); ?></div>
</div>


<div class="alert alert-info">
	[app name="shop" id="<?= $service['hash']; ?>"]
</div>
<p><?= __('Используйте этот шорткод в ссылках и кнопках при редактировании Процессов, чтобы запускать приложение.'); ?></p>
<b><?= __('Для каждого приложения ID в шорткоде отличается'); ?></b>
<p><?= __('Так в Процессах можно открывать разные приложения, в зависимости от вашего сценария.'); ?></p>
<p><?= __('В Телеграме приложения открываются в формате виджета. В остальных мессенджерах - во внутреннем браузере.'); ?></p>