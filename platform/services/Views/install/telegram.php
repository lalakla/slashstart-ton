<iframe width="466" height="262" src="https://www.youtube.com/embed/uGw3YysYYOQ" title="YouTube video player"
        frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        allowfullscreen></iframe>

<p class="help-label">
    Для регистрации нового бота, перейдите по ссылке <a href="https://t.me/BotFather" target="_blank">https://t.me/BotFather</a><br>
    — запустите бот<br>
    — введите команду /newbot<br>
    — впишите никнейм бота и название<br>
    — скопируйте токен и вставьте в поле ниже
</p>

<div class="form-group">
    <label for="telegram-token" class="form-label"><b><?= __('Токен бота (Token):'); ?></b></label>
    <input id="telegram-token" class="form-control" type="text" name="data[token][token]" required="required"
           placeholder="123456789:AbcdeFGh....">
    <span class="help-label"><?= __('Укажите здесь токен, который вы получили при создании бота.'); ?></span>
</div>


<script type="text/javascript">
    $('.service-help-link').attr('href', 'https://a.app.slashstart.ru/dashboard/masters?section=telegram').show();
</script>
