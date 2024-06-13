<?php

$pr = \Platform::getProject();

ob_start();


?>

<!-- <?= PROJECT_NAME; ?> code: begin -->
<script type="text/javascript">
(function(t,r,ig,gi,m,c,om){t[m]=t[m]||function(){(t[m].t=t[m].t||[]).push(arguments)};c=r.createElement(ig),om=r.getElementsByTagName(ig)[0],c.async=1,c.src=gi,om.parentNode.insertBefore(c,om)}) (window, document, "script", "<?= API_EXTERNAL_JS_URL; ?>", "<?= PROJECT_NAME; ?>");
<?= PROJECT_NAME; ?>(['init', '<?= str_replace(HOST_SYSTEM, '', $pr['domain_system']); ?>']);
</script>
<!-- /<?= PROJECT_NAME; ?> code: end -->


<?php

$code = ob_get_contents();
ob_end_clean();

$code = trim($code);
// $code = preg_replace('# {2,}#si', '', $code);
// $code = preg_replace('#\t+#si', '', $code);

echo $code;

// p($code);

?>