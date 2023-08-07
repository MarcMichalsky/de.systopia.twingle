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

declare(strict_types = 1);

use CRM_Twingle_ExtensionUtil as E;
use Civi\Twingle\Exceptions\ProfileException;
use Civi\Twingle\Exceptions\BaseException;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Twingle_Form_Profile extends CRM_Core_Form {

  /**
   * @var CRM_Twingle_Profile
   *
   * The profile object the form is acting on.
   */
  protected ?CRM_Twingle_Profile $profile = NULL;

  /**
   * The ID of this profile.
   * @var int|NULL
   */
  protected $profile_id = NULL;

  /**
   * @var string
   *
   * The operation to perform within the form.
   */
  protected $_op;

  /**
   * @var array<string, string>
   *
   * A static cache of retrieved payment instruments found within
   * self::getPaymentInstruments().
   */
  protected static $_paymentInstruments;

  /**
   * @var array<int, string>
   *
   * A static cache of retrieved contribution statuses found within
   * static::getContributionStatusOptions().
   */
  protected static array $_contributionStatusOptions;

  /**
   * @var array<int, string>
   *
   * A static cache of retrieved groups found within static::getGroups().
   */
  protected static array $_groups;

  /**
   * @var array<string, string>
   *
   * A static cache of retrieved newsletter groups found within
   * static::getNewsletterGroups().
   */
  protected static array $_newsletterGroups;

  /**
   * @var array<int, string>
   *
   * A static cache of retrieved campaigns found within static::getCampaigns().
   */
  protected static array $_campaigns;

  /**
   * @var array<int, string>
   *
   * A static cache of retrieved financial types found within
   * static::getFinancialTypes().
   */
  protected static array $_financialTypes;

  /**
   * @var array<int, string>
   *
   * A static cache of retrieved genders found within
   * static::getGenderOptions().
   */
  protected static array $_genderOptions;

  /**
   * @var array<int, string>
   *
   * A static cache of retrieved prefixes found within
   * static::getGenderOptions().
   */
  protected static array $_prefixOptions;

  /**
   * @var array<int, string>
   *
   * A static cache of retrieved location types found within
   * static::getLocationTypes().
   */
  protected static array $_locationTypes;

  /**
   * @var array<string, string>
   *
   * A static cache of retrieved location types found within
   * static::getXCMProfiles().
   */
  protected static array $_xcm_profiles;

  /**
   * @var array<int, string>
   *
   * A static cache of retrieved membership types found within
   * static::getMembershipTypes().
   */
  protected static array $_membershipTypes;

  /**
   * @var array<int, string>
   *
   * A static cache of retrieved CiviSEPA creditors found within
   * static::getSepaCreditors().
   */
  protected static array $_sepaCreditors;

  public function preProcess(): void {
    // "Create" is the default operation.
    $op = CRM_Utils_Request::retrieve('op', 'String', $this);
    $this->_op = is_string($op) ? $op : 'create';

    // Verify that a profile with the given id exists.
    if ($this->_op != 'copy' && $this->_op != 'create') {
      $this->profile_id = CRM_Utils_Request::retrieve('id', 'Int', $this);
      $this->profile = CRM_Twingle_Profile::getProfile($this->profile_id);
    }

    // Set redirect destination.
    $this->controller->_destination = CRM_Utils_System::url(
      'civicrm/admin/settings/twingle/profiles',
      'reset=1'
    );

    parent::preProcess();
  }

  /**
   * Builds the form structure.
   */
  public function buildQuickForm(): void {
    $profile_name = (isset($this->profile) ? $this->profile->getName() : NULL);

    switch ($this->_op) {
      case 'delete':
        if ($this->profile) {
          CRM_Utils_System::setTitle(E::ts('Delete Twingle API profile <em>%1</em>', [1 => $this->profile->getName()]));
          $this->addButtons([
            [
              'type' => 'submit',
              'name' => ($this->profile->getName() == 'default' ? E::ts('Reset') : E::ts('Delete')),
              'isDefault' => TRUE,
            ],
          ]);
        }
        parent::buildQuickForm();
        return;

      case 'copy':
        // Retrieve the source profile name.
        $source_id = CRM_Utils_Request::retrieve('source_id', 'Int', $this);
        // When copying without a valid profile id, copy the default profile.
        if (!$source_id) {
          $this->profile = CRM_Twingle_Profile::createDefaultProfile();
        } else {
          try {
            $source_profile = CRM_Twingle_Profile::getProfile($source_id);
            $this->profile = $source_profile->copy();
            $this->profile->validate();
          } catch (ProfileValidationError $e) {
            if ($e->getErrorCode() == ProfileValidationError::ERROR_CODE_PROFILE_VALIDATION_FAILED) {
              Civi::log()->error($e->getLogMessage());
              CRM_Core_Session::setStatus(E::ts('The profile is invalid and cannot be copied.'), E::ts('Error'));
              CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/admin/settings/twingle/profiles', 'reset=1'));
              return;
            }
          } catch (ProfileException $e) {
            if ($e->getErrorCode() == ProfileException::ERROR_CODE_PROFILE_NOT_FOUND) {
              Civi::log()->error($e->getLogMessage());
              CRM_Core_Session::setStatus(E::ts('The profile to be copied could not be found.'), E::ts('Error'));
              CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/admin/settings/twingle/profiles', 'reset=1'));
              return;
            }
          }
          catch (Civi\Core\Exception\DBQueryException $e) {
            Civi::log()->error($e->getMessage());
            CRM_Core_Session::setStatus(E::ts('A database error has occurred. See the log for details.'), E::ts('Error'));
            CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/admin/settings/twingle/profiles', 'reset=1'));
            return;
          }
        }
        CRM_Utils_System::setTitle(E::ts('New Twingle API profile'));
        break;

      case 'edit':
        CRM_Utils_System::setTitle(E::ts('Edit Twingle API profile <em>%1</em>', [1 => $this->profile->getName()]));
        break;

      case 'create':
        // Load factory default profile values.
        $this->profile = CRM_Twingle_Profile::createDefaultProfile(E::ts('New Profile'));
        CRM_Utils_System::setTitle(E::ts('New Twingle API profile'));
        break;
    }

    // Assign template variables.
    $this->assign('op', $this->_op);
    $this->assign('profile_name', $this->profile->getName());
    $this->assign('is_default', $this->profile->is_default());

    // Add form elements.
    $is_default = $profile_name == 'default';
    $this->add(
      ($is_default ? 'static' : 'text'),
      'name',
      E::ts('Profile name'),
      [],
      !$is_default
    );

    $this->add(
    // field type
      'text',
    // field name
      'selector',
    // field label
      E::ts('Project IDs'),
      ['class' => 'huge'],
    // is required
      TRUE
    );

    $this->add(
        'select',
        'xcm_profile',
        E::ts('Contact Matcher (XCM) Profile'),
        static::getXCMProfiles(),
        TRUE
    );

    $this->add(
      'select',
      'location_type_id',
      E::ts('Location type'),
      static::getLocationTypes(),
      TRUE
    );

    $this->add(
      'select',
      'location_type_id_organisation',
      E::ts('Location type for organisations'),
      static::getLocationTypes(),
      TRUE
    );

    $this->add(
    // field type
      'select',
    // field name
      'financial_type_id',
    // field label
      E::ts('Financial type'),
    // list of options
      static::getFinancialTypes(),
    // is required
      TRUE
    );
    $this->add(
    // field type
      'select',
    // field name
      'financial_type_id_recur',
    // field label
      E::ts('Financial type (recurring)'),
    // list of options
      static::getFinancialTypes(),
    // is required
      TRUE
    );

    $this->add(
      'select',
      'gender_male',
      E::ts('Gender option for submitted value "male"'),
      static::getGenderOptions(),
      TRUE
    );
    $this->add(
      'select',
      'gender_female',
      E::ts('Gender option for submitted value "female"'),
      static::getGenderOptions(),
      TRUE
    );
    $this->add(
      'select',
      'gender_other',
      E::ts('Gender option for submitted value "other"'),
      static::getGenderOptions(),
      TRUE
    );

    $this->add(
        'select',
        'prefix_male',
        E::ts('Prefix option for submitted value "male"'),
        static::getPrefixOptions()
    );
    $this->add(
        'select',
        'prefix_female',
        E::ts('Prefix option for submitted value "female"'),
        static::getPrefixOptions()
    );
    $this->add(
        'select',
        'prefix_other',
        E::ts('Prefix option for submitted value "other"'),
        static::getPrefixOptions()
    );

    $payment_instruments = CRM_Twingle_Profile::paymentInstruments();
    $this->assign('payment_instruments', $payment_instruments);
    foreach ($payment_instruments as $pi_name => $pi_label) {
      $this->add(
      // field type
        'select',
      // field name
        $pi_name,
      // field label
        E::ts('Record %1 as', [1 => $pi_label]),
      // list of options
        static::getPaymentInstruments(),
      // is required
        TRUE
      );

      $this->add(
        'select',
        $pi_name . '_status',
        E::ts('Record %1 donations with contribution status', [1 => $pi_label]),
        static::getContributionStatusOptions(),
        TRUE
      );
    }

    if (CRM_Twingle_Submission::civiSepaEnabled()) {
      $this->add(
        'select',
        'sepa_creditor_id',
        E::ts('CiviSEPA creditor'),
        static::getSepaCreditors(),
        TRUE
      );
    }

    $this->add(
    // field type
      'checkbox',
    // field name
      'newsletter_double_opt_in',
    // field label
      E::ts('Use Double-Opt-In for newsletter')
      );

    $this->add(
    // field type
      'select',
    // field name
      'newsletter_groups',
    // field label
      E::ts('Sign up for newsletter groups'),
    // list of options
      static::getNewsletterGroups(),
    // is not required
      FALSE,
      ['class' => 'crm-select2 huge', 'multiple' => 'multiple']
    );

    $this->add(
    // field type
      'select',
    // field name
      'postinfo_groups',
    // field label
      E::ts('Sign up for postal mail groups'),
    // list of options
      static::getPostinfoGroups(),
    // is not required
      FALSE,
      ['class' => 'crm-select2 huge', 'multiple' => 'multiple']
    );

    $this->add(
    // field type
      'select',
    // field name
      'donation_receipt_groups',
    // field label
      E::ts('Sign up for Donation receipt groups'),
    // list of options
      static::getDonationReceiptGroups(),
    // is not required
      FALSE,
      ['class' => 'crm-select2 huge', 'multiple' => 'multiple']
    );

    $this->add(
    // field type
      'select',
    // field name
      'campaign',
    // field label
      E::ts('Default Campaign'),
    // list of options
      ['' => E::ts('- none -')] + static::getCampaigns(),
    // is not required
      FALSE,
      ['class' => 'crm-select2 huge']
    );

    $this->add(
      'select',
      'campaign_targets',
      E::ts('Set Campaign for'),
      [
        'contribution' => E::ts('Contribution'),
        'recurring'    => E::ts('Recurring Contribution'),
        'membership'   => E::ts('Membership'),
        'mandate'      => E::ts('SEPA Mandate'),
        'contact'      => E::ts('Contacts (XCM)'),
      ],
      // is not required
      FALSE,
      ['class' => 'crm-select2 huge', 'multiple' => 'multiple']
    );

    $this->add(
    // field type
      'select',
    // field name
      'membership_type_id',
    // field label
      E::ts('Create membership of type'),
    // list of options
      ['' => E::ts('- none -')] + static::getMembershipTypes(),
    // is not required
      FALSE,
      ['class' => 'crm-select2 huge']
    );
    $this->add(
    // field type
      'select',
    // field name
      'membership_type_id_recur',
    // field label
      E::ts('Create membership of type (recurring)'),
    // list of options
      ['' => E::ts('- none -')] + static::getMembershipTypes(),
    // is not required
      FALSE,
      ['class' => 'crm-select2 huge']
    );
    $this->add(
        'text',
        'membership_postprocess_call',
        E::ts('API Call for Membership Postprocessing')
    );
    $this->addRule(
      'membership_postprocess_call',
      E::ts("The API call must have the form 'Entity.Action'."),
      'regex',
      '/^[A-Za-z_]+[.][A-Za-z_]+$/'
    );

    $this->add(
    // field type
      'text',
    // field name
      'contribution_source',
    // field label
      E::ts('Contribution source'),
      []
    );

    $this->add(
      'select',
      'required_address_components',
      E::ts('Required address components'),
      [
        'street_address' => E::ts('Street'),
        'postal_code'    => E::ts('Postal Code'),
        'city'           => E::ts('City'),
        'country'        => E::ts('Country'),
      ],
      // is not required
      FALSE,
      ['class' => 'crm-select2 huge', 'multiple' => 'multiple']
    );

    $this->add(
    // field type
      'textarea',
    // field name
      'custom_field_mapping',
    // field label
      E::ts('Custom field mapping'),
      []
    );

    $this->addButtons([
      [
        'type' => 'submit',
        'name' => E::ts('Save'),
        'isDefault' => TRUE,
      ],
    ]);

    // Export form elements.
    parent::buildQuickForm();
  }

  /**
   * Validates the profile form.
   *
   * @return bool
   *   TRUE when the form was successfully validated.
   */
  public function validate() {

    if (in_array($this->_op, ['create', 'edit', 'copy'])) {
      // Create profile with new values
      $profile_values = $this->exportValues();
      $profile = new CRM_Twingle_Profile(
        $profile_values['name'],
        $profile_values,
        $this->profile_id
      );

      // Validate profile data
      try {
        $profile->validate();
      }
      catch (ProfileValidationError $e) {
        $this->setElementError($e->getAffectedFieldName(), $e->getMessage());
      }
    }

    return parent::validate();
  }

  /**
   * Set the default values (i.e. the profile's current data) in the form.
   *
   * @return array<string, mixed>
   */
  public function setDefaultValues() {
    $defaults = parent::setDefaultValues();
    if (in_array($this->_op, ['create', 'edit', 'copy'])) {
      if (!$this->profile) {
        $this->profile = CRM_Twingle_Profile::createDefaultProfile()->copy();
      }
      $defaults['name'] = $this->profile->getName();
      $profile_data = $this->profile->getData();
      foreach ($profile_data as $element_name => $value) {
        $defaults[$element_name] = $value;
      }
      // backwards compatibility, see issue #27
      if (!isset($profile_data['campaign_targets'])) {
        $defaults['campaign_targets'] = ['contribution', 'contact'];
      }
    }
    return $defaults;
  }

  /**
   * Store the values submitted with the form in the profile.
   */
  public function postProcess(): void {
    $values = $this->exportValues();
    try {
      if (!isset($this->profile)) {
        throw new BaseException(E::ts('No profile set.'));
      }
      if (in_array($this->_op, ['create', 'edit', 'copy'], TRUE)) {
        if (!is_string($values['name'])) {
          $values['name'] = 'default';
        }
        $this->profile->setName($values['name']);
        foreach ($this->profile->getData() as $element_name => $value) {
          if ($element_name == 'newsletter_double_opt_in') {
            $values[$element_name] = (int) isset($values[$element_name]);
          }
          if (isset($values[$element_name])) {
            $this->profile->setAttribute($element_name, $values[$element_name]);
          }
        }
        $this->profile->saveProfile();
      }
      elseif ($this->_op == 'delete') {
        $this->profile->deleteProfile();
      }
    }
    catch (ProfileException $e) {
      Civi::log()->error($e->getLogMessage());
      CRM_Core_Session::setStatus(
        E::ts('Error'),
        $e->getMessage(),
        'error',
        ['unique' => TRUE]
      );
    }
    parent::postProcess();
  }

  /**
   * Retrieves location types present within the system as options for select
   * form elements.
   *
   * @return array<int, string>
   */
  public static function getLocationTypes() {
    if (!isset(static::$_locationTypes)) {
      static::$_locationTypes = [];
      $query = civicrm_api3('LocationType', 'get', [
        'option.limit' => 0,
        'is_active' => 1,
      ]);
      foreach ($query['values'] as $type) {
        static::$_locationTypes[(int) $type['id']] = $type['name'];
      }
    }
    return static::$_locationTypes;
  }

  /**
   * Retrieves XCM profiles (if supported). 'default' profile is always available
   *
   * @return array<string, string>
   */
  public static function getXCMProfiles() {
    if (!isset(static::$_xcm_profiles)) {
      if (class_exists('CRM_Xcm_Configuration')) {
        static::$_xcm_profiles = [
          '' => E::ts('&lt;select profile&gt;'),
        ];
        $profiles = CRM_Xcm_Configuration::getProfileList();
        foreach ($profiles as $profile_key => $profile_name) {
          static::$_xcm_profiles[$profile_key] = $profile_name;
        }
      }
    }
    return static::$_xcm_profiles;
  }

  /**
   * Retrieves financial types present within the system as options for select
   * form elements.
   *
   * @return array<int, string>
   */
  public static function getFinancialTypes() {
    if (!isset(static::$_financialTypes)) {
      static::$_financialTypes = [];
      $query = civicrm_api3('FinancialType', 'get', [
        'option.limit' => 0,
        'is_active' => 1,
        'return' => 'id,name',
      ]);
      foreach ($query['values'] as $type) {
        static::$_financialTypes[(int) $type['id']] = $type['name'];
      }
    }
    return static::$_financialTypes;
  }

  /**
   * Retrieves membership types present within the system as options for select
   * form elements.
   *
   * @return array<int, string>
   */
  public static function getMembershipTypes() {
    if (!isset(static::$_membershipTypes)) {
      static::$_membershipTypes = [];
      $query = civicrm_api3('MembershipType', 'get', [
        'option.limit' => 0,
        'is_active' => 1,
        'return' => 'id,name',
      ]);
      foreach ($query['values'] as $type) {
        static::$_membershipTypes[$type['id']] = $type['name'];
      }
    }
    return static::$_membershipTypes;
  }

  /**
   * Retrieves genders present within the system as options for select form
   * elements.
   *
   * @return array<int, string>
   */
  public static function getGenderOptions() {
    if (!isset(static::$_genderOptions)) {
      static::$_genderOptions = [];
      $query = civicrm_api3('OptionValue', 'get', [
        'option.limit' => 0,
        'option_group_id' => 'gender',
        'is_active' => 1,
        'return' => [
          'value',
          'label',
        ],
      ]);
      foreach ($query['values'] as $gender) {
        static::$_genderOptions[(int) $gender['value']] = $gender['label'];
      }
    }
    return static::$_genderOptions;
  }

  /**
   * Retrieves prefixes present within the system as options for select form
   * elements.
   *
   * @return array<int, string>
   */
  public static function getPrefixOptions() {
    if (!isset(static::$_prefixOptions)) {
      static::$_prefixOptions = ['' => E::ts('none')];
      $query = civicrm_api3('OptionValue', 'get', [
        'option.limit' => 0,
        'option_group_id' => 'individual_prefix',
        'is_active' => 1,
        'return' => [
          'value',
          'label',
        ],
      ]);
      foreach ($query['values'] as $prefix) {
        static::$_prefixOptions[(int) $prefix['value']] = $prefix['label'];
      }
    }
    return static::$_prefixOptions;
  }

  /**
   * Retrieves CiviSEPA creditors as options for select form elements.
   *
   * @return array<int, string>
   */
  public static function getSepaCreditors() {
    if (!isset(static::$_sepaCreditors)) {
      static::$_sepaCreditors = [];
      if (CRM_Twingle_Submission::civiSepaEnabled()) {
        $result = civicrm_api3('SepaCreditor', 'get', [
          'option.limit' => 0,
        ]);
        foreach ($result['values'] as $sepa_creditor) {
          static::$_sepaCreditors[(int) $sepa_creditor['id']] = $sepa_creditor['name'];
        }
      }
    }
    return static::$_sepaCreditors;
  }

  /**
   * Retrieves payment instruments present within the system as options for
   * select form elements.
   *
   * @return array<string, string>
   */
  public static function getPaymentInstruments() {
    if (!isset(self::$_paymentInstruments)) {
      self::$_paymentInstruments = [];
      $query = civicrm_api3('OptionValue', 'get', [
        'option.limit' => 0,
        'option_group_id' => 'payment_instrument',
        'is_active'  => 1,
        'return' => 'value,label',
      ]);
      foreach ($query['values'] as $payment_instrument) {
        // Do not include CiviSEPA payment instruments, but add a SEPA option if
        // enabled.
        if (
          CRM_Twingle_Submission::civiSepaEnabled()
              && CRM_Twingle_Tools::isSDD($payment_instrument['value'])
        ) {
          if (!isset(self::$_paymentInstruments['sepa'])) {
            self::$_paymentInstruments['sepa'] = E::ts('CiviSEPA');
          }
        }
        else {
          self::$_paymentInstruments[$payment_instrument['value']] = $payment_instrument['label'];
        }
      }
    }
    return self::$_paymentInstruments;
  }

  /**
   * Retrieves contribution statuses as options for select form elements.
   *
   * @return array<int, string>
   */
  public static function getContributionStatusOptions() {
    if (!isset(self::$_contributionStatusOptions)) {
      $query = civicrm_api3(
        'OptionValue',
        'get',
        [
          'option.limit' => 0,
          'option_group_id' => 'contribution_status',
          'return' => [
            'value',
            'label',
          ],
        ]
      );

      foreach ($query['values'] as $contribution_status) {
        self::$_contributionStatusOptions[(int) $contribution_status['value']] = $contribution_status['label'];
      }
    }

    return self::$_contributionStatusOptions;
  }

  /**
   * Retrieves active groups used as mailing lists within the system as options
   * for select form elements.
   *
   * @return array<string, string>
   *
   */
  public static function getNewsletterGroups() {
    if (!isset(static::$_newsletterGroups)) {
      static::$_newsletterGroups = [];
      $group_types = civicrm_api3('OptionValue', 'get', [
        'option.limit' => 0,
        'option_group_id' => 'group_type',
        'name' => CRM_Twingle_Submission::GROUP_TYPE_NEWSLETTER,
      ]);
      if ($group_types['count'] > 0) {
        $group_type = reset($group_types['values']);
        $query = civicrm_api3('Group', 'get', [
          'is_active' => 1,
          'group_type' => ['LIKE' => '%' . CRM_Utils_Array::implodePadded($group_type['value']) . '%'],
          'option.limit'   => 0,
          'return'         => 'id,name',
        ]);
        foreach ($query['values'] as $group) {
          static::$_newsletterGroups[$group['id']] = $group['name'];
        }
      }
      else {
        static::$_newsletterGroups[''] = E::ts('No mailing lists available');
      }
    }
    return static::$_newsletterGroups;
  }

  /**
   * Retrieves active groups as options for select form elements.
   *
   * @return array<int, string>
   */
  public static function getGroups() {
    if (!isset(static::$_groups)) {
      static::$_groups = [];
      $query = civicrm_api3('Group', 'get', [
        'option.limit' => 0,
        'is_active' => 1,
        'return' => 'id,name',
      ]);
      foreach ($query['values'] as $group) {
        static::$_groups[(int) $group['id']] = $group['name'];
      }
    }
    return static::$_groups;
  }

  /**
   * Retrieves active groups used as postal mailing lists within the system as
   * options for select form elements.
   *
   * @return array<int, string>
   */
  public static function getPostinfoGroups() {
    return static::getGroups();
  }

  /**
   * Retrieves active groups used as donation receipt requester lists within the
   * system as options for select form elements.
   *
   * @return array<int, string>
   */
  public static function getDonationReceiptGroups() {
    return static::getGroups();
  }

  /**
   * Retrieves campaigns as options for select elements.
   *
   * @return array<int, string>
   */
  public static function getCampaigns() {
    if (!isset(static::$_campaigns)) {
      static::$_campaigns = [];
      $query = civicrm_api3('Campaign', 'get', [
        'option.limit' => 0,
        'return' => [
          'id',
          'title',
        ],
      ]);
      foreach ($query['values'] as $campaign) {
        static::$_campaigns[(int) $campaign['id']] = $campaign['title'];
      }
    }
    return static::$_campaigns;
  }

}
