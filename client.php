<?php
class Client
{
	private $instance_url;
	private $app;

	public function __construct($instance_url) {
		$this->instance_url = $instance_url;
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
		var_dump($client_id, $client_secret, $code);

		$response = $this->_post('/oauth/token',array(
			'grant_type' => 'authorization_code',
			'redirect_uri' => $redirect_uri,
			'client_id' => $client_id,
			'client_secret' => $client_secret,
			'code' => $code
		));

		return json_decode($response);
	}

	public function get_client_id(){
		return $this->app->client_id;
	}

	public function get_client_secret(){
		return $this->app->client_secret;
	}

	private function _post($url, $data = array()){
		return $this->post($this->instance_url.$url, $data);
	}

	private function post($url, $data = array()) {
		$postData = http_build_query($data);

		$opts = array(
			'http' => array(
				'method' => 'POST',
				'header' => 'Content-type: application/x-www-form-urlencoded',
				'content' => $postData
			)
		);

		$context = stream_context_create($opts);

		return file_get_contents($url, false, $context);
	}
}