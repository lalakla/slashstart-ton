<?= $this->extend('app') ?>
<?= $this->section('main') ?>


<?php
	$rq = service('request');
	$section = $rq->getGet('section');

	if (!$section)
	{
		$section = $installed ? 'installed' : 'available';
	}

?>



<?php

	if (!empty($installed))
	{
		foreach ($installed as $k=>$v)
		{
			if ($v['client_params'] && !empty($services[$v['name']]['description_format']))
			{
				$dscr = $services[$v['name']]['description_format'];

				$cp = json_decode($v['client_params'], true);

				foreach ($cp as $kk=>$vv)
				{
					$dscr = str_replace('%' . $kk . '%', $vv, $dscr);
				}

				$installed[$k]['description'] = $dscr;

			}

		}
	}

?>
<div class="tab-content mb-5" id="v-pills-tabContent">



	<div class="tab-pane p-0 fade <?= $section == 'installed' ? 'show active' : ''; ?>" id="v-installed" role="tabpanel" aria-labelledby="v-installed-tab">
		<?php if (!empty($installed)): ?>
			<div class="row g-gs services">
				<?php foreach ($installed as $i=>$service): $item = $services[$service['name']] ?? null; if (!$item) continue; ?>
				<div class="col-12">
					<div class="card card-bordered">
						<div class="card-inner d-md-inline-flex align-items-center">
							<div class="thumb w-80px"><?php if (!empty($item['photo'])): ?><img src="<?= $item['photo']; ?>" alt=""><?php endif; ?></div>
							<div class="card-body align-center ps-0 ps-md-4">
								<div class="info">
									<h6 class="title"><span class="name"><?= $item['title']; ?></span></h6>
									<div class="meta">
										<?php if (!empty($service['channel_title'])): ?>
								        	<span class="release"><?= $service['channel_title']; ?></span>
								        <?php endif ?>
										<span class="release"><span class="text-soft"><?= $service['description'] ?? ''; ?></span></span>
									</div>
								</div>
							</div>
							<div class="align-center">
								<span class="hidden show-on-hover"><a class="me-2 btn btn-icon btn-outline-danger service-uninstall" data-service="<?= $service['id']; ?>" href="#"><i class="icon ni ni-cross"></i></a></span>
								<a href="#" class="btn btn-info service-config" data-service="<?= $service['id']; ?>" data-service-app="<?= !empty($item['is_virtual_app']) ? $item['id'] : ''; ?>" data-service-title="<?= $service['title']; ?>"><?= __('app.configure_btn', 'Settings'); ?></a>
							</div>
						</div>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		<?php else: ?>
			<div class="empty-module nh">	
				<div class="empty-module-icon"><i class="icon ni ni-block-over"></i></div>
				<h3><?= __('services.no_integrations_title', 'No integrations'); ?></h3>
				<p><?= __('services.no_integrations_notice', 'Connect any integration from Available section'); ?></p>
			</div>
		<?php endif; ?>
	</div>



<?php
	$ready = ['telegram', 'instagram', 'bizon365', 'bitrix24', 'getcourse', 'shop', 'vk', 'yookassa', 'yoomoney', 'prodamus', 'sheets', 'tgadmin', 'leadpay'];

	if (strpos(HOST_CURRENT, 'e.cp') !== false)
	{
		$ready[] = 'mailerlite';
	}

	$hl = !empty($_GET['hl']) ? $_GET['hl'] : [];
	$hl = is_array($hl) ? $hl : [$hl];
	$hl[] = '';

