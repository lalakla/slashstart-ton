
<div class="text-center">

	<div class="cart-panel">
		<?php if (!empty($config['cart']['order_thanks_title'])): ?>
			<h1 class="text-dark"><?= hst($config['cart']['order_thanks_title'] ?? ''); ?></h1>
		<?php endif ?>

		<?php if (!empty($config['cart']['order_thanks_content'])): ?>
			<div class="cart-page-content mb-4"><?= hst($config['cart']['order_thanks_content']); ?></div>
		<?php endif ?>


		<?php if (!empty($config['cart']['thanks_btn'])): ?>

			<?php if ($config['cart']['thanks_btn_action'] == 'close'): ?>
				<button class="btn btn-success app-window-close checkout-btn-styles"><?= hst($config['cart']['thanks_btn'] ?? __('Закрыть')); ?></button>

			<?php elseif ($config['cart']['thanks_btn_action'] == 'link'): ?>
				<a class="btn btn-success checkout-btn-styles" href="<?= $config['cart']['thanks_btn_link'] ?? '/' ?>"><?= hst($config['cart']['thanks_btn'] ?? __('Перейти')); ?></a>

			<?php elseif ($config['cart']['thanks_btn_action'] == 'payment' && $paymentLink): ?>
				<a class="btn btn-success checkout-btn-styles" href="<?= $paymentLink ?>"><?= hst($config['cart']['thanks_btn'] ?? __('Оплатить')); ?></a>

			<?php endif ?>


			<div class="alert alert-warning app-window-close-notice d-none"><?= __('Не удалось закрыть окно. Нажмите, пожалуйста, крестик вверху для возврата в мессенджер.'); ?></div>

		<?php endif ?>



	</div>

</div>

