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

/**
 * TwingleDonation.Submit API specification
 * This is used for documentation and validation.
 *
 * @param array $params
 *   Description of fields supported by this API call.
 *
 * @return void
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_twingle_donation_Submit_spec(&$params) {
  $params['project_id'] = array(
    'name' => 'project_id',
    'title' => 'Project ID',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 1,
    'description' => 'The Twingle project ID.',
  );
  $params['trx_id'] = array(
    'name' => 'trx_id',
    'title' => 'Transaction ID',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 1,
    'description' => 'The unique transaction ID of the donation',
  );
  $params['confirmed_at'] = array(
    'name'         => 'confirmed_at',
    'title'        => 'Confirmed at',
    'type'         => CRM_Utils_Type::T_INT,
    'api.required' => 1,
    'description'  => 'The date when the donation was issued, format: YYYYMMDD.',
  );
  $params['purpose'] = array(
    'name'         => 'purpose',
    'title'        => 'Purpose',
    'type'         => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'description'  => 'The purpose of the donation.',
  );
  $params['amount'] = array(
    'name'         => 'amount',
    'title'        => 'Amount',
    'type'         => CRM_Utils_Type::T_INT,
    'api.required' => 1,
    'description'  => 'The donation amount in minor currency unit.',
  );
  $params['currency'] = array(
    'name'         => 'currency',
    'title'        => 'Currency',
    'type'         => CRM_Utils_Type::T_STRING,
    'api.required' => 1,
    'description'  => 'The ISO-4217 currency code of the donation.',
  );
  $params['newsletter'] = array(
    'name'         => 'newsletter',
    'title'        => 'Newsletter',
    'type'         => CRM_Utils_Type::T_BOOLEAN,
    'api.required' => 0,
    'description'  => 'Whether to subscribe the contact to the newsletter group defined in the profile.',
  );
  $params['postinfo'] = array(
    'name'         => 'postinfo',
    'title'        => 'Postal mailing',
    'type'         => CRM_Utils_Type::T_BOOLEAN,
    'api.required' => 0,
    'description'  => 'Whether to subscribe the contact to the postal mailing group defined in the profile.',
  );
  $params['donation_receipt'] = array(
    'name'         => 'donation_receipt',
    'title'        => 'Donation receipt',
    'type'         => CRM_Utils_Type::T_BOOLEAN,
    'api.required' => 0,
    'description'  => 'Whether the contact requested a donation receipt.',
  );
  $params['payment_method'] = array(
    'name'         => 'payment_method',
    'title'        => 'Payment method',
    'type'         => CRM_Utils_Type::T_STRING,
    'api.required' => 1,
    'description'  => 'The Twingle payment method used for the donation.',
  );
  $params['donation_rhythm'] = array(
    'name'         => 'donation_rhythm',
    'title'        => 'Donation rhythm',
    'type'         => CRM_Utils_Type::T_STRING,
    'api.required' => 1,
    'description'  => 'The interval which the donation is recurring in.',
  );
  $params['debit_iban'] = array(
    'name'         => 'debit_iban',
    'title'        => 'SEPA IBAN',
    'type'         => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'description'  => 'The IBAN for SEPA Direct Debit payments, conforming with ISO 13616-1:2007.',
  );
  $params['debit_bic'] = array(
    'name'         => 'debit_bic',
    'title'        => 'SEPA BIC',
    'type'         => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'description'  => 'The BIC for SEPA Direct Debit payments, conforming with ISO 9362.',
  );
  $params['debit_mandate_reference'] = array(
    'name'         => 'debit_mandate_reference',
    'title'        => 'SEPA Direct Debit Mandate reference',
    'type'         => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'description'  => 'The mandate for SEPA Direct Debit payments.',
  );
  $params['debit_account_holder'] = array(
    'name'         => 'debit_account_holder',
    'title'        => 'SEPA Direct Debit Account holder',
    'type'         => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'description'  => 'The mandate for SEPA Direct Debit payments.',
  );
  $params['is_anonymous'] = array(
    'name'         => 'is_anonymous',
    'title'        => 'Anonymous donation',
    'type'         => CRM_Utils_Type::T_BOOLEAN,
    'api.required' => 0,
    'api.default'  => 0,
    'description'  => 'Whether the donation is submitted anonymously.',
  );
  $params['user_gender'] = array(
    'name'         => 'user_gender',
    'title'        => 'Gender',
    'type'         => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'description'  => 'The gender of the contact.',
  );
  $params['user_birthdate'] = array(
    'name'         => 'user_birthdate',
    'title'        => 'Date of birth',
    'type'         => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'description'  => 'The date of birth of the contact, format: YYYYMMDD.',
  );
  $params['user_title'] = array(
    'name'         => 'user_title',
    'title'        => 'Formal title',
    'type'         => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'description'  => 'The formal title of the contact.',
  );
  $params['user_email'] = array(
    'name'         => 'user_email',
    'title'        => 'E-mail address',
    'type'         => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'description'  => 'The e-mail address of the contact.',
  );
  $params['user_firstname'] = array(
    'name'         => 'user_firstname',
    'title'        => 'First name',
    'type'         => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'description'  => 'The first name of the contact.',
  );
  $params['user_lastname'] = array(
    'name'         => 'user_lastname',
    'title'        => 'Last name',
    'type'         => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'description'  => 'The last name of the contact.',
  );
  $params['user_street'] = array(
    'name'         => 'user_street',
    'title'        => 'Street address',
    'type'         => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'description'  => 'The street address of the contact.',
  );
  $params['user_postal_code'] = array(
    'name'         => 'user_postal_code',
    'title'        => 'Postal code',
    'type'         => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'description'  => 'The postal code of the contact.',
  );
  $params['user_city'] = array(
    'name'         => 'user_city',
    'title'        => 'City',
    'type'         => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'description'  => 'The city of the contact.',
  );
  $params['user_telephone'] = array(
    'name'         => 'user_telephone',
    'title'        => 'Telephone',
    'type'         => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'description'  => 'The telephone number of the contact.',
  );
  $params['user_company'] = array(
    'name'         => 'user_company',
    'title'        => 'Company',
    'type'         => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'description'  => 'The company of the contact.',
  );
  $params['user_extrafield'] = array(
    'name'         => 'user_extrafield',
    'title'        => 'User extra field',
    'type'         => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'description'  => 'Additional information of the contact.',
  );
}

/**
 * TwingleDonation.Submit API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 */