?>

	<div class="tab-pane p-0 fade <?= $section == 'available' || !$section ? 'show active' : ''; ?>" id="v-available" role="tabpanel" aria-labelledby="v-available-tab">
		<div class="row g-gs services">


					<?php if (!empty($services)): foreach ($hl as $hls): foreach ($services as $i=>$item): $isReady = 1; ?>
						<?php if (!in_array($item['id'], $ready) && !isset($_GET[$item['id']]) ): $isReady = 0; ?><?php endif ?>
						<?php if ($hls && $hls != $item['id']): continue; ?><?php endif; if (!$isReady) continue;  ?>
						<div class="col-12">
							<div class="card card-bordered" id="app-<?= $item['id']; ?>">
								<div class="card-inner d-md-inline-flex align-items-center">
									<div class="thumb w-80px"><?php if (!empty($item['photo'])): ?><img src="<?= $item['photo']; ?>" alt=""><?php endif; ?></div>
									<div class="card-body align-center ps-0 ps-md-4">
										<div class="info">
											<h6 class="title"><span class="name"><?= $item['title']; ?></span></h6>
											<div class="meta">
												<span class="release"><span class="text-soft"><?= $item['description'] ?? ''; ?></span></span>
											</div>
										</div>
									</div>
									<div class="align-center">
										<?php if (!$isReady): ?>
											<a class="btn btn-lighter" href="#" data-bs-toggle="modal" data-bs-target="#modal-feedback-services"><?= __('services.testing_connect_btn', 'Early Birds'); ?></a>
										<?php else: ?>
											<a class="btn btn-info service-install service-install-<?= $item['id']; ?> <?php if (!empty($_GET['start']) && $_GET['start'] == $item['id']): ?>service-install-autoopen<?php endif ?>" data-service="<?= $item['id']; ?>" data-service-app="<?= $item['is_virtual_app'] ?? 0; ?>" data-service-title="<?= $item['title']; ?>" href="#"><?= __('app.connect_btn', 'Connect'); ?></a>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>


						
					  <?php if ($hls && $hls == $item['id'])
					  {
					  	unset($services[$i]);
					  }
					  ?>

					<?php endforeach; endforeach; endif; ?>
		</div>
	</div>


	<div class="tab-pane p-0 fade <?= $section == 'dev' ? 'show active' : ''; ?>" id="v-dev" role="tabpanel" aria-labelledby="v-dev-tab">
		<div class="row g-gs services">


					<?php if (!empty($services)): foreach ($hl as $hls): foreach ($services as $i=>$item): $isReady = 1; ?>
						<?php if (!in_array($item['id'], $ready) && !isset($_GET[$item['id']]) ): $isReady = 0; ?><?php endif ?>
						<?php if ($hls && $hls != $item['id']): continue; ?><?php endif; if ($isReady) continue; ?>
						<div class="col-12">
							<div class="card card-bordered">
								<div class="card-inner d-md-inline-flex align-items-center">
									<div class="thumb w-80px"><?php if (!empty($item['photo'])): ?><img src="<?= $item['photo']; ?>" alt=""><?php endif; ?></div>
									<div class="card-body align-center ps-0 ps-md-4">
										<div class="info">
											<h6 class="title"><span class="name"><?= $item['title']; ?></span></h6>
											<div class="meta">
												<span class="release"><span class="text-soft"><?= $item['description'] ?? ''; ?></span></span>
											</div>
										</div>
									</div>
									<div class="align-center">
										<?php if (!$isReady): ?>
											<a class="btn btn-lighter" href="#" data-bs-toggle="modal" data-bs-target="#modal-feedback-services"><?= __('services.testing_connect_btn', 'Early Birds'); ?></a>
										<?php else: ?>
											<a class="btn btn-info service-install service-install-<?= $item['id']; ?> <?php if (!empty($_GET['start']) && $_GET['start'] == $item['id']): ?>service-install-autoopen<?php endif ?>" data-service="<?= $item['id']; ?>" data-service-app="<?= $item['is_virtual_app'] ?? 0; ?>" data-service-title="<?= $item['title']; ?>" href="#"><?= __('app.connect_btn', 'Connect'); ?></a>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>


						
					  <?php if ($hls && $hls == $item['id'])
					  {
					  	unset($services[$i]);
					  }
					  ?>

					<?php endforeach; endforeach; endif; ?>
		</div>
	</div>

</div>


<div class="nk-block">
	<div class="card card-bordered">
		<div class="card-inner card-inner-lg">
			<div class="nk-help align-items-center justify-content-between d-md-inline-flex">
				<div class="nk-help-img fs-5rem text-success">
					<i class="icon ni ni-thumbs-up"></i>
				</div>
				<div class="nk-help-text">
					<h5><?= __('services.request_intagration_bn_title', 'We are Hearing our Clients!'); ?></h5>
					<p class="text-soft"><?= __('services.request_intagration_bn_text', 'For our clients with annual paid plans, we can build almost any integration for free.'); ?></p>
				</div>
				<div class="nk-help-action ps-0 ps-md-4">
					<a href="#" class="btn btn-lg btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-feedback-services"><?= __('services.request_intagration_btn', 'Request Integration'); ?></a>
				</div>
			</div>
		</div>
	</div>
