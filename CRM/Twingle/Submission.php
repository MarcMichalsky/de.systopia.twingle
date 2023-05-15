<?php
/*------------------------------------------------------------+
| SYSTOPIA Twingle Integration                                |
| Copyright (C) 2018 SYSTOPIA                                 |
| Author: J. Schuppe (schuppe@systopia.de)                    |
+-------------------------------------------------------------+
| This program is released as free software under the         |
| Affero GPL license. You can redistribute it and/or          |
| modify it under the terms of this license which you         |
| can read by viewing the included agpl.txt or online         |
| at www.gnu.org/licenses/agpl.html. Removal of this          |
| copyright header is strictly prohibited without             |
| written permission from the original author(s).             |
+-------------------------------------------------------------*/

use CRM_Twingle_ExtensionUtil as E;

class CRM_Twingle_Submission {

  /**
   * The default ID of the "Work" location type.
   */
  const LOCATION_TYPE_ID_WORK = 2;

  /**
   * The option value name of the group type for newsletter subscribers.
   */
  const GROUP_TYPE_NEWSLETTER = 'Mailing List';

  /**
   * The option value for the contribution type for completed contributions.
   */
  const CONTRIBUTION_STATUS_COMPLETED = 'Completed';

  /**
   * The default ID of the "Employer of" relationship type.
   */
  const EMPLOYER_RELATIONSHIP_TYPE_ID = 5;

  /**
   * @param array &$params
   *   A reference to the parameters array of the submission.
   *
   * @param \CRM_Twingle_Profile $profile
   *   The Twingle profile to use for validation, defaults to the default
   *   profile.
   *
   * @throws \CiviCRM_API3_Exception
   *   When invalid parameters have been submitted.
   */
  public static function validateSubmission(&$params, $profile = NULL) {
    if (!$profile) {
      $profile = CRM_Twingle_Profile::createDefaultProfile();
    }

    // Validate donation rhythm.
    if (!in_array($params['donation_rhythm'], array(
      'one_time',
      'halfyearly',
      'quarterly',
      'yearly',
      'monthly',
    ))) {
      throw new CiviCRM_API3_Exception(
        E::ts('Invalid donation rhythm.'),
        'invalid_format'
      );
    }

    // Get the payment instrument defined within the profile, or return an error
    // if none matches (i.e. an unknown payment method was submitted).
    if (!$payment_instrument_id = $profile->getAttribute('pi_' . $params['payment_method'])) {
      throw new CiviCRM_API3_Exception(
        E::ts('Payment method could not be matched to existing payment instrument.'),
        'invalid_format'
      );
    }
    $params['payment_instrument_id'] = $payment_instrument_id;

    // Validate date for parameter "confirmed_at".
    if (!DateTime::createFromFormat('YmdHis', $params['confirmed_at'])) {
      throw new CiviCRM_API3_Exception(
        E::ts('Invalid date for parameter "confirmed_at".'),
        'invalid_format'
      );
    }

    // Validate date for parameter "user_birthdate".
    if (!empty($params['user_birthdate']) && !DateTime::createFromFormat('Ymd', $params['user_birthdate'])) {
      throw new CiviCRM_API3_Exception(
        E::ts('Invalid date for parameter "user_birthdate".'),
        'invalid_format'
      );
    }

    // Get the gender ID defined within the profile, or return an error if none
    // matches (i.e. an unknown gender was submitted).
    if (!empty($params['user_gender'])) {
      if (!$gender_id = $profile->getAttribute('gender_' . $params['user_gender'])) {
        throw new CiviCRM_API3_Exception(
          E::ts('Gender could not be matched to existing gender.'),
          'invalid_format'
        );
      }
      $params['gender_id'] = $gender_id;
    }

    // Validate custom fields parameter, if given.
    if (!empty($params['custom_fields'])) {
      if (is_string($params['custom_fields'])) {
          $params['custom_fields'] = json_decode($params['custom_fields'], TRUE);
      }
      if (!is_array($params['custom_fields'])) {
        throw new CiviCRM_API3_Exception(
          E::ts('Invalid format for custom fields.'),
          'invalid_format'
        );
      }
    }

    // Validate campaign_id, if given.
    if (!empty($params['campaign_id'])) {
      // Check whether campaign_id is a numeric string and cast it to an integer.
      if (is_numeric($params['campaign_id'])) {
        $params['campaign_id'] = intval($params['campaign_id']);
      }
      else {
        throw new CiviCRM_API3_Exception(
          E::ts('campaign_id must be a numeric string. '),
          'invalid_format'
        );
      }
      // Check whether given campaign_id exists and if not, unset the parameter.
      try {
        civicrm_api3(
          'Campaign',
          'getsingle',
          ['id' => $params['campaign_id']]
        );
      } catch (CiviCRM_API3_Exception $e) {
        unset($params['campaign_id']);
      }
    }
  }