function civicrm_api3_twingle_donation_Submit($params) {
  try {
    // Copy submitted parameters.
    $original_params = $params;

    // Get the profile defined for the given form ID, or the default profile
    // if none matches.
    $profile = CRM_Twingle_Profile::getProfileForProject($params['project_id']);

    // Validate submitted parameters
    CRM_Twingle_Submission::validateSubmission($params, $profile);

    // Do not process an already existing contribution with the given
    // transaction ID.
    $contribution = civicrm_api3('Contribution', 'get', array(
      'trxn_id' => $params['trx_id']
    ));
    if ($contribution['count'] > 0) {
      throw new CiviCRM_API3_Exception(
        E::ts('Contribution with the given transaction ID already exists.'),
        'api_error'
      );
    }

    // Create contact(s).
    if ($params['is_anonymous']) {
      // Retrieve the ID of the contact to use for anonymous donations defined
      // within the profile
      $contact_id = civicrm_api3('Contact', 'getsingle', array(
        'id' => $profile->getAttribute('anonymous_contact_id'),
      ))['id'];
    }
    else {
      // Prepare parameter mapping for address.
      foreach (array(
                 'user_street' => 'street_address',
                 'user_postal_code' => 'postal_code',
                 'user_city' => 'city',
               ) as $address_param => $address_component) {
        if (!empty($params[$address_param])) {
          $params[$address_component] = $params[$address_param];
          if ($address_param != $address_component) {
            unset($params[$address_param]);
          }
        }
      }

      // Prepare parameter mapping for organisation.
      if (!empty($params['user_company'])) {
        $params['organization_name'] = $params['user_company'];
        unset($params['user_company']);
      }

      // Remove parameter "id".
      if (!empty($params['id'])) {
        unset($params['id']);
      }

      // Add location type to parameters.
      $params['location_type_id'] = (int) $profile->getAttribute('location_type_id');

      // Exclude address for now when retrieving/creating the individual contact
      // and an organisation is given, as we are checking organisation address
      // first and share it with the individual.
      if (!empty($params['organization_name'])) {
        $submitted_address = array();
        foreach (array(
                   'street_address',
                   'postal_code',
                   'city',
                   'country',
                   'location_type_id',
                 ) as $address_component) {
          if (!empty($params[$address_component])) {
            $submitted_address[$address_component] = $params[$address_component];
            unset($params[$address_component]);
          }
        }
      }

      // Get the ID of the contact matching the given contact data, or create a
      // new contact if none exists for the given contact data.
      $contact_data = array();
      foreach (array(
                 'user_firstname' => 'first_name',
                 'user_lastname' => 'last_name',
                 'gender_id' => 'gender_id',
                 'user_birthdate' => 'birth_date',
                 'user_email' => 'email',
                 'user_telephone' => 'phone',
               ) as $contact_param => $contact_component) {
        if (!empty($params[$contact_param])) {
          $contact_data[$contact_component] = $params[$contact_param];
        }
      }
      if (!$contact_id = CRM_Twingle_Submission::getContact(
        'Individual',
        $contact_data
      )) {
        throw new CiviCRM_API3_Exception(
          E::ts('Individual contact could not be found or created.'),
          'api_error'
        );
      }

      // Organisation lookup.
      if (!empty($params['organization_name'])) {
        $organisation_data = array(
          'organization_name' => $params['organization_name'],
        );
        if (!empty($submitted_address)) {
          $params += $submitted_address;
        }
        if (!$organisation_id = CRM_Twingle_Submission::getContact(
          'Organization',
          $organisation_data
        )) {
          throw new CiviCRM_API3_Exception(
            E::ts('Organisation contact could not be found or created.'),
            'api_error'
          );
        }
      }
      $address_shared = (
        isset($organisation_id)
        && CRM_Twingle_Submission::shareWorkAddress(
          $contact_id,
          $organisation_id,
          $params['location_type_id']
        )
      );

      // Address is not shared, use submitted address.
      if (!$address_shared && !empty($submitted_address)) {
        $submitted_address['contact_id'] = $contact_id;
        civicrm_api3('Address', 'create', $submitted_address);
      }

      // Create employer relationship between organization and individual.
      if (isset($organisation_id)) {
        CRM_Twingle_Submission::updateEmployerRelation($contact_id, $organisation_id);
      }
    }

    // Create contribution or SEPA mandate.
    $contribution_data = array(
      'contact_id' => (isset($organisation_id) ? $organisation_id : $contact_id),
      'currency' => $params['currency'],
      'trxn_id' => $params['trx_id'],
      'financial_type_id' => $profile->getAttribute('financial_type_id'),
      'payment_instrument_id' => $params['payment_instrument_id'],
      'amount' => $params['amount'],
      'total_amount' => $params['amount'],
    );
    if (!empty($params['purpose'])) {
      $contribution_data['note'] = $params['purpose'];
    }

    $sepa_extension = civicrm_api3('Extension', 'get', array(
      'full_name' => 'org.project60.sepa',
      'is_active' => 1,
    ));
    if ($sepa_extension['count'] && CRM_Sepa_Logic_Settings::isSDD($contribution_data)) {
      // If CiviSEPA is installed and the financial type is a CiviSEPA-one,
      // create SEPA mandate (and recurring contribution, using "createfull" API
      // action).
      $mandate_data = $contribution_data + array(
          'type' => ($params['donation_rhythm'] == 'one_time' ? 'OOFF' : 'RCUR'),
          'iban' => $params['debit_iban'],
          'bic' => $params['debit_bic'],
          'reference' => $params['debit_mandate_reference'],
          'date' => $params['confirmed_at'],
          'creditor_id' => $profile->getAttribute('sepa_creditor_id'),
        );
      $mandate = civicrm_api3('SepaMandate', 'createfull', $mandate_data);
      if ($mandate['is_error']) {
        throw new CiviCRM_API3_Exception(
          E::ts('Could not create SEPA mandate'),
          'api_error'
        );
      }
    }
    else {
      // Create (recurring) contribution.
      if ($params['donation_rhythm'] != 'one_time') {
        // Create recurring contribution first.
        $contribution_recur_data = $contribution_data + array(
            'frequency_interval' => '', // TODO
            'contribution_status_id' => 'Pending', // TODO: Or "In Progress"?
            'start_date' => $params['confirmed_at'],
          );
        $contribution_recur = civicrm_api3('contributionRecur', 'create', $contribution_recur_data);
        if ($contribution_recur['is_error']) {
          throw new CiviCRM_API3_Exception(
            E::ts('Could not create recurring contribution.'),
            'api_error'
          );
        }
        $contribution_data['contribution_recur_id'] = $contribution_recur['id'];
      }

      // Create contribution.
      $contribution_data += array(
        'contribution_status_id' => 'Completed',
        'receive_date' => $params['confirmed_at'],
      );
      $contribution = civicrm_api3('Contribution', 'create', $contribution_data);
      if ($contribution['is_error']) {
        throw new CiviCRM_API3_Exception(
          E::ts('Could not create contribution'),
          'api_error'
        );
      }
    }

    // TODO: Assemble return data.
    $result = civicrm_api3_create_success();
  }
  catch (CiviCRM_API3_Exception $exception) {
    $result = civicrm_api3_create_error($exception->getMessage());
  }

  return $result;
}