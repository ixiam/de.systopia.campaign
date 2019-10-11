<?php
/*-------------------------------------------------------+
| CAMPAIGN MANAGER                                       |
| Copyright (C) 2015-2017                                |
| Author: M. Wire                                        |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/


/**
 * This class contains all campaign tree related functions that are called using AJAX (jQuery)
 */
class CRM_CampaignTree_Page_AJAX {

  /*
   * getCampaignList()
   * Retrieves AJAX data for list of campaigns
   *
   * @return: void
   */
  public static function getCampaignList() {
    $params = $_REQUEST;
    if (isset($params['parent_id'])) {
      // requesting child groups for a given parent
      $params['page'] = 1;
      $params['rp'] = 0;
      $campaigns = CRM_CampaignTree_BAO_Campaign::getCampaignListSelector($params);

      CRM_Utils_JSON::output($campaigns);
    }
    else {
      $sortMapper = array(
        0 => 'camp.title',
        1 => 'camp.description',
        2 => 'camp.start_date',
        3 => 'camp.end_date',
        4 => 'campaign_type_label',
        5 => 'campaign_status_label',
        6 => 'createdBy.sort_name',   
        7 => 'camp.external_identifier'
      );
      $sEcho = isset($_REQUEST['sEcho']) ? CRM_Utils_Type::escape($_REQUEST['sEcho'], 'Integer') : 1;
      $offset = isset($_REQUEST['iDisplayStart']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayStart'], 'Integer') : 0;
      $rowCount = isset($_REQUEST['iDisplayLength']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayLength'], 'Integer') : 25;
      $sort = isset($_REQUEST['iSortCol_0']) ? CRM_Utils_Array::value(CRM_Utils_Type::escape($_REQUEST['iSortCol_0'], 'Integer'), $sortMapper) : NULL;
      $sortOrder = isset($_REQUEST['sSortDir_0']) ? CRM_Utils_Type::escape($_REQUEST['sSortDir_0'], 'MysqlOrderByDirection') : 'asc';

      if ($sort && $sortOrder) {
        $params['sortBy'] = $sort . ' ' . $sortOrder;
      }

      // Search mode - some search parameters it makes sense to use tree view, some it does not
      // When displayed in flatSearch there may be duplicates in the list
      $params['rootOnly'] = 1;

      $flatSearch = array(
        'title', // name
        'description',
        'start_date',
        'end_date',
        'show',
        'showActive',
        'type',
        'status',
        'created_by',
        'external_id',
      );
      foreach ($flatSearch as $p) {
        if (!empty($params[$p])) {
          $params['rootOnly'] = 0;
        }
      }

      $params['page'] = ($offset / $rowCount) + 1;
      $params['rp'] = $rowCount;

      // get campaign list
      $campaigns = CRM_CampaignTree_BAO_Campaign::getCampaignListSelector($params);
      $iFilteredTotal = $iTotal = $params['total'];

      $selectorElements = array(
        'name',
        'description',
        'start_date',
        'end_date',
        'type',
        'status',
        'created_by',
        'external_id',
        'links',
        'is_active',
        'class', // This one MUST always be at the end, as the js code in search.tpl looks for the class in the last element
      );

      header('Content-Type: application/json');
      echo CRM_Utils_JSON::encodeDataTableSelector($campaigns, $sEcho, $iTotal, $iFilteredTotal, $selectorElements);
      CRM_Utils_System::civiExit();
    }
  }
}
