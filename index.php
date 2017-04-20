<?php
date_default_timezone_set('Europe/Zurich');

require_once(__DIR__ . '/lib/zaphpa/zaphpa.lib.php');
require_once(__DIR__ . '/lib/savant/Savant3.php');

if (!file_exists('data')) {
	mkdir('data', 0777, true);
}

$router = new Zaphpa_Router();

$router->addRoute(array(
  'path'     => '/',
  'get'      => array('Main', 'main'),
));
$router->addRoute(array(
  'path'     => '/{email}/help',
  'get'      => array('Main', 'help'),
));
$router->addRoute(array(
  'path'     => '/{email}/upload',
  'get'      => array('Main', 'upload'),
));
$router->addRoute(array(
  'path'     => '/{email}/install',
  'get'      => array('Main', 'install'),
));
$router->addRoute(array(
  'path'     => '/{email}/fingerprint',
  'get'      => array('Main', 'fingerprint'),
));
$router->addRoute(array(
  'path'     => '/{email}',
  'put'      => array('Main', 'create'),
));
$router->addRoute(array(
  'path'     => '/{email}',
  'get'      => array('Main', 'get'),
));

try {
  $router->route();
} catch (Zaphpa_InvalidPathException $ex) {
  header("Content-Type: text/plain;", TRUE, 404);
  die('Nothing found.');
}

class Main {
	private $tpl;
	private $file_db;
	function __construct() {
		$this->tpl = new Savant3();
		$this->file_db = new PDO('sqlite:data/db.sqlite3');
		$this->file_db->setAttribute(PDO::ATTR_ERRMODE,
		                        PDO::ERRMODE_EXCEPTION);
		$this->file_db->exec("CREATE TABLE IF NOT EXISTS keys (
			                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
			                email TEXT,
			                key TEXT,
			                created INTEGER
			                )");
	}
  public function main($req, $res) {
		$this->tpl->url = $this->base_url();
		$this->tpl->email = "your@email.tld";
		$res->add($this->tpl->fetch('views/help.tpl.php'));
		$res->send(200, 'txt');
	}
	public function help($req, $res) {
		$this->tpl->url = $this->base_url();
		$this->tpl->email = $req->params['email'];
		$res->add($this->tpl->fetch('views/help.tpl.php'));
		$res->send(200, 'txt');
	}
	public function upload($req, $res) {
		$result = $this->file_db->query('SELECT * FROM keys WHERE email="'.$req->params['email'].'"');
		$rows = $result->fetchAll();
		if (count($rows) === 0) {
			$this->tpl->url = $this->base_url().$req->params['email'];
			$this->tpl->keypath = isset($req->data['keypath']) ? $req->data['keypath'] : '';
			$res->add($this->tpl->fetch('views/upload.tpl.php'));
		}
		else {
			$res->add('echo -e "\\033[31mSorry I just can\'t do it!\\033[0m"');
		}
		$res->send(200, 'txt');
	}
  public function install($req, $res) {
		$result = $this->file_db->query('SELECT * FROM keys WHERE email="'.$req->params['email'].'"');
		$rows = $result->fetchAll();
		if (count($rows) === 1) {
			$this->tpl->key = $rows[0]['key'];
			$this->tpl->fingerprint = $this->get_fingerprint($rows[0]['key']);
			$res->add($this->tpl->fetch('views/install.tpl.php'));
		}
		else {
			$res->add('echo -e "\\033[31mNo key to install.\\033[0m"');
		}
		$res->send(200, 'txt');
	}
	public function fingerprint($req, $res) {
		$result = $this->file_db->query('SELECT * FROM keys WHERE email="'.$req->params['email'].'"');
		$rows = $result->fetchAll();
		if (count($rows) === 1) {
			$res->add($this->get_fingerprint($rows[0]['key']) . "\n");
		}
		else {
			$res->add('No key found.');
		}
		$res->send(200, 'txt');
	}
	public function create($req, $res) {
		$data = json_decode($req->data['_RAW_HTTP_DATA']);
		$this->file_db->query('INSERT INTO keys (email, key, created) VALUES ("'.$req->params['email'].'", "'.$data->key.'", '.time().')');
		$res->add($this->get_fingerprint($data->key) . "\n");
		$res->send(200, 'txt');
	}
	public function get($req, $res) {
		$result = $this->file_db->query('SELECT * FROM keys WHERE email="'.$req->params['email'].'"');
		$rows = $result->fetchAll();
		if (count($rows) === 1) {
			$res->add($rows[0]['key']);
		}
		else {
			$res->add('No key found.');
		}
		$res->send(200, 'txt');
	}

	private function base_url() {
		return sprintf(
			"%s://%s",
			isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
			$_SERVER['SERVER_NAME'].'/'
		);
	}
  private function get_fingerprint($key) {
    $content = explode(' ', $key, 3);
    return join(':', str_split(md5(base64_decode($content[1])), 2));
  }
}
