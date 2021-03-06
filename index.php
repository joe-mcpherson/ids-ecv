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
<link href="http://explorer.okhub.org/css/main.css" rel="stylesheet" type="text/css">
<link href="http://www.okhub.org/static/globalnav.css" rel="stylesheet" type="text/css">
<link href="http://data.okhub.org/sites/all/themes/skeletontheme/css/style.css" rel="stylesheet" type="text/css">
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
      google.load('visualization', '1.1', {'packages':['corechart', 'timeline']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(drawCharts);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawCharts() {
		<?php if( $page_vars['group_global']['message']['total'] > 0): ?>
	    	/* Group messages breakdown pie chart */
	        // Create the data table.
	        var data = new google.visualization.DataTable();
	        data.addColumn('string', 'User type');
	        data.addColumn('number', 'Messages');
	        data.addRows([
	          ['Admin', <?php echo $page_vars['group_global']['message']['total_admin'] ?>],
	          ['Group member', <?php echo $page_vars['group_global']['message']['total_member'] ?>],
	          ['Non-member', <?php echo $page_vars['group_global']['message']['total_nonmember'] ?>]
	        ]);
	
	        // Set chart options
	        var options = {'title':'Number of messages by user type',
	                       'width':400,
	                       'height':300};
	
	        // Instantiate and draw our chart, passing in some options.
	        var chart = new google.visualization.PieChart(document.getElementById('group_message_overview_results_div'));
	        chart.draw(data, options);
	
			<?php if($page_vars['message_data']['country_data']): ?>
	
	    	/* Messages by country breakdown bar graph */
	        // Create the data table.
	        var data = new google.visualization.DataTable();
	        data.addColumn('string', 'Country');
	        data.addColumn('number', 'Messages');
	        data.addRows([
	
			<?php foreach($page_vars['message_data']['country_data'] as $country => $num_messages): ?>
	          ['<?php echo $country; ?>', <?php echo $num_messages; ?>],
	         <?php endforeach; ?> 
	        ]);
	
	        // Set chart options
	        var options = {'title':'Messages by country',
	                       'width':400,
	                       'height':<?php 
	                       $base_height = 300;
	                       $graph_height = $base_height + (count($page_vars['message_data']['country_data']) * 15);
	                       echo $graph_height;
	                       ?>};
	
	        // Instantiate and draw our chart, passing in some options.
	        var chart = new google.visualization.BarChart(document.getElementById('group_message_by_country_results_div'));
	        chart.draw(data, options);
			
			<?php endif; ?>
	
	
			<?php if($page_vars['message_data']['time_data']): 
			$time_data_mode = (ecv_display_day_mode($page_vars['message_data']['time_data']['month'])) ? 'day':'month';
			$time_data_format = ($time_data_mode == 'day') ? 'd/MMM/yy':'MMM/yy';
			?>
			/* Messages by month line graph */
			 var data = new google.visualization.DataTable();
		        data.addColumn('date', 'Month');
		        data.addColumn('number', 'Messages');
	
		        data.addRows([
	
		              		<?php foreach($page_vars['message_data']['time_data'][$time_data_mode] as $time_key => $num_messages): 
		              		$time_key_arr = explode('-', $time_key);
		              		$year = $time_key_arr[0];
		              		$month = $time_key_arr[1];
		              		$day = ($time_data_mode == 'day') ?  $time_key_arr[2]:1; 
		              		?>
		                    [new Date(<?php echo $year; ?>, <?php echo $month - 1; ?>, <?php echo $day; ?>), <?php echo $num_messages; ?>],
		                   <?php endforeach; ?> 
		        ]);
	
	
		        var options = {
		          title: 'Number of messages per <?php echo $time_data_mode; ?>',
		          width: 400,
		          height: 250,
		          hAxis: {
		            format: '<?php echo $time_data_format; ?>',
		            gridlines: {count: <?php echo count($page_vars['message_data']['time_data'][$time_data_mode]); ?>}
		          },
		          vAxis: {
		            gridlines: {color: 'none'},
		            minValue: 0
		          }
		        };
	
		        var chart = new google.visualization.LineChart(document.getElementById('message_time_results_div'));
		        chart.draw(data, options);
		    <?php endif; ?>
	    <?php endif; ?>
		
	    <?php if( $page_vars['group_global']['discussion']['total'] > 0): ?>
		    /* Discussions */
	
		   	/* Group discussions breakdown pie chart */
	        // Create the data table.
	        var data = new google.visualization.DataTable();
	        data.addColumn('string', 'User type');
	        data.addColumn('number', 'Discussions');
	        data.addRows([
	          ['Admin', <?php echo $page_vars['group_global']['discussion']['total_admin'] ?>],
	          ['Member', <?php echo $page_vars['group_global']['discussion']['total_member'] ?>],
	          ['Non-member', <?php echo $page_vars['group_global']['discussion']['total_nonmember'] ?>]
	        ]);
	
	        // Set chart options
	        var options = {'title':'Number of discussions by user type',
	                       'width':400,
	                       'height':300};
	
	        // Instantiate and draw our chart, passing in some options.
	        var chart = new google.visualization.PieChart(document.getElementById('group_discussion_overview_results_div'));
	        chart.draw(data, options);
	
			<?php if($page_vars['discussion_data']['country_data']): ?>
	
	    	/* Messages by country breakdown bar graph */
	        // Create the data table.
	        var data = new google.visualization.DataTable();
	        data.addColumn('string', 'Country');
	        data.addColumn('number', 'Discussions');
	        data.addRows([
	
			<?php foreach($page_vars['discussion_data']['country_data'] as $country => $num_discussions): ?>
	          ['<?php echo $country; ?>', <?php echo $num_discussions; ?>],
	         <?php endforeach; ?> 
	        ]);
	
	        // Set chart options
	        var options = {'title':'Discussions by country',
	                       'width':400,
	                       'height':<?php 
	                       $base_height = 300;
	                       $graph_height = $base_height + (count($page_vars['discussion_data']['country_data']) * 15);
	                       echo $graph_height;
	                       ?>};
	
	        // Instantiate and draw our chart, passing in some options.
	        var chart = new google.visualization.BarChart(document.getElementById('group_discussion_by_country_results_div'));
	        chart.draw(data, options);
			
			<?php endif; ?>
	
	
			<?php if($page_vars['discussion_data']['time_data']): 
				$time_data_mode = (ecv_display_day_mode($page_vars['discussion_data']['time_data']['month'])) ? 'day':'month';
				$time_data_format = ($time_data_mode == 'day') ? 'd/MMM/yy':'MMM/yy';
				?>
				/* Messages by month line graph */
				 var data = new google.visualization.DataTable();
			        data.addColumn('date', 'Month');
			        data.addColumn('number', 'Discussions');
	
			        data.addRows([
	
			              		<?php foreach($page_vars['discussion_data']['time_data'][$time_data_mode] as $time_key => $num_discussions): 
			              		$time_key_arr = explode('-', $time_key);
			              		$year = $time_key_arr[0];
			              		$month = $time_key_arr[1];
			              		$day = ($time_data_mode == 'day') ?  $time_key_arr[2]:1; 
			              		?>
			                    [new Date(<?php echo $year; ?>, <?php echo $month - 1; ?>, <?php echo $day; ?>), <?php echo $num_discussions; ?>],
			                   <?php endforeach; ?> 
			        ]);
	
	
		        var options = {
		          title: 'Number of discussion per <?php echo $time_data_mode; ?>',
		          width: 400,
		          height: 250,
		          hAxis: {
		        	format: '<?php echo $time_data_format; ?>',
		            gridlines: {count: <?php echo count($page_vars['discussion_data']['time_data'][$time_data_mode]); ?>}
		          },
		          vAxis: {
		            gridlines: {color: 'none'},
		            minValue: 0
		          }
		        };
	
		        var chart = new google.visualization.LineChart(document.getElementById('discussion_time_results_div'));
		        chart.draw(data, options);
		    <?php endif; ?>
		<?php endif; ?>
        
      }
      <?php endif; ?>
</script>
</head>
<body>
<?php include('/var/www/includes/nav.shtml'); ?>
	<div class="container">
		<div class="row">
			<div id="heading-row" class="col-md-12">
				<h1>Eldis Communities Visualisation</h1>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6 main-col">
				<div id="user-filters">
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
			</div>
			<div class="col-md-6 main-col">
				<?php if($page_vars['group_id']): ?>
				<div id="group-data-results">
					
					<img class="group-picture" src="<?php echo $page_vars['group_data'][$page_vars['group_id']]['picture']; ?>"/>
					<h2>
					<?php echo $page_vars['group_data'][$page_vars['group_id']]['entity_name']; ?>
					</h2>
					<p class="group-num-visits"><span class="labelspan">number of group members (current): </span><span class="result"><?php echo $page_vars['group_global']['member_count']; ?></span></p>
					<p class="group-num-visits"><span class="labelspan">number of visits (all time): </span><span class="result"><?php echo $page_vars['group_data'][$page_vars['group_id']]['visit_count']; ?></span></p>
					<p class="group-description">
					<?php echo $page_vars['group_data'][$page_vars['group_id']]['description']; ?>
					</p>	
					<p class="group-url">
					<?php $edlis_communties_url = 'http://community.eldis.org' . '/' . $page_vars['group_id']; ?>
					<a href="<?php echo  $edlis_communties_url; ?>">View this group in Eldis Communities</a>
					</p>			
				</div>
				<div class="right-side-results">
					<?php if(!empty($page_vars['form_start_date']) || !empty($page_vars['form_end_date'])): ?>
						<p>Date range <?php if(!empty($page_vars['form_start_date'])): echo 'from <span class="date-text">' . $page_vars['form_start_date'] . '</span>'; endif;  ?> to 
						<span class="date-text"><?php if(!empty($page_vars['form_end_date'])): echo $page_vars['form_end_date']; else: echo 'NOW'; endif;  ?></span></p>
					<?php endif; ?>
					
					<?php if(!$admin_included_checked): ?>
					<p>Admins not included in results!</p>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>
		</div>
		
		<!-- Messages data -->
		<?php if($page_vars['group_id']): ?>
			<?php if($page_vars['group_global']['message']['total']): ?>
			<div class="row">
				<div id="heading-row" class="col-md-12">
					<h2>Group messages</h2>
				</div>
			</div>		
			<div class="row">
				<div class="col-md-6 main-col">
					<div class="left-side-results">
					<?php if(isset($page_vars['message_data']['country_data'])):  ?>
					<div id="group_message_by_country_results_div"></div>
					<?php endif; ?>
					</div> 
					<div class="left-side-results">
					<?php if(isset($page_vars['message_data']['time_data'])):  ?>
					<div id="message_time_results_div"></div>
					<?php endif; ?>
					</div> 
				</div>
				<div class="col-md-6 main-col">
					<div class="right-side-results">
						<div class="results">
							<span class="labelspan">Total number of messages: </span><span class="result result-gg-total"><?php echo $page_vars['group_global']['message']['total']; ?></span><br>
							<?php if($admin_included_checked): ?>
							<span class="labelspan">Admin messages: </span><span class="result result-gg-admin-total"><?php echo $page_vars['group_global']['message']['total_admin']; ?></span><br>
							<?php endif; ?>
							<span class="labelspan">Non-member messages: </span><span class="result result-gg-nonmember-total"><?php echo $page_vars['group_global']['message']['total_nonmember']; ?></span><br>
							<span class="labelspan">Member messages: </span><span class="result result-gg-member-total"><?php echo $page_vars['group_global']['message']['total_member']; ?></span><br>
						</div>			
						<!--Div that will hold the pie chart-->
		    			<div id="group_message_overview_results_div"></div>			
					</div>
				</div>
			</div>
			<?php endif; ?>
		<?php endif; ?>
		
		<!-- Discussions data -->
		<?php if($page_vars['group_id']): ?>
			<?php if($page_vars['group_global']['discussion']['total']): ?>
			<div class="row">
				<div id="heading-row" class="col-md-12">
					<h2>Group discussions</h2>
				</div>
			</div>		
			<div class="row">
				<div class="col-md-6 main-col">
					<div class="left-side-results">
					<?php if(isset($page_vars['discussion_data']['country_data'])):  ?>
					<div id="group_discussion_by_country_results_div"></div>
					<?php endif; ?>
					</div> 
					<div class="left-side-results">
					<?php if(isset($page_vars['discussion_data']['time_data'])):  ?>
					<div id="discussion_time_results_div"></div>
					<?php endif; ?>
					</div> 
				</div>
				<div class="col-md-6 main-col">
					<div class="right-side-results">
						<div class="results">
							<span class="labelspan">Total number of discussions: </span><span class="result result-gg-total"><?php echo $page_vars['group_global']['discussion']['total']; ?></span><br>
							<?php if($admin_included_checked): ?>
							<span class="labelspan">Admin discussions: </span><span class="result result-gg-admin-total"><?php echo $page_vars['group_global']['discussion']['total_admin']; ?></span><br>
							<?php endif; ?>
							<span class="labelspan">Non-member discussions: </span><span class="result result-gg-nonmember-total"><?php echo $page_vars['group_global']['discussion']['total_nonmember']; ?></span><br>
							<span class="labelspan">Member discussions: </span><span class="result result-gg-member-total"><?php echo $page_vars['group_global']['discussion']['total_member']; ?></span><br>
						</div>		
						<!--Div that will hold the pie chart-->
		    			<div id="group_discussion_overview_results_div"></div>				
					</div>
				</div>
			</div>
			<?php endif; ?>
		<?php endif; ?>
		
		<?php if(ECV_DEBUG): ?>
			<?php if($page_vars['group_id']): ?>
			<div id="debug-row" class="row">
				<p><?php echo $page_vars['base_query'] ?><br/><a href="<?php echo ECV_BASE_URL; ?>?q=<?php echo $page_vars['base_query'] ?>" target="_blank">launch</a></p>
			</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
<?php include_once('includes/footer.php'); ?>
</body>
</html>
