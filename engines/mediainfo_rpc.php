<?php
/**
 * Gets information from movie library by Mediainfo XMLRPC
 *
 *
 * @package Engines
 * @author  mboczek    <mboczek@gmail.com>
 */

function get_mediainfo($diskid,$title,$data)
{
	global $config;
	
	//If title or diskid is empty we can do nothing. The same if settings for mediainfo are empty
	if(empty($diskid) OR empty($title) OR empty($config['xmlrpc_mediainfo_secret']) OR empty($config['xmlrpc_mediainfo_server'])) return $data;
	
	//Getting data
	$request = xmlrpc_encode_request("get_mediainfo", array($config['xmlrpc_mediainfo_secret'], $diskid, $title),array('encoding'=>'UTF-8','escaping'=>'markup'));
	$context = stream_context_create(array('http' => array(
			'method' => "POST",
			'header' => "Content-Type: text/xml",
			'content' => $request
	)));
	
	$file = file_get_contents($config['xmlrpc_mediainfo_server'], false, $context);
	
	$response = xmlrpc_decode($file);
	
	if ($response && xmlrpc_is_fault($response)) {
		//We should show error here but what for?
		//trigger_error("xmlrpc: $response[faultString] ($response[faultCode])");
		
		//In case user want to override data and we don't want to keep bad/old data, 
		// we set everything to empty cause we haven't found anything
		$data['filename'] = '';
		$data['filesize'] = '';
		$data['filedate'] = '';
		$data['audio_codec'] = '';
		$data['video_codec'] = '';
		$data['video_width'] = '';
		$data['video_height'] = '';
	} 
	else {
		//Setting table with proper values
		$data['filename'] = $response['filename'];
		$data['filesize'] = $response['filesize'];
		$data['filedate'] = $response['filedate'];
		$data['audio_codec'] = $response['audio_codec'];
		$data['video_codec'] = $response['video_codec'];
		$data['video_width'] = $response['video_width'];
		$data['video_height'] = $response['video_height'];
	}
	return $data;
}