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
							?>
							<option value="<?php echo $group_key; ?>"><?php echo $group_name; ?></option>
							<?php
							endforeach;
							?>
						</select>
					</div>

					<div class="ui-widget">
						<label for="start_date">Start date</label> <input
							class="datepicker" name="start_date" id="start_date" type="text" />
					</div>

					<div class="ui-widget">
						<label for="end_date">End date</label> <input class="datepicker"
							name="end_date" id="end_date" type="text" />
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
					<span class="labelspan">Number of messages: </span><span class="result"><?php echo $page_vars['group_number_of_messages'] ?></span>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
