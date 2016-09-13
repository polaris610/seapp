<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: My
 * Date: 4/5/2016
 * Time: 3:05 PM
 */
class Auth extends App_Controller
{
	private $headers;
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Auth_model', 'a_m');
		$this->load->library('email', array(
			'protocol' => 'smtp',
			'smtp_host' => 'smtp.postmarkapp.com',
			'smtp_user' => 'e9182580-2bf4-4417-b076-5c911d9ba31d',
			'smtp_pass' => 'e9182580-2bf4-4417-b076-5c911d9ba31d',
			'smtp_timeout' => 7,
			'mailtype' => 'html'
		));
		$this->headers = apache_request_headers();
	}
	
	public function index()
	{
	}
	
	public function login()
	{
		$user_email = $this->getRequestPayloadParameter('UserEmail');
		$user_password = $this->getRequestPayloadParameter('UserPassword');
		$rData = $this->a_m->userLogin($user_email, $user_password);
		if (sizeof($rData) > 0) {
			$result = array(
				'result' => 'success',
				'data' => $rData
			);
		} else {
			$result = array(
				'result' => 'failed'
			);
		}
		exit($this->sendJsonResponse($result));
	}
	
	public function getuserlist()
	{
		$rData = $this->a_m->getUserList();
		if (sizeof($rData) > 0) {
			$result = array(
				'result' => 'success',
				'data' => $rData
			);
		} else {
			$result = array(
				'result' => 'failed'
			);
		}
		exit($this->sendJsonResponse($result));
	}
	
	public function register()
	{
		$id = $this->getRequestPayloadParameter('id');
		$first_name = $this->getRequestPayloadParameter('first_name');
		$last_name = $this->getRequestPayloadParameter('last_name');
		$user_email = $this->getRequestPayloadParameter('user_email');
		$user_password = $this->getRequestPayloadParameter('user_password');
		$user_flag = $this->getRequestPayloadParameter('user_flag');
		$rData = $this->a_m->RegisterUser($id, $first_name, $last_name, $user_email, $user_password, $user_flag);
		if ($rData == "success" && $id == "Auto") {
			$this->email->set_newline("\r\n");
			$this->email->from("login@emailtools.com");
			$this->email->to($user_email);
			$this->email->subject('Welcome to Email Tools - Your username and Password!');
			$message = "Hi, <br/><br/>Thank you for your purchase.<br/><br/>";
			$message .= "Your login area is: ";
			$message .= "<a href='https://" . $_SERVER['HTTP_HOST'] . "' style='font-weight: bold; color: blue;' target='_blank'>https://" . $_SERVER['HTTP_HOST'] . "</a><br/><br/>";
			$message .= "<p style='color: #0000ff; font-weight: bold;'>Login Email: '" . $user_email . "'</p>";
			$message .= "<p style='color: #0000ff; font-weight: bold;'>Password: '" . $user_password . "'</p>";
			$message .= "If you need any help, please contact our support at ";
			$message .= "<span style='color: #FF0000;font-weight: bold;'>support@emailtools.com</span><br/><br/>Thank you,<br/><br/>JIMMY KIM";
			$this->email->message($message);
			if ($this->email->send()) {
				$rData = "success";
			} else {
				$rData = $this->email->print_debugger();
			}
		}
		$result = array(
			'result' => $rData
		);
		exit($this->sendJsonResponse($result));
	}
	
	public function retrieve()
	{
		$user_email = $this->getRequestPayloadParameter('user_email');
		$retrieveKey = time() . random_string('alnum', 16);
		$retrieveKey1 = base64_encode($retrieveKey);
		$result = $this->a_m->retrievePwd($user_email, $retrieveKey1);
		if ($result == "success") {
			$this->email->set_newline("\r\n");
			$this->email->from("login@emailtools.com");
			$this->email->to($user_email);
			$this->email->subject('EmailTools - Forget Your Password!');
			$message = "Hey,<br/><br/>Your Login Email is <span style='color: blue;font-weight: bold;'>" . $user_email . "</span>.<br/><br/>";
			$message .= "Click here to reset your password <br/>";
			$message .= "<a href='https://" . $_SERVER['HTTP_HOST'] . "/#/access/reset/" . $retrieveKey1 . "'";
			$message .= " style='font-weight: bold; color: blue;' target='_blank'>";
			$message .= "https://" . $_SERVER['HTTP_HOST'] . "/" . $retrieveKey1 . "</a><br/><br/>";
			$message .= "If you need any help, please contact our support at <span style='color: red;font-weight: bold;'>";
			$message .= "support@emailtools.com</span>";
			$message .= "<p>Regards, </p><p style='color: red;font-weight: bold;'>JIMMY KIM</p>";
			$this->email->message($message);
			if ($this->email->send()) {
				$rData = array(
					'result' => $result
				);
			} else {
				$rData = array(
					'result' => $this->email->print_debugger()
				);
			}
		} else {
			$rData = array(
				'result' => $result
			);
		}
		exit($this->sendJsonResponse($rData));
	}
	
	public function reset()
	{
		$retrieve_key = $this->getRequestPayloadParameter('retrieve_key');
		$user_password = $this->getRequestPayloadParameter('user_password');
		$result = $this->a_m->resetPassword($retrieve_key, $user_password);
		exit($this->sendJsonResponse($result));
	}
	
	public function deleteuser()
	{
		$user_key = $this->getRequestPayloadParameter('user_id');
		$this->a_m->deleteUser($user_key);
		$result = array(
			'result' => "success"
		);
		exit($this->sendJsonResponse($result));
	}
	
	public function uploadphoto()
	{
		$user_key = $this->getRequestPayloadParameter('user_id');
		$image = $this->getRequestPayloadParameter('photo');
		$target_path = $_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . 'photos/';
		$photo_file = $target_path . 'a0' . $user_key . '.png';
		$img = substr($image, strlen('data:image/png;base64,'));
		$data = str_replace(' ', '+', $img);
		$success = file_put_contents($photo_file, base64_decode($data));
		if ($success) {
			$result = array(
				'result' => "success"
			);
		} else {
			$result = array(
				'result' => "failure"
			);
		}
		exit($this->sendJsonResponse($result));
	}
	
	public function setesp()
	{
		$user_key = $this->getRequestPayloadParameter('user_id');
		$tbl_flag = $this->getRequestPayloadParameter('tbl_flag');
		$this->a_m->setEsp($user_key, $tbl_flag);
	}
	
}