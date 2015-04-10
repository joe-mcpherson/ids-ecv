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
<script type="text/javascript" src="//code.jquery.com/jquery-1.10.2.js"></script>
<script type="text/javascript" src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript" src="js/ecv.js"></script>
<script type="text/javascript">
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
<?php if($page_vars['group_id']): ?>
/* Google charts stuff */
      // Load the Visualization API and the piechart package.
      google.load('visualization', '1.0', {'packages':['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawChart() {

        // Create the data table.
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'User type');
        data.addColumn('number', 'Messages');
        data.addRows([
          ['Admin', <?php echo $page_vars['group_admin_number_of_messages'] ?>],
          ['Member', <?php echo $page_vars['group_member_number_of_messages'] ?>],
          ['Non-member', <?php echo $page_vars['group_nonmember_number_of_messages'] ?>]
        ]);

        // Set chart options
        var options = {'title':'Number of messages by user type',
                       'width':400,
                       'height':300};

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
      <?php endif; ?>
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
							foreach($page_vars['group_data'] as $group_key => $group_obj):
							$selected = ($group_key == $page_vars['form_group']) ? 'selected="selected"':'';
							?>
							<option value="<?php echo $group_key; ?>" <?php echo $selected; ?>><?php echo $group_obj['entity_name']; ?></option>
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
					
					<?php 
					$admin_included_checked = '';
					if(!isset($page_vars['form_include_admin']) || (isset($page_vars['form_include_admin']) && $page_vars['form_include_admin'])){
						$admin_included_checked ='checked="checked"';
					}
					?>
					
					<div class="ui-widget">
						<label for="include_admin">Admin included</label> <input
							name="include_admin" id="include_admin" type="checkbox" value="1"
							<?php echo $admin_included_checked; ?> />
					</div>
					<input name="submit" id="submit" type="submit" value="Submit" />
				</form>
			</div>
			<div id="top-level-results" class="col-md-6">
				<?php if($page_vars['group_id']): ?>
				<h2>
				<?php echo $page_vars['group_data'][$page_vars['group_id']]['entity_name'] ?>
				</h2>
				<p>
				<?php echo $page_vars['group_data'][$page_vars['group_id']]['description'] ?>
				</p>
				<?php if(!empty($page_vars['form_start_date']) || !empty($page_vars['form_end_date'])): ?>
					<p>Date range <?php if(!empty($page_vars['form_start_date'])): echo 'from ' . $page_vars['form_start_date']; endif;  ?> to 
					<?php if(!empty($page_vars['form_end_date'])): echo $page_vars['form_end_date']; else: echo 'NOW'; endif;  ?></p>
				<?php endif; ?>
				<?php if(!$admin_included_checked): ?>
				<p>Admins not included in results!</p>
				<?php endif; ?>
				<!--Div that will hold the pie chart-->
    			<div id="chart_div"></div>
				
				<div class="results">
					<span class="labelspan">Total number of messages: </span><span class="result"><?php echo $page_vars['group_total_number_of_messages'] ?></span><br>
					<?php if($admin_included_checked): ?>
					<span class="labelspan">Admin messages: </span><span class="result"><?php echo $page_vars['group_admin_number_of_messages'] ?></span><br>
					<?php endif; ?>
					<span class="labelspan">Non-member messages: </span><span class="result"><?php echo $page_vars['group_nonmember_number_of_messages'] ?></span><br>
					<span class="labelspan">Member messages: </span><span class="result"><?php echo $page_vars['group_member_number_of_messages'] ?></span><br>
				</div>
				<?php endif; ?>
			</div>
		
		</div>
		<div id="content-row" class="row">
			<p><?php echo $page_vars['base_query'] ?><br/><a href="http://ec.solr.test.ids.ac.uk?q=<?php echo $page_vars['base_query'] ?>" target="_blank">launch</a></p>
		</div>
	</div>
</body>
</html>
