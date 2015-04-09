<?php
include_once('includes/ecv.php');
$this_page_url =  "//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>
<html>
<head>
<title>Eldis Communities Visualisation</title>
<link rel="stylesheet"href="css/html5reset-1.6.1.css"/>
<link rel="stylesheet"href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css"/>
<link rel="stylesheet"href="css/ecv.css"/>
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script src="js/ecv.js"></script>
</head>
<body>
	<h1>Eldis Communities Visualisation</h1>
	<div id="user-filters">
		<form action="<?php echo $this_page_url; ?>" method="get">
			<div class="ui-widget">
				<label for="group">Group</label>
				<select id="group" name="group">
				<option value=""></option>
				<?php
				foreach($group_options as $group_key => $group_name):
				?>
					<option value="<?php echo $group_key; ?>"><?php echo $group_name; ?></option>
				<?php
				endforeach;
				?>
				</select>
			</div>
			
			<div class="ui-widget">
				<label for="start_date">Start date</label>
				<input class="datepicker" name="start_date" id="start_date" type="text"/>
			</div>		
			
			<div class="ui-widget">
				<label for="end_date">End date</label>
				<input class="datepicker" name="end_date" id="end_date" type="text"/>
			</div>		
			
			<div class="ui-widget">
				<label for="include_admin">Admin included</label>
				<input name="include_admin" id="include_admin" type="checkbox" value="1" checked="checked"/>
			</div>
			<input type="submit" value="Submit"/>
		</form>
	</div>
</body>
</html>
