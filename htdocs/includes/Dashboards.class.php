<?php
// vim: set ai ts=4 sw=4 ft=php:
/**
 * This is Part of the User Control Panel Object
 * A replacement for the Asterisk Recording Interface
 * for FreePBX
 *
 * User class for the UCP Object.
 * Contains all user data for the logged in user
 *
 * License for all code of this FreePBX module can be found in the license file inside the module directory
 * Copyright 2006-2014 Schmooze Com Inc.
 */
namespace UCP;
use \Ramsey\Uuid\Uuid;
use \Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
class Dashboards {

	public function __construct($UCP) {
		$this->UCP = $UCP;
	}

	/**
	 * Determine what commands are allowed
	 *
	 * Used by Ajax Class to determine what commands are allowed by this class
	 *
	 * @param string $command The command something is trying to perform
	 * @param string $settings The Settings being passed through $_POST or $_PUT
	 * @return bool True if pass
	 */
	function ajaxRequest($command, $settings) {
		switch($command) {
			case 'add':
			case 'rename':
			case 'remove':
			case 'dashboards':
			case 'savedashlayout':
			case 'getdashlayout':
				return true;
			default:
				return false;
			break;
		}
	}

	/**
	 * The Handler for all ajax events releated to this class
	 *
	 * Used by Ajax Class to process commands
	 *
	 * @return mixed Output if success, otherwise false will generate a 500 error serverside
	 */
	function ajaxHandler() {
		$return = array("status" => false, "message" => "");
		switch($_REQUEST['command']) {
			case 'add':
				$user = $this->UCP->User->getUser();
				$dashboards = $this->UCP->getGlobalSettingByID($user['id'],'dashboards');
				$dashboards = is_array($dashboards) ? $dashboards : array();
				$id = (string)Uuid::uuid4();
				$dashboards[] = array(
					"id" => $id,
					"name" => $_POST['name']
				);
				$this->UCP->setGlobalSettingByID($user['id'],'dashboards',$dashboards);
				return array("status" => true, "id" => $id);
			break;
			case 'rename':
				$user = $this->UCP->User->getUser();
				$dashboards = $this->UCP->getGlobalSettingByID($user['id'],'dashboards');
				$dashboards = is_array($dashboards) ? $dashboards : array();
				foreach($dashboards as $k => $d) {
					if($d['id'] == $_POST['id']) {
						$dashboards[$k]['name'] = $_POST['name'];
						$this->UCP->setGlobalSettingByID($user['id'],'dashboards',$dashboards);
						return array("status" => true, "id" => $id);
						break;
					}
				}
				return array("status" => false, "message" => "Invalid Dashboard ID");
			break;
			case 'remove':
				$user = $this->UCP->User->getUser();
				$dashboards = $this->UCP->getGlobalSettingByID($user['id'],'dashboards');
				$dashboards = is_array($dashboards) ? $dashboards : array();
				foreach($dashboards as $k => $d) {
					if($d['id'] == $_POST['id']) {
						unset($dashboards[$k]);
						$this->UCP->setGlobalSettingByID($user['id'],'dashboards',$dashboards);
						$this->UCP->setGlobalSettingByID($user['id'],'dashboard-layout-'.$_POST['id'],null);
						return array("status" => true);
						break;
					}
				}
			break;
			case 'dashboards':
				return $this->getDashboards();
			break;
			case 'orderdashboards':
			break;
			case 'savedashlayout':
				$user = $this->UCP->User->getUser();
				return $this->UCP->setGlobalSettingByID($user['id'],'dashboard-layout-'.$_POST['id'],$_POST['data']);
			break;
			case 'getdashlayout':
				return $this->getLayoutByID($_POST['id']);
			break;
		}
		return false;
	}

	public function getDashboards() {
		$user = $this->UCP->User->getUser();
		$dashboards = $this->UCP->getGlobalSettingByID($user['id'],'dashboards');
		$dashboards = is_array($dashboards) ? $dashboards : array();
		return $dashboards;
	}

	public function getLayoutByID($id) {
		$user = $this->UCP->User->getUser();
		return $this->UCP->getGlobalSettingByID($user['id'],'dashboard-layout-'.$id);
	}
}