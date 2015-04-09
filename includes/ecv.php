<?php
/*
 * Makes a call to the Eldis solr webservice passing query string and returns Simple XML Object
 */
function ecv_eldis_solr_search_xml($query_string = ''){
	$source_base_url = 'http://ec.solr.test.ids.ac.uk';
	if($query_string){
		$source_url = $source_base_url . '/?q=' . $query_string;
	}
	$xmlstr = file_get_contents($source_url);
	$xmlobj = new SimpleXMLElement($xmlstr);
	return $xmlobj;
}

/*
 * Gets all the group names and ids
 */
function ecv_get_eldis_solr_group_data(){
	$xmlobj = ecv_eldis_solr_search_xml('entity_type:group&fl=entity_id+entity_name&rows=1000');
	$group_options = array();
	if(isset($xmlobj->result->doc)){
		foreach($xmlobj->result->doc as $doc){
			$group_options['' . $doc->str[0]] = trim('' . $doc->str[1]);
		}
	}
	return $group_options;	
}

/*
 * Builds the solr base query from the user filters
 */
function ecv_build_base_query($group_options){
	$query = '';
	if(isset($_REQUEST['submit'])){
		if(isset($_REQUEST['group'])){
			$group_key = $_REQUEST['group'];
			$group_header = $group_options[$group_key];
			$query .= 'group_id:' . $group_key;
		}
	}	
	return $query;
}

/*
 * Gets group name from filters
 */
function ecv_get_group_name($group_options){
	$group_name = '';
	if(isset($_REQUEST['submit'])){
		if(isset($_REQUEST['group'])){
			$group_key = $_REQUEST['group'];
			$group_name = $group_options[$group_key];
		}
	}	
	return $group_name;
}

/*
 * Gets an array of data for the page (populated with form is submittes and solr is queried
 */
function ecv_load_page_data(){
	$page_vars = array();
	$group_options= ecv_get_eldis_solr_group_data();
	$base_query = ecv_build_base_query($group_options);
	$page_vars['group_options'] = $group_options;
	$page_vars['group_name'] = ecv_get_group_name($group_options);
	$group_number_of_messages_xml = ecv_eldis_solr_search_xml($base_query . ' AND entity_type:message');
	$group_number_of_messages_result_attributes = $group_number_of_messages_xml->result->attributes();
	$group_number_of_messages = $group_number_of_messages_result_attributes['numFound'];
	$page_vars['group_number_of_messages'] = $group_number_of_messages;
	return $page_vars;	
}