  /**
   * Retrieves the contact matching the given contact data or creates a new
   * contact.
   *
   * @param string $contact_type
   *   The contact type to look for/to create.
   * @param array $contact_data
   *   Data to use for contact lookup/to create a contact with.
   * @param CRM_Twingle_Profile $profile
   *   Profile used for this process
   * @param array $submission
   *   Submission data
   *
   * @return int | NULL
   *   The ID of the matching/created contact, or NULL if no matching contact
   *   was found and no new contact could be created.
   * @throws \CiviCRM_API3_Exception
   *   When invalid data was given.
   */
  public static function getContact($contact_type, $contact_data, $profile, $submission = []) {
    // If no parameters are given, do nothing.
    if (empty($contact_data)) {
      return NULL;
    }

    // add xcm profile
    $xcm_profile = $profile->getAttribute('xcm_profile');
    if (!empty($xcm_profile)) {
      $contact_data['xcm_profile'] = $xcm_profile;
    }

    // add campaign, see issue #17
    CRM_Twingle_Submission::setCampaign($contact_data, 'contact', $submission, $profile);

    // Prepare values: country.
    if (!empty($contact_data['country'])) {
      if (is_numeric($contact_data['country'])) {
        // If a country ID is given, update the parameters.
        $contact_data['country_id'] = $contact_data['country'];
        unset($contact_data['country']);
      }
      else {
        // Look up the country depending on the given ISO code.
        $country = civicrm_api3('Country', 'get', array('iso_code' => $contact_data['country']));
        if (!empty($country['id'])) {
          $contact_data['country_id'] = $country['id'];
          unset($contact_data['country']);
        }
        else {
          throw new \CiviCRM_API3_Exception(
            E::ts('Unknown country %1.', array(1 => $contact_data['country'])),
            'invalid_format'
          );
        }
      }
    }

    // Prepare values: language.
    if (!empty($contact_data['preferred_language'])) {
      $mapping = CRM_Core_I18n_PseudoConstant::longForShortMapping();
      // Override the default mapping for German.
      $mapping['de'] = 'de_DE';
      $contact_data['preferred_language'] = $mapping[$contact_data['preferred_language']];
    }

    // If there is an external_identifier, we can try to find a corresponding
    // contact
    if (isset($contact_data['external_identifier']) && !empty($contact_data['external_identifier'])){
    try {
      $result = civicrm_api3('Contact', 'getsingle',
        ['external_identifier' => $contact_data['external_identifier']]);
      if ($result['is_error'] != 0) {
        throw Civicrm_API3_Exception($result['error_message']);
      }
      else {
        $contact_data['id'] = $result['contact_id'];
      }
    } catch (CiviCRM_API3_Exception $e) {
      # No matching contact was found, so do nothing
    }
  }

    // Pass to XCM.
    $contact_data['contact_type'] = $contact_type;
    $contact = civicrm_api3('Contact', 'getorcreate', $contact_data);
    if (empty($contact['id'])) {
      return NULL;
    }

    return $contact['id'];
  }

  /**
   * Shares an organisation's work address, unless the contact already has one.
   *
   * @param $contact_id
   *   The ID of the contact to share the organisation address with.
   * @param $organisation_id
   *   The ID of the organisation whose address to share with the contact.
   * @param $location_type_id
   *   The ID of the location type to use for address lookup.
   *
   * @return boolean
   *   Whether the organisation address has been shared with the contact.
   *
   * @throws \CiviCRM_API3_Exception
   *   When looking up or creating the shared address failed.
   */
  public static function shareWorkAddress($contact_id, $organisation_id, $location_type_id = self::LOCATION_TYPE_ID_WORK) {
    if (empty($organisation_id)) {
      // Only if organisation exists.
      return FALSE;
    }

    // Check whether organisation has a WORK address.
    $existing_org_addresses = civicrm_api3('Address', 'get', array(
      'contact_id'       => $organisation_id,
      'location_type_id' => $location_type_id));
    if ($existing_org_addresses['count'] <= 0) {
      // Organisation does not have a WORK address.
      return FALSE;
    }

    // Check whether contact already has a WORK address.
    $existing_contact_addresses = civicrm_api3('Address', 'get', array(
      'contact_id'       => $contact_id,
      'location_type_id' => $location_type_id));
    if ($existing_contact_addresses['count'] > 0) {
      // Contact already has a WORK address.
      return FALSE;
    }

    // Create a shared address.
    $address = reset($existing_org_addresses['values']);
    $address['contact_id'] = $contact_id;
    $address['master_id']  = $address['id'];
    unset($address['id']);
    civicrm_api3('Address', 'create', $address);
    return TRUE;
  }

