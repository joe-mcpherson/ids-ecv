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
 * Gets an array of data for the page (populated with form is submittes and solr is queried
 */
function ecv_load_page_data(){
	$page_vars = array();
	evc_get_form_data($page_vars);
	$group_data = ecv_get_eldis_solr_group_data();
	$user_data = ecv_get_eldis_solr_user_data();
	$base_query = ecv_build_base_query($group_data);
	$page_vars['group_data'] = $group_data;
	$page_vars['base_query'] = '';
	$page_vars['group_number_of_messages'] = '';
	$page_vars['group_id'] = '';
	$member_id_array = ecv_get_group_attr($group_data, 'member_id');
	$group_admin_id = ecv_get_group_attr($group_data, 'admin_owner_id');
	if(isset($_REQUEST['submit']) && $base_query){
		if(isset($_REQUEST['group']) && $_REQUEST['group']){
			$page_vars['group_id'] = $_REQUEST['group'];
		}
		$results_query = $base_query . '%20AND%20entity_type:message';
		if(!isset($_REQUEST['include_admin'])){
			$results_query .= '%20AND%20-author_entity_id:' . $group_admin_id;
		}
		$results_json = ecv_eldis_solr_search_json($results_query);
		$group_total_number_of_messages = $results_json->response->numFound;
		
		
		/* Set data for number of messages breakdown */
		
		$group_member_number_of_messages = 0;
		$group_admin_number_of_messages = 0;
		if(isset($results_json->response->docs)){
			foreach($results_json->response->docs as $doc){
				if($doc->author_entity_id == $group_admin_id){
					$group_admin_number_of_messages++;
				}
				elseif(in_array($doc->author_entity_id, $member_id_array)){
					$group_member_number_of_messages++;
				}
			}
		}
		$page_vars['group_total_number_of_messages'] = $group_total_number_of_messages;
		$page_vars['group_member_number_of_messages'] = $group_member_number_of_messages;
		$page_vars['group_admin_number_of_messages'] = $group_admin_number_of_messages;
		$page_vars['group_nonmember_number_of_messages'] = $group_total_number_of_messages - $group_member_number_of_messages - $group_admin_number_of_messages;
		
		
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
		$page_vars['country_data'] = $country_data;
		
		$page_vars['base_query'] = $base_query;
	}
	return $page_vars;	
}


