<?php
function eldis_solr_search_xml($query_string = ''){
	$source_base_url = 'http://ec.solr.test.ids.ac.uk';
	if($query_string){
		$source_url = $source_base_url . '/?q=' . $query_string;
	}
	$xmlstr = file_get_contents($source_url);
	$xmlobj = new SimpleXMLElement($xmlstr);
	return $xmlobj;
}
/* get all group names and ids */
$xmlobj = eldis_solr_search_xml('entity_type:group&fl=entity_id+entity_name&rows=1000');

$group_options = '';
if(isset($xmlobj->result->doc)){
	foreach($xmlobj->result->doc as $doc){
		$group_options['' . $doc->str[0]] = trim('' . $doc->str[1]);
	}
} else {
	$output .= 'Failed to load groups';
}