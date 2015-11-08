<?php

/**
 * CampaignDetails.CampaignDetailsActivity API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_campaign_details_activity_spec(&$spec) {
  $spec['magicword']['api.required'] = 1;
}

/**
 * CampaignDetails.CampaignDetailsActivity API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_campaign_details_activity_get() {
  $query = "
            SELECT id,title, is_active,page_type,goal_amount,pcp_block_id
            FROM civicrm_pcp ";    
  $dao = CRM_Core_DAO::executeQuery($query);
  $result = array();

  
  while($dao->fetch()) {
    $results[$dao->id]['title'] = $dao->title;
    $results[$dao->id]['is_active'] = $dao->is_active ? 'Active' : 'Inactive';
    $results[$dao->id]['page_type']  = getContributionPageTitle($dao->id,$dao->page_type);       
    $results[$dao->id]['goal_amount'] = CRM_Utils_Money::format($dao->goal_amount, $dao->currency); 
    $contributionDetails = getContributionDetails($dao->id);       
    $results[$dao->id]['amout_raised'] = CRM_Utils_Money::format(0, $dao->currency);
    $results[$dao->id]['no_of_contributions'] = 0;

    if($contributionDetails){
      $results[$dao->id]['amout_raised'] = CRM_Utils_Money::format($contributionDetails[0], $dao->currency);
      $results[$dao->id]['no_of_contributions'] = $contributionDetails[1];
    }

    $results[$dao->id]['view_page_link']  = CRM_Utils_System::url('civicrm/pcp/info',
                                    'reset=1&id='.$dao->id.'&component='.$dao->page_type);
    $results[$dao->id]['edit_page_link']  = CRM_Utils_System::url( 'civicrm/pcp/info',
                                        "action=update&reset=1&id=$dao->id&context=dashboard");
  }
  
  return civicrm_api3_create_success($results);  
}


/**
  * Function to get the contribution details
  *
  * @param $pcpId INT
  *
  * @return array()
  **/
  function getContributionDetails($pcpId) {
      $query = "
                SELECT SUM(cc.total_amount) as total,count(*) as total_contributions
                FROM civicrm_pcp pcp
                LEFT JOIN civicrm_contribution_soft cs ON ( pcp.id = cs.pcp_id )
                LEFT JOIN civicrm_contribution cc ON ( cs.contribution_id = cc.id)
                WHERE pcp.id = %1 AND cc.contribution_status_id =1 AND cc.is_test = 0";
      $params = array(1 => array($pcpId, 'Integer'));
      $dao = CRM_Core_DAO::executeQuery($query, $params);
      if ($dao->fetch()) {
          return array($dao->total, $dao->total_contributions);
      }

      return array();
      
  }


  /**
   * Obtain the title of page associated with a pcp.
   *
   * @param int $pcpId
   * @param $component
   *
   * @return int
  */
  function getContributionPageTitle($pcpId, $component) {
    if ($component == 'contribute') {
      $query = "
      SELECT cp.title
      FROM civicrm_pcp pcp
      LEFT JOIN civicrm_contribution_page as cp ON ( cp.id =  pcp.page_id )
      WHERE pcp.id = %1";
    }
    elseif ($component == 'event') {
      $query = "
      SELECT ce.title
      FROM civicrm_pcp pcp
      LEFT JOIN civicrm_event as ce ON ( ce.id =  pcp.page_id )
      WHERE pcp.id = %1";
    }

    $params = array(1 => array($pcpId, 'Integer'));
    return CRM_Core_DAO::singleValueQuery($query, $params);
  }