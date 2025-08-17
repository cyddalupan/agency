<?php //-->
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Model extends CI_Model  {
	
	public $sessionName = [];
	protected $_post 	= [];
	
    public function __construct()  
	{
        parent::__construct();	
		
		$this->_post 		= $this->input->post();
		$this->sessionName 	= $this->config->item('sess_cookie_name');
    }
	
	public function getMeta($parent, $type, $key)
	{
		$condition = [
			'meta_parent'	=> $parent,
			'meta_type'		=> $type,
			'meta_key'		=> $key,
		];
		
		$this->db->flush_cache();
		$meta = $this->db->from('meta')
			->where($condition)
			->get()->row_array();
		
		if (empty($meta)) {
			return NULL;
		}
		
		return $meta['meta_value'];
	}
	
	public function setMeta($parent, $type, $key, $limit)
	{
		$condition = [
			'meta_parent'	=> $parent,
			'meta_type'		=> $type,
			'meta_key'		=> $key,
		];
		
		$data = [
			'meta_value' => $limit,
		];
		
		$this->db->flush_cache();
		
		return $this->db->where($condition)
			->update('meta', $data);
	}
	
	public function addMeta($parent, $type, $key, $limit)
	{
		$data = [
			'meta_parent'	=> $parent,
			'meta_type'		=> $type,
			'meta_key'		=> $key,
			'meta_value'	=> $limit,
		];
		
		$this->db->flush_cache();
		
		return $this->db->insert('meta', $data);
	}
	
	protected function getMetas($parent, $type, $key = false) 
	{
		$metas = $attributes = [];
		
		$condition = [
			'meta_parent' 	=> $parent,
			'meta_type'		=> $type,
		];
		
		if ($key !== false) { $condition['meta_key'] = $key;}
		
		//Get Metas/Attributes
		$metas = $this->db->from('meta')
			->where( $condition )
			->get()->result_array();
		
		//Append metas to $attributes
		foreach ( $metas as $key => $meta ) {
			if ( isset( $attributes[$meta['meta_key']] ) ) {
				if ( ! is_array($attributes[$meta['meta_key']] ) ) {
					$attributes[$meta['meta_key']] = [];	
				}
				
				$attributes[$meta['meta_key']][] = $meta['meta_value'];
				continue;		
			}
			
			$attributes[$meta['meta_key']] = $meta['meta_value'];
		}
		
		return $attributes;
	}
    
    protected function indexArray( &$stack, $key, $value = null )
    {
        $arrayIndexed = [];
		foreach ( $stack as $item ) {
			$arrayIndexed[ $item[$key] ] = ! is_null( $value ) ? $item[$value] : $item;
		}
        
        $stack = $arrayIndexed;
        return $stack;
    }
	
	protected function setDBQueryOptions( $options )
	{
		$methods = ['where', 'like', 'or_like', 'having'];

		foreach ( $options as $key => $items ) {
			if ( ! in_array( $key, $methods ) ) {
				throw new Exception( 'Invalid method DB Option - '.$key );
				exit;
			}
			
			foreach ( $items as $option ) {
				//Bold option
				if ( ! is_array( $option ) ) {
					$this->db->{$key}( $option, NULL, FALSE);
					continue;
				}
				
				$this->db->{$key}( $option );
			}
		}	
		
		return $this;
	}
	
	protected function setDBQueryRange( $limit, $offset ) 
	{
		if ($limit > 0) {
			$this->db->limit( $limit, $offset );
		}
		
		return $this;
	}
	
	protected function setDBQueryOrders( $orders ) 
	{
		if ( $orders === false || empty( $orders ) ) { return $e; }
		
		$isArray = isset( $orders[0] ) && is_array( $orders[0] );
		
		if ( $isArray ) {
			foreach ( $orders as $key => $order ) {
				$this->db->order_by( $order[0], $order[1] );
			}
			
			return $this;
		} 
		
		$this->db->order_by( $orders[0], $orders[1] );
		
		return $this;
	}
}

abstract class Schema_Model extends CI_Model  {
    
    protected static $TABLE    = '';
    protected static $TABLE_PK = '';
    
    public function __construct( $table, $tablePK )
    {
        parent::__construct();
        
        self::$TABLE    = $table;
        self::$TABLE_PK = $tablePK;
    }
    
    public function all()
    {
        $this->db->flush_cache();
        $this->db->from( self::$TABLE );
        
        return $this->db->get()->result_array();
    }
    
    public function find( $id )
    {
        $this->db->flush_cache();
        $this->db->from( self::$TABLE )
            ->where([
                self::$TABLE_PK => $id,
            ]);
        
        return $this->db->get()->row_array();
    }
}
