  /**
   * Updates or creates an employer relationship between contact and
   * organisation.
   *
   * @param int $contact_id
   *   The ID of the employee contact.
   * @param int $organisation_id
   *   The ID of the employer contact.
   *
   * @throws \CiviCRM_API3_Exception
   */
  public static function updateEmployerRelation($contact_id, $organisation_id) {
    if (empty($contact_id) || empty($organisation_id)) {
      return;
    }

    // see if there is already one
    $existing_relationship = civicrm_api3('Relationship', 'get', array(
      'relationship_type_id' => self::EMPLOYER_RELATIONSHIP_TYPE_ID,
      'contact_id_a' => $contact_id,
      'contact_id_b' => $organisation_id,
      'is_active' => 1,
    ));

    if ($existing_relationship['count'] == 0) {
      // There is currently no (active) relationship between these contacts.
      $new_relationship_data = array(
        'relationship_type_id' => self::EMPLOYER_RELATIONSHIP_TYPE_ID,
        'contact_id_a' => $contact_id,
        'contact_id_b' => $organisation_id,
        'is_active' => 1,
      );

      civicrm_api3('Relationship', 'create', $new_relationship_data);
    }
  }

  /**
   * Check whether the CiviSEPA extension is installed and CiviSEPA
   * functionality is activated within the Twingle extension settings.
   *
   * @return bool
   * @throws \CiviCRM_API3_Exception
   */
  public static function civiSepaEnabled() {
    $sepa_extension = civicrm_api3('Extension', 'get', array(
      'full_name' => 'org.project60.sepa',
      'is_active' => 1,
    ));
    return
      Civi::settings()->get('twingle_use_sepa')
      && $sepa_extension['count'];
  }

  /**
   * Retrieves recurring contribution frequency attributes for a given donation
   * rhythm parameter value, according to a static mapping.
   *
   * @param string $donation_rhythm
   *   The submitted "donation_rhythm" paramter according to the API action
   *   specification.
   *
   * @return array
   *   An array with "frequency_unit" and "frequency_interval" keys, to be added
   *   to contribution parameter arrays.
   */
  public static function getFrequencyMapping($donation_rhythm) {
    $mapping = array(
      'halfyearly' => array(
        'frequency_unit' => 'month',
        'frequency_interval' => 6,
      ),
      'quarterly' => array(
        'frequency_unit' => 'month',
        'frequency_interval' => 3,
      ),
      'yearly' => array(
        'frequency_unit' => 'month',
        'frequency_interval' => 12,
      ),
      'monthly' => array(
        'frequency_unit' => 'month',
        'frequency_interval' => 1,
      ),
      'one_time' => array(),
    );

    return $mapping[$donation_rhythm];
  }

  /**
   * Retrieves the next possible cycle day for a SEPA mandate from a given start
   * date of the mandate, depending on CiviSEPA creditor configuration.
   *
   * @param string $start_date
   *   A string representing a date in the format "Ymd".
   *
   * @param int $creditor_id
   *   The ID of the CiviSEPA creditor to use for determining the cycle day.
   *
   * @return int
   *   The next possible day of this or the next month to start collecting.
   */
  public static function getSEPACycleDay($start_date, $creditor_id) {
    $buffer_days = (int) CRM_Sepa_Logic_Settings::getSetting("pp_buffer_days");
    $frst_notice_days = (int) CRM_Sepa_Logic_Settings::getSetting("batching.FRST.notice", $creditor_id);
    $earliest_rcur_date = strtotime("$start_date + $frst_notice_days days + $buffer_days days");

    // Find the next cycle day
    $cycle_days = CRM_Sepa_Logic_Settings::getListSetting("cycledays", range(1, 28), $creditor_id);
    $earliest_cycle_day = $earliest_rcur_date;
    while (!in_array(date('j', $earliest_cycle_day), $cycle_days)) {
      $earliest_cycle_day = strtotime("+ 1 day", $earliest_cycle_day);
    }

    return date('j', $earliest_cycle_day);
  }

  /**
   * Will set the campaign_id to the entity_data set, if the
   * profile is configured to do so. In that case the campaign is taken
   * from the submission data. Should that be empty, the profile's default
   * campaign is used.
   *
   * @param array $entity_data
   *   the data set where the campaign_id should be set
   * @param string $context
   *   defines the type of the entity_data: one of 'contribution', 'membership','mandate', 'recurring', 'contact'
   * @param array $submission
   *   the submitted data
   * @param CRM_Twingle_Profile $profile
   *   the twingle profile used
   */
  public static function setCampaign(&$entity_data, $context, $submission, $profile) {
    // first: make sure it's not set from other workflows
    unset($entity_data['campaign_id']);

    // then: check if campaign should be set it this context
    $enabled_contexts = $profile->getAttribute('campaign_targets');
    if ($enabled_contexts === null || !is_array($enabled_contexts)) {
      // backward compatibility:
      $enabled_contexts = ['contribution', 'contact'];
    }
    if (in_array($context, $enabled_contexts)) {
      // use the submitted campaign if set
      if (!empty($submission['campaign_id'])) {
        $entity_data['campaign_id'] = $submission['campaign_id'];
      } // or use the submitted internal_project_id if set
      elseif (!empty($submission['internal_project_id'])) {
        $entity_data['campaign_id'] = $submission['internal_project_id'];
      }
      // otherwise use the profile's
      elseif (!empty($campaign = $profile->getAttribute('campaign'))) {
        $entity_data['campaign_id'] = $campaign;
      }
    }
  }
}
