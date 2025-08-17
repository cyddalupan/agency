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

class m_recruitment_agent extends MY_Model {
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
	public function getRecruitmentAgents( $options = [], $limit = 0, $offset = 0, $sort = ['agent_first', 'ASC'] )
	{
		$this->db->flush_cache();
		$this->db->select('*, 
			(SELECT COUNT(*) FROM `applicant` WHERE `applicant_source` = `agent_id`) AS `applicants`', false)
			->from('recruitment_agent');

		$this->setDBQueryOptions( $options )
			->setDBQueryRange( $limit, $offset)
			->setDBQueryOrders( $sort );

		$agents = $this->db->get()->result_array();
        
        return $this->indexArray( $agents, 'agent_id' );
	}

	public function getRecruitmentAgentsCount( $options = [])
	{
		$this->db->flush_cache();
		$this->db->from('recruitment_agent');

		$this->setDBQueryOptions( $options );

		$agents = $this->db->count_all_results();

		return $agents;
	}
	
	public function getRecruitmentAgentById( $agentId )
	{
		$this->db->flush_cache();
		$this->db->select( '*, 
			(SELECT COUNT(*) FROM `applicant` WHERE `applicant_source` = `agent_id`) AS `applicants`', false)
			->from('recruitment_agent')
			->where([
				'agent_id'	=> $agentId,
			]);

		$agent = $this->db->get()->row_array();

		return $agent;
	}
	
	public function addRecruitmentAgent()
	{
		$agent = $_POST['agent'];
		$agentData		= [];

		//Start Transaction
		$this->db->trans_begin();
		
		//Insert Recruitment Agency
		$agentData = [
            'agent_first'      => ucwords( $agent['first'] ),
            'agent_last'       => ucwords( $agent['last'] ),
            'agent_contacts'   => $agent['contactsss'],
		
			'agent_remarks'      => $agent['agent_remarks'],
            'agent_email'      => $agent['email'],
			'branch_type'      => $agent['branch_type'],
            'agent_createdby'  => $_SESSION['admin']['user']['user_id'],
            'agent_updatedby'  => $_SESSION['admin']['user']['user_id'],
            'agent_created'    => date( 'Y-m-d H:i:s', time() ),
            'agent_updated'    => date( 'Y-m-d H:i:s', time() ),
		];
		
		$agentInserted	= $this->db->insert('recruitment_agent', $agentData);
		$agentId 			= $this->db->insert_id();
		//endOf: Insert Recruitment Agency
		
		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $agentInserted) {
			$this->db->trans_rollback();
			return false;
		} 
		
		$this->endProcess();
		
		//Commit transaction
		$this->db->trans_commit();	
		
		$agent = $this->getRecruitmentAgentById( $agentId );
		
		return $agent;
	}
	
	public function updateRecruitmentAgent( $agentId )
	{
		$agent         = $_POST['agent'];
		$agentData		= [];

		//Start Transaction
		$this->db->trans_begin();
		
		//Update Recruitment Agency
		$agentData = [
            'agent_first'      => ucwords( $agent['first'] ),
            'agent_last'       => ucwords( $agent['last'] ),
            'agent_contacts'   => $agent['contacts'],
            'agent_email'      => $agent['email'],

			'branch_type'      => $agent['branch_type'],
	
			'agent_remarks'      => $agent['agent_remarks'],
            'agent_updatedby'  => $_SESSION['admin']['user']['user_id'],
            'agent_updated'    => date( 'Y-m-d H:i:s', time() ),
		];
		
		$agentUpdated = 
		$this->db->where([
				'agent_id' => $agentId,
			])
			->update('recruitment_agent', $agentData);
		//endOf: Update Category
		
		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $agentUpdated) {
			$this->db->trans_rollback();
			return false;
		} 
		
		$this->endProcess();
		
		//Commit transaction
		$this->db->trans_commit();	
		
		$agent = $this->getRecruitmentAgentById( $agentId );

		return $agent;
	}
	
	protected function endProcess()
	{
		if ( isset( $_SESSION['post']['admin']['recruitment-agents/add'] ) ) {
			unset( $_SESSION['post']['admin']['recruitment-agents/add'] );
		}
		
		if ( isset( $_SESSION['post']['admin']['recruitment-agents/edit'] ) ) {
			unset( $_SESSION['post']['admin']['recruitment-agents/edit'] );
		}

		return $this;
	}
	
	/* Private Methods
	-------------------------------*/
}
