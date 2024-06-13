<?php

$request = service('request');
$section = $request->getGet('section');

if (!$section)
{
	$section = $installed ? 'installed' : 'available';
}

?>




	<div class="nav nav-tabs nav-tabs-s2 justify-content-start g">
		<li class="nav-item"><a class="nav-link <?= $section == 'installed' ? 'active' : ''; ?>" id="v-installed-tab" href="?section=installed" role="tab" aria-controls="v-installed" aria-selected="true"><?= __('services.tab_installed', 'Installed'); ?></a></li>
		<li class="nav-item"><a class="nav-link <?= $section == 'available' ? 'active' : ''; ?>" id="v-available-tab" href="?section=available" role="tab" aria-controls="v-available" aria-selected="false"><?= __('services.tab_available', 'Available'); ?></a></li>
		<li class="nav-item"><a class="nav-link <?= $section == 'dev' ? 'active' : ''; ?>" id="v-dev-tab"  href="?section=dev" role="tab" aria-controls="v-dev" aria-selected="false"><?= __('services.tab_dev', 'In Development'); ?></a></li>
	</div>
