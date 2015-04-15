<?php
define('ECV_BASE_URL', 'http://ec.solr.test.ids.ac.uk');
define('ECV_DEBUG', FALSE);
/*
 * Makes a call to the Eldis solr webservice passing query string and returns json Object
 */
function ecv_eldis_solr_search_json($query_string = '', $printme = FALSE){
	$source_base_url = ECV_BASE_URL;
	if($query_string){
		$source_url = $source_base_url . '/?q=' . $query_string . '&rows=99999&indent=on&wt=json';
	}
	if($printme) { print($source_url);}
	$json = file_get_contents($source_url);
	$json_obj = json_decode($json);
	return $json_obj;
}

/*
 * Gets all the group data
 */
function ecv_get_eldis_solr_group_data(){
	$json_obj = ecv_eldis_solr_search_json('entity_type:group');/*&fl=entity_id+entity_name+admin_owner_id+member_id*/
	$group_data = array();
	if(isset($json_obj->response->docs)){
		foreach($json_obj->response->docs as $doc){
			$group_key = '' . $doc->entity_id;
			$group_data[$group_key] = (array) $doc;
		}
	}
	return $group_data;	
}

/*
 * Gets all the user data
 */
function ecv_get_eldis_solr_user_data(){
	$json_obj = ecv_eldis_solr_search_json('entity_type:user&fl=entity_id+entity_name+email+country');
	$user_data = array();
	if(isset($json_obj->response->docs)){
		foreach($json_obj->response->docs as $doc){
			$user_key = '' . $doc->entity_id;
			$user_data[$user_key] = (array) $doc;
		}
	}
	return $user_data;	
}


/*
 * Format date for solr to understand
 */
function ecv_format_date_for_solr($raw_date){
	$timestamp = strtotime($raw_date);
	return date("Y-m-d", $timestamp) . 'T' . date("H:i:s", $timestamp) . 'Z';	
}


/*
 * Builds the solr base query from the user filters
 */
function ecv_build_base_query($group_data){
	$query = '';
	if(isset($_REQUEST['submit'])){
		if(isset($_REQUEST['group']) && $_REQUEST['group']){
			$group_key = $_REQUEST['group'];
			$group_header = $group_data[$group_key]['entity_name'];
			$query .= 'group_id:' . $group_key;
		}
		$at_least_one_date_set = FALSE;
		if(isset($_REQUEST['start_date']) && trim($_REQUEST['start_date']) != ''){
			$start_date_solr = ecv_format_date_for_solr($_REQUEST['start_date']);
			$at_least_one_date_set = TRUE;
		} else {
			/* just set to 1970 if start date not set */
			$start_date_solr = ecv_format_date_for_solr('1-1-1970');
		}
		if(isset($_REQUEST['end_date']) && trim($_REQUEST['end_date']) != ''){
			$end_date_solr = ecv_format_date_for_solr($_REQUEST['end_date']);
			$at_least_one_date_set = TRUE;
		} else {
			$end_date_solr = 'NOW';
		}
		if($at_least_one_date_set && $_REQUEST['group']){
			$query .= "%20AND%20date_created:[$start_date_solr%20TO%20$end_date_solr]";
		}
		
	}	
	return $query;
}

/*
 * Gets group attribute from group key
 */
function ecv_get_group_attr($group_data, $attr){
	$group_attr = '';
	if(isset($_REQUEST['submit'])){
		if(isset($_REQUEST['group']) && $_REQUEST['group']){
			$group_key = $_REQUEST['group'];
			$group_attr = $group_data[$group_key][$attr];
		}
	}	
	return $group_attr;
}

/*
 * Gets the form vars
 */
function evc_get_form_data(&$page_vars){
	if(isset($_REQUEST['submit'])){
		$page_vars['form_group'] = (isset($_REQUEST['group'])) ? $_REQUEST['group']:'';
		$page_vars['form_start_date'] = (isset($_REQUEST['start_date'])) ? $_REQUEST['start_date']:'';
		$page_vars['form_end_date'] = (isset($_REQUEST['end_date'])) ? $_REQUEST['end_date']:'';
		$page_vars['form_include_admin'] = (isset($_REQUEST['include_admin'])) ? $_REQUEST['include_admin']:'';
	}		
}

