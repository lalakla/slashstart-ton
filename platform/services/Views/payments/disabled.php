<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="noindex, nofollow">
	<meta name="robots" content="noarchive">


	<title></title>

	<?php if (!empty($settings['favicon'])): ?>
		<link href="<?= $settings['favicon']; ?>" rel="shortcut icon">
	<?php else: ?>
		<link href="<?= defined('STATIC_SERVER_URL') ? STATIC_SERVER_URL : '/'; ?>assets/site/images/icons/favicon.png" rel="shortcut icon">
	<?php endif; ?>


	<link rel="stylesheet" href="/assets/intl-tel/css/intlTelInput.min.css">
	<link rel="stylesheet" href="/assets/css/widgets.css">

	<?= $settings['codes_head'] ?? ''; ?>
</head>
<body class="trggm-widget" style="background: #f5f5f5;">

	<div class="microlp webinar">

		<div class="container">

			<div class="body text-center">

				<div class="text"><?= __('services.payments_disabled_title', 'All payment methods are unavailable.'); ?></div>
				<br>
				<h1><?= __('services.payments_disabled_title_h1', 'Sorry!'); ?></h1>

			</div>


		</div>

	</div>


	<?= view('Services\Views\copy', ['from' => 'payment']); ?>

	


	<script src="/assets/js/jquery.min.js"></script>
	<script src="/assets/intl-tel/js/intlTelInput-jquery.min.js"></script>
	<script src="/assets/js/widgets.js"></script>

	<?= $settings['codes_body'] ?? ''; ?>

</body>
</html>