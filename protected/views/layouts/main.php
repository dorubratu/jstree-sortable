<!DOCTYPE html>
<html>
	<head>
		<title><?php echo APP_NAME; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="Content-Language" content="EN">

		<!--js-->
		<script src="<?php echo APP_PATH . '/assets/jquery/jquery.min.js'; ?>"></script>
		<script src="<?php echo APP_PATH . '/assets/jstree/dist/jstree.min.js'; ?>"></script>
		<script type="text/javascript">window.baseUrl = '<?php echo JS_BASEURL; ?>'</script>
		<?php if (is_file(BASEPATH . '/public_html/js/' . $this->id . '.js')): ?>
		<script type="text/javascript" src="<?php echo APP_PATH . '/js/' . $this->id .'.js'; ?>"></script>
		<?php endif; ?>

		<!--css-->
		<link rel="stylesheet" href="<?php echo APP_PATH . '/assets/jstree/dist/themes/default/style.min.css'; ?>">
	</head>
	<body>
		<div id="globalWrapper" class="<?php echo $this->id; ?>Wrapper">
			<div id="<?php echo $this->id; ?>Content" class="content">
				<section>
					<?php echo $content; ?>
				</section>
			</div>
		</div>
	</body>
</html>