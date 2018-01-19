<?php
class Client
{
	private $instance_url;
	private $access_token;
	private $app;

	public function __construct($instance_url, $access_token = '') {
		$this->instance_url = $instance_url;
		$this->access_token = $access_token;
	}

	public function register_app($redirect_uri) {

		$result = $this->_post('/api/v1/apps', array(
			'client_name' => 'Mastodon Share for WordPress',
			'redirect_uris' => $redirect_uri,
			'scopes' => 'read write',
			'website' => $this->instance_url
		));

		$response = json_decode($result);
		$this->app = $response;

		$params = http_build_query(array(
			'response_type' => 'code',
			'scope' => 'write',
			'redirect_uri' => $redirect_uri,
			'client_id' =>$this->app->client_id
		));

		return $this->instance_url.'/oauth/authorize?'.$params;
	}

	public function get_bearer_token($client_id, $client_secret, $code, $redirect_uri) {

		$response = $this->_post('/oauth/token',array(
			'grant_type' => 'authorization_code',
			'redirect_uri' => $redirect_uri,
			'client_id' => $client_id,
			'client_secret' => $client_secret,
			'code' => $code
		));

		return json_decode($response);
	}

	public function get_client_id() {
		return $this->app->client_id;
	}

	public function get_client_secret() {
		return $this->app->client_secret;
	}

	public function postStatus($status, $mode) {
		var_dump($this->access_token);

		$headers = array(
			'Authorization: Bearer '.$this->access_token
		);

		$this->_post('/api/v1/statuses', array(
			'status' => $status,
			'visibility' => $mode
		), $headers);
	}

	public function create_attachment($media_path) {
		$headers[] = 'Authorization: Bearer '.$this->access_token;
		$data = array('file' => file_get_contents($media_path));
		$x = $this->_post('/api/v1/media', $data, $headers);
		var_dump($x);
	}

	private function _post($url, $data = array(), $headers = array()) {

		$headers[] = 'Content-type: application/x-www-form-urlencoded';
		return $this->post($this->instance_url.$url, $data, $headers);
	}

	/*private function _post_file($url, $file_path, $headers = array()) {

		$boundary = '_' . 'Mastoshare' . mktime(rand());

		$filename = basename($file_path);
		$mimetype = mime_content_type($file_path);

		$headers[] = 'Content-type: multipart/form-data; boundary=' . $boundary;
		//$headers[] = 'Accept-Encoding: gzip, deflate, br';
		$headers[] = 'Authorization: Bearer '.$this->access_token;

		$content = file_get_contents($file_path);


		/*$data = ['file' => '--' . $boundary . "\r\n" .
				'Content-Disposition: form-data; name="file"; filename="' . $filename . "\r\n" .
				'Content-type: '. $mimetype . "\r\n\r\n" .
				$content ."\r\n" .
				"--". $boundary . "--"."\r\n"];

		$headers[] = 'Content-length: '.strlen($data['file']);

		$data = ['file' => file_get_contents($file_path)];



		return $this->post($this->instance_url.$url, $data, $headers);
	}*/

	private function post($url, $data = '', $headers = array()) {

		//if(is_array($data)){
			$data = http_build_query($data);
		//}

		$opts = array(
			'http' => array(
				'method' => 'POST',
				'header' => $headers,
				'content' => $data
			)
		);

		$context = stream_context_create($opts);

		return file_get_contents($url, false, $context);
	}
}