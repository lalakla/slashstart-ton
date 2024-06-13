<p><?= __('Создайте чат-бота в <a href="https://t.me/botfather" target="_blank">@botfather</a> и добавьте его <u>администратором</u> в свой чат или группу.'); ?></p>

<div class="form-group">
	<label for="telegram-token" class="form-label"><b><?= __('Токен бота (Token):'); ?></b></label>
	<input id="telegram-token" class="form-control" type="text" name="data[token][token]" value="<?= $service['token'] ?? ''; ?>" required="required" placeholder="123456789:AbcdeFGh....">
	<span class="help-label"><?= __('Укажите здесь токен, который вы получили при создании бота.'); ?></span>
</div>


