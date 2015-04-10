<?php

/*
 * Makes a call to the Eldis solr webservice passing query string and returns json Object
 */
function ecv_eldis_solr_search_json($query_string = '', $printme = FALSE){
	$source_base_url = 'http://ec.solr.test.ids.ac.uk';
	if($query_string){
		$source_url = $source_base_url . '/?q=' . $query_string . '&rows=99999&indent=on&wt=json';
	}
	if($printme) { print($source_url);}
	$json = file_get_contents($source_url);
	$json_obj = json_decode($json);
	return $json_obj;
}

/*
 * Gets all the group names and ids
 */
function ecv_get_eldis_solr_group_data(){
	$json_obj = ecv_eldis_solr_search_json('entity_type:group&fl=entity_id+entity_name+admin_owner_id');
	$group_options = array();
	if(isset($json_obj->response->docs)){
		foreach($json_obj->response->docs as $doc){
			$group_key = '' . $doc->entity_id;
			$group_options[$group_key] = (array) $doc;
		}
	}
	return $group_options;	
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
function ecv_build_base_query($group_options){
	$query = '';
	if(isset($_REQUEST['submit'])){
		if(isset($_REQUEST['group']) && $_REQUEST['group']){
			$group_key = $_REQUEST['group'];
			$group_header = $group_options[$group_key]['entity_name'];
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
function ecv_get_group_attr($group_options, $attr){
	$group_attr = '';
	if(isset($_REQUEST['submit'])){
		if(isset($_REQUEST['group']) && $_REQUEST['group']){
			$group_key = $_REQUEST['group'];
			$group_attr = $group_options[$group_key][$attr];
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
 * Gets an array of data for the page (populated with form is submittes and solr is queried
 */
function ecv_load_page_data(){
	$page_vars = array();
	evc_get_form_data($page_vars);
	$group_options= ecv_get_eldis_solr_group_data();
	$base_query = ecv_build_base_query($group_options);
	$page_vars['show_results'] = FALSE;
	$page_vars['group_options'] = $group_options;
	$page_vars['base_query'] = '';
	$page_vars['group_number_of_messages'] = '';
	$page_vars['group_name'] = ecv_get_group_attr($group_options, 'entity_name');
	$group_admin_id = ecv_get_group_attr($group_options, 'admin_owner_id');
	if(isset($_REQUEST['submit']) && $base_query){
		$results_query = $base_query . '%20AND%20entity_type:message';
		if(!isset($_REQUEST['include_admin'])){
			$results_query .= '%20AND%20-author_entity_id:' . $group_admin_id;
		}
		$results_json = ecv_eldis_solr_search_json($results_query);
		$group_number_of_messages = $results_json->response->numFound;
		$page_vars['group_number_of_messages'] = $group_number_of_messages;
		$page_vars['base_query'] = $results_query;
		$page_vars['show_results'] = TRUE;
	}
	return $page_vars;	
}