</div>


<div class="modal fade" id="service-install-modal" role="dialog">
    <div class="modal-dialog" role="document">
    	<form class="ajax-form modal-content" data-action="/services/install/%s">
	        
	            <div class="modal-header">
	                <h5 class="modal-title" data-title="<?= __('app.connect_integration_title', 'Connect Integration: %s'); ?>"></h5>
	                <div class="close" data-bs-dismiss="modal" aria-label="Close"><span><i class="icon ni ni-cross"></i></span></div>
	            </div>
	            <div class="modal-body">

	            </div>
	            <div class="ajax-form-result"></div>
	            <div class="modal-footer d-flex justify-content-between">
	                <div>
	                	<a class="btn btn-lighter service-help-link hidden" target="_blank"><i class="fa fa-file-text-o"></i> <?= __('app.documentation_btn', 'Help'); ?></a>
	                </div>
	                <div>
	                	<button class="btn btn-light" type="button" data-bs-dismiss="modal"><?= __('app.cancel_btn', 'Cancel'); ?></button>
	                    <button type="submit" class="btn btn-primary service-install-submit"><?= __('app.connect_btn', 'Connect'); ?></button>
	                </div>
	            </div>
	        
        </form>
    </div>
</div>


<div class="modal fade" id="service-config-modal" role="dialog">
    <div class="modal-dialog" role="document">
    	<form class="ajax-form modal-content" data-action="/services/config/%s" data-action-app="/services/install/%s/app/%a">
	        
	            <div class="modal-header">
	                <h5 class="modal-title" data-title="<?= __('app.edit_integration_title', 'Edit Integration %s'); ?>"></h5>
	                <div class="close" data-bs-dismiss="modal" aria-label="Close"><span><i class="icon ni ni-cross"></i></span></div>
	            </div>
	            <div class="modal-body">

	            </div>
	            <div class="ajax-form-result"></div>
	            <div class="modal-footer d-flex justify-content-between">
	                <div>
	                	<a class="btn btn-lighter service-help-link hidden" target="_blank"><i class="fa fa-file-text-o"></i> <?= __('app.documentation_btn', 'Help'); ?></a>
	                </div>
	                <div>
	                	<button class="btn btn-light" type="button" data-bs-dismiss="modal"><?= __('app.cancel_btn', 'Cancel'); ?></button>
	                    <button class="btn btn-primary"><?= __('app.save_btn', 'Save'); ?></button>
	                </div>
	            </div>
	        
        </form>
    </div>
</div>



<div class="modal fade" class="wf-settings-form wf-settings-form-modal" id="modal-feedback-services">
    <div class="modal-dialog" role="document">
    	<form class="ajax-form modal-content" action="/pages/message">
	        
	            <div class="modal-header">
	                <h5 class="modal-title"><?= __('services.join_testing_title', 'Request Integration'); ?></h5>
	                <div class="close" data-bs-dismiss="modal" aria-label="Close"><span><i class="icon ni ni-cross"></i></span></div>
	            </div>
	            <div class="modal-body">


	            	<div class="form-group">
						<label><?= __('app.form_label_your_name', 'Your Name'); ?></label>
					    <input name="data[name]" value="<?= session()->get('user.name') ?>" class="form-control">
					</div>
					<div class="form-group">
						<label><?= __('app.form_label_email', 'Email'); ?></label>
					    <input name="data[email]" value="<?= session()->get('user.email') ?>" class="form-control">
					</div>

		            <div class="form-group">
		            	<label><?= __('services.join_testing_describe_title', 'Please describe which integration and features you need.'); ?></label>
					    <textarea name="data[message]" class="form-control" rows="7"></textarea>
					</div>

					<div class="ajax-form-result"></div>

	            </div>

	            <div class="modal-footer d-flex justify-content-between">
	                <button class="btn btn-light" type="button" data-bs-dismiss="modal"><?= __('app.cancel_btn', 'Cancel'); ?></button>
	                <button class="btn btn-primary"><?= __('app.send_btn', 'Send'); ?></button>
	            </div>

	            
	        
	    </form>
    </div>
</div>

<?= $this->endSection() ?>