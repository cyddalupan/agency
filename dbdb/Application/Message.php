<?php //-->

namespace Application;

class Message {
		
	public static function add($type, $message, $timeout = false, $title = false)
	{
		self::startSession();
		
		$_SESSION['alerts'][] = [
			'type'		=> $type,
			'message'	=> $message,
			'timeout'	=> $timeout,
			'title'		=> $title,
		];
	}
	
	public static function addSuccess($message, $timeout = false, $title = false)
	{
		self::startSession();
		
		$_SESSION['alerts'][] = [
			'type'		=> 'success',
			'message'	=> $message,
			'timeout'	=> $timeout,
			'title'		=> $title,
		];
	}	
	
	public static function addInfo($message, $timeout = false, $title = false)
	{
		self::startSession();
		
		$_SESSION['alerts'][] = [
			'type'		=> 'info',
			'message'	=> $message,
			'timeout'	=> $timeout,
			'title'		=> $title,
		];
	}
	
	public static function addWarning($message, $timeout = false, $title = false)
	{
		self::startSession();
		
		$_SESSION['alerts'][] = [
			'type'		=> 'warning',
			'message'	=> $message,
			'timeout'	=> $timeout,
			'title'		=> $title,
		];
	}
	
	public static function addDanger($message, $timeout = false, $title = false)
	{
		self::startSession();
		
		$_SESSION['alerts'][] = [
			'type'		=> 'danger',
			'message'	=> $message,
			'timeout'	=> $timeout,
			'title'		=> $title,
		];
	}
	
	public static function addDefault($message, $timeout = false, $title = false)
	{
		self::startSession();
		
		$_SESSION['alerts'] = [
			'type'		=> 'default',
			'message'	=> $message,
			'timeout'	=> $timeout,
			'title'		=> $title,
		];
	}

	public static function addModal($type, $message, $title = false)
	{
		self::startSession();
		
		$_SESSION['modals'] = [
			'type'		=> $type,
			'message'	=> $message,
			'timeout'	=> false,
			'title'		=> $title,
		];
	}

	public static function addModalSuccess($message, $title = false)
	{
		self::startSession();
		
		$_SESSION['modals'][] = [
			'type'		=> 'success',
			'message'	=> $message,
			'timeout'	=> false,
			'title'		=> $title,
		];
	}

	public static function addModalInfo($message, $timeout = false, $title = false)
	{
		self::startSession();
		
		$_SESSION['modals'][] = [
			'type'		=> 'info',
			'message'	=> $message,
			'timeout'	=> $timeout,
			'title'		=> $title,
		];
	}
	
	public static function addModalWarning($message, $timeout = false, $title = false)
	{
		self::startSession();
		
		$_SESSION['modals'][] = [
			'type'		=> 'warning',
			'message'	=> $message,
			'timeout'	=> $timeout,
			'title'		=> $title,
		];
	}
	
	public static function addModalDanger($message, $timeout = false, $title = false)
	{
		self::startSession();
		
		$_SESSION['modals'][] = [
			'type'		=> 'danger',
			'message'	=> $message,
			'timeout'	=> $timeout,
			'title'		=> $title,
		];
	}
	
	public static function addModalDefault($message, $timeout = false, $title = false)
	{
		self::startSession();
		
		$_SESSION['modals'][] = [
			'type'		=> 'default',
			'message'	=> $message,
			'timeout'	=> $timeout,
			'title'		=> $title,
		];
	}
	
	public static function all()
	{
		self::startSession();

		$messages = [];
		
		if ( isset( $_SESSION['alerts'] ) ) {
			$messages['alerts'] = $_SESSION['alerts'];
			unset( $_SESSION['alerts'] );
		}
		
		if ( isset( $_SESSION['modals'] ) ) {
			$messages['modals'] = $_SESSION['modals'];
			unset( $_SESSION['modals'] );
		}
		
		return $messages;
	}
	
	protected static function startSession()
	{
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
	}
}