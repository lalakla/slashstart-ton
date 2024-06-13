<?php
	$url = SERVICE_URL . '?utm_source=' . HOST_CURRENT .'&utm_campaign=widget&utm_content=' . ($from ?? '');
?>
<div class="microlp-copy">
	<div class="logo"><a href="<?= $url; ?>" target="_blank"><img src="<?= STATIC_SERVER_URL; ?>assets/images/logo-dark.png"></a></div>
	<div class="text">
		<?= str_replace(['{url}', '{name}'], [$url, PROJECT_NAME], __('app.copy_widgets_label', 'Powered by <a href="{url}" target="_blank">{name}</a>')); ?>
	</div>
</div>