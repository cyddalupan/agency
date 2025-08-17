<?php //-->
/*
 * This file is part a custom application package.
 * (c) 2014 Clemente Quiñones Jr. <clemquinones@gmail.com>
 */

/**
 * Core Knowledge of all pages
 *
 * @author     Clemente Quiñones Jr. <clemquinones@gmail.com>
 * @version    1.0.0
 */
 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class m_marketing_agent extends MY_Model {
 	/* Constants
	-------------------------------*/
	/* Public Properties
	-------------------------------*/
	/* Protected Properties
	-------------------------------*/
	/* Private Properties
	-------------------------------*/
	/* Get
	-------------------------------*/
	/* Magic
	-------------------------------*/ 
	public function __construct() 
	{
		parent::__construct(); 
	}
	
	/* Public Methods
	-------------------------------*/
	/* Protected Methods
	-------------------------------*/
	public function getMarketingAgents( $options = [], $limit = 0, $offset = 0, $sort = ['agent_first', 'ASC'] )
	{
		$this->db->flush_cache();
		$this->db->select( '*, 
			(SELECT COUNT(*) FROM `employer` WHERE `employer_source_agent` = `agent_id`) AS `employers`', false)
			->from('marketing_agent')
            ->where('agent_agency IS NULL', null, false);

		$this->setDBQueryOptions( $options )
			->setDBQueryRange( $limit, $offset)
			->setDBQueryOrders( $sort );

		$agents = $this->db->get()->result_array();
		
        return $this->indexArray( $agents, 'agent_id' );
	}

	public function getMarketingAgentsCount( $options = [])
	{
		$this->db->flush_cache();
		$this->db->from('marketing_agent');

		$this->setDBQueryOptions( $options );

		$agents = $this->db->count_all_results();

		return $agents;
	}
	
	public function getMarketingAgentById( $agentId )
	{
		$this->db->flush_cache();
		$this->db->select( '*, 
			(SELECT COUNT(*) FROM `employer` WHERE `employer_source_agent` = `agent_id`) AS `employers`', false)
			->from('marketing_agent')
			->where([
				'agent_id'	=> $agentId,
			])
            ->where( 'agent_agency IS NULL', null, false );

		$agent = $this->db->get()->row_array();

		return $agent;
	}
    
    public function getCommissions( $agentId, $options = [], $limit = 0, $offset = 0, $sort = ['commission_created', 'ASC'] )
    {
        $this->db->flush_cache();
        $this->db->from( 'commission_employer' )
            ->where([
                'commission_agent' => $agentId,
            ]);
        
        $this->setDBQueryOptions( $options )
            ->setDBQueryRange( $limit, $offset )
            ->setDBQueryOrders( $sort );
        
        $commissions = $this->db->get()->result_array();
        
        return $this->indexArray( $commissions, 'commission_id' );
    }
	
	public function addMarketingAgent()
	{
		$agent = $_POST['agent'];
		$agentData		= [];

		//Start Transaction
		$this->db->trans_begin();
		
		//Insert Marketing Agency
		$agentData = [
            'agent_first'      => ucwords( $agent['first'] ),
            'agent_last'       => ucwords( $agent['last'] ),
            'agent_contacts'   => $agent['contacts'],
            'agent_email'      => $agent['email'],
            'agent_createdby'  => $_SESSION['admin']['user']['user_id'],
            'agent_updatedby'  => $_SESSION['admin']['user']['user_id'],
            'agent_created'    => date( 'Y-m-d H:i:s', time() ),
            'agent_updated'    => date( 'Y-m-d H:i:s', time() ),
		];
		
		$agentInserted	= $this->db->insert('marketing_agent', $agentData);
		$agentId 			= $this->db->insert_id();
		//endOf: Insert Marketing Agency
		
		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $agentInserted) {
			$this->db->trans_rollback();
			return false;
		} 
		
		$this->endProcess();
		
		//Commit transaction
		$this->db->trans_commit();	
		
		$agent = $this->getMarketingAgentById( $agentId );
		
		return $agent;
	}
	
	public function updateMarketingAgent( $agentId )
	{
		$agent         = $_POST['agent'];
		$agentData		= [];

		//Start Transaction
		$this->db->trans_begin();
		
		//Update Marketing Agency
		$agentData = [
            'agent_first'      => ucwords( $agent['first'] ),
            'agent_last'       => ucwords( $agent['last'] ),
            'agent_contacts'   => $agent['contacts'],
            'agent_email'      => $agent['email'],
            'agent_updatedby'  => $_SESSION['admin']['user']['user_id'],
            'agent_updated'    => date( 'Y-m-d H:i:s', time() ),
		];
		
		$agentUpdated = 
		$this->db->where([
				'agent_id' => $agentId,
			])
			->update('marketing_agent', $agentData);
		//endOf: Update Category
		
		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $agentUpdated) {
			$this->db->trans_rollback();
			return false;
		} 
		
		$this->endProcess();
		
		//Commit transaction
		$this->db->trans_commit();	
		
		$agent = $this->getMarketingAgentById( $agentId );

		return $agent;
	}
	
	protected function endProcess()
	{
		if ( isset( $_SESSION['post']['admin']['marketing-agents/add'] ) ) {
			unset( $_SESSION['post']['admin']['marketing-agents/add'] );
		}
		
		if ( isset( $_SESSION['post']['admin']['marketing-agents/edit'] ) ) {
			unset( $_SESSION['post']['admin']['marketing-agents/edit'] );
		}

		return $this;
	}
	
	/* Private Methods
	-------------------------------*/
}