/*
 * Get the global totals data for the group 
 */
function ecv_get_data_group_totals($entity_id, $results_json, $group_admin_id, $member_id_array){
		$totals_arr = array();
		$group_total = $results_json->response->numFound;
		/* Set data for number of messages breakdown */
		$member_total = 0;
		$admin_total = 0;
		if(isset($results_json->response->docs)){
			foreach($results_json->response->docs as $doc){
				if($doc->author_entity_id == $group_admin_id){
					$admin_total++;
				}
				elseif(in_array($doc->author_entity_id, $member_id_array)){
					$member_total++;
				}
			}
		}
		$totals_arr[$entity_id] = array();
		$totals_arr[$entity_id]['total'] = $group_total;
		$totals_arr[$entity_id]['total_member'] = $member_total;
		$totals_arr[$entity_id]['total_admin'] = $admin_total;
		$totals_arr[$entity_id]['total_nonmember'] = $group_total - $member_total - $admin_total;
		return $totals_arr;	
}

/*
 * Country data 
 */
function ecv_get_data_country_data($user_data, $results_json){
	/* Set data for user/country */
	$country_data = array();
	if(isset($results_json->response->docs)){
		foreach($results_json->response->docs as $doc){
			$user_key = ''. $doc->author_entity_id;
			if(isset($user_data[$user_key])){
				$user_country = $user_data[$user_key]['country'];
				if(!isset($country_data[$user_country])){
					$country_data[$user_country] = 1;
				} else {
					$country_data[$user_country]++;
				}
			}
		}		
	}
	return $country_data;			
}


/*
 * Time data 
 */
function ecv_get_data_time_data($results_json){
	/* Get messages over time data */
	//print_r($results_json);
	$month_array = array();
	$messages_over_time = array();
	if(isset($results_json->response->docs)){
		foreach($results_json->response->docs as $doc){
			/* e.g. 2014-12-15T11:27:29Z */
			$date_created_arr_raw = explode('T', $doc->date_created);
			/* e.g. 2014-12-15 */
			$date_created_raw = $date_created_arr_raw[0];
			$date_created_arr = explode('-', $date_created_raw);
			$month = $date_created_arr[0] . '-' . $date_created_arr[1];
			if(!isset($month_array[$month])){
				$month_array[$month] = 1;
			} else {
				$month_array[$month]++;
			}
		}
	}	
	return $month_array;		
}

/*
 * format the query to select a entity dataset form filters 
 */
function ecv_format_data_query($base_query, $entity_id, $group_admin_id){
	$results_query = $base_query . '%20AND%20entity_type:' .$entity_id;
	if(!isset($_REQUEST['include_admin'])){
		$results_query .= '%20AND%20-author_entity_id:' . $group_admin_id;
	}	
	return $results_query;
}


/*
 * Gets an array of data for the page (populated with form is submittes and solr is queried
 */
function ecv_load_page_data(){
	$page_vars = array();
	evc_get_form_data($page_vars);
	$group_data = ecv_get_eldis_solr_group_data();
	$user_data = ecv_get_eldis_solr_user_data();
	$base_query = ecv_build_base_query($group_data);
	$page_vars['group_data'] = $group_data;
	$page_vars['group_id'] = '';
	$page_vars['group_global'] = array();
	$member_id_array = ecv_get_group_attr($group_data, 'member_id');
	$group_admin_id = ecv_get_group_attr($group_data, 'admin_owner_id');
	$page_vars['message_data'] = array();
	
	if(isset($_REQUEST['submit']) && $base_query){
		if(isset($_REQUEST['group']) && $_REQUEST['group']){
			$page_vars['group_id'] = $_REQUEST['group'];
		}
		
		/* Get messages data */
		$results_query = ecv_format_data_query($base_query, 'message', $group_admin_id);
		$results_json = ecv_eldis_solr_search_json($results_query);
		$page_vars['group_global'] = ecv_get_data_group_totals('message', $results_json, $group_admin_id, $member_id_array);
		$page_vars['message_data']['country_data'] = ecv_get_data_country_data($user_data, $results_json);
		$page_vars['message_data']['time_data'] = ecv_get_data_time_data($results_json);
		
		$page_vars['base_query'] = $base_query;
	}
	return $page_vars;	
}


