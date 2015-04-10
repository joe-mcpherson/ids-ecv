<?php
include_once('includes/ecv.php');
$this_page_url =  "//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$page_vars = ecv_load_page_data();
?>
<html>
<head>
<title>Eldis Communities Visualisation</title>
<link rel="stylesheet" href="css/html5reset-1.6.1.css" />
<link
	href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css"
	rel="stylesheet">
<link rel="stylesheet"
	href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css" />
<link rel="stylesheet" href="css/ecv.css" />
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script src="js/ecv.js"></script>
<script>
$(function() {
	var form_start_date = '<?php if(isset($page_vars['form_start_date'])): echo $page_vars['form_start_date']; endif;  ?>';
	var form_end_date = '<?php if(isset($page_vars['form_end_date'])): echo $page_vars['form_end_date']; endif;  ?>';
	if(form_start_date){
		$('#start_date').val(form_start_date);
	}
	if(form_end_date){
		$('#end_date').val(form_end_date);
	}
});

</script>
</head>
<body>
	<div class="container">
		<div class="row">
			<div id="heading-row" class="col-md-12">
				<h1>Eldis Communities Visualisation</h1>
			</div>

			<div id="user-filters" class="col-md-6">
				<form action="<?php echo $this_page_url; ?>" method="get">
					<div class="ui-widget">
						<label for="group">Group</label> <select id="group" name="group">
							<option value=""></option>
							<?php
							foreach($page_vars['group_options'] as $group_key => $group_name):
							$selected = ($group_key == $page_vars['form_group']) ? 'selected="selected"':'';
							?>
							<option value="<?php echo $group_key; ?>" <?php echo $selected; ?>><?php echo $group_name; ?></option>
							<?php
							endforeach;
							?>
						</select>
					</div>

					<div class="ui-widget">
						<label for="start_date">Start date</label> 
						<input class="datepicker" name="start_date" id="start_date" type="text" value="<?php echo $page_vars['form_start_date'] ?>" />
					</div>

					<div class="ui-widget">
						<label for="end_date">End date</label> 
						<input class="datepicker" name="end_date" id="end_date" type="text" value="<?php echo $page_vars['form_end_date'] ?>" />
					</div>

					<div class="ui-widget">
						<label for="include_admin">Admin included</label> <input
							name="include_admin" id="include_admin" type="checkbox" value="1"
							checked="checked" />
					</div>
					<input name="submit" id="submit" type="submit" value="Submit" />
				</form>
			</div>
			<div id="top-level-results" class="col-md-6">
				<h2>
				<?php echo $page_vars['group_name'] ?>
				</h2>
				<div class="results">
					<?php if($page_vars['show_results']): ?>
					<span class="labelspan">Number of messages: </span><span class="result"><?php echo $page_vars['group_number_of_messages'] ?></span>
					<?php endif; ?>
				</div>
			</div>
		
		</div>
		<div id="content-row" class="row">
			<p><?php echo $page_vars['base_query'] ?><br/><a href="http://ec.solr.test.ids.ac.uk?q=<?php echo $page_vars['base_query'] ?>" target="_blank">launch</a></p>
		</div>
	</div>
</body>
</html>
