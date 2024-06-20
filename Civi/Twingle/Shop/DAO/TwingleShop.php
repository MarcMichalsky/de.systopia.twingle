<?php

namespace Civi\Twingle\Shop\DAO;

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from de.systopia.twingle/xml/schema/CRM/Twingle/TwingleShop.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:95a46935c30b3da52e4df2bec871b962)
 */
use CRM_Twingle_ExtensionUtil as E;

/**
 * Database access object for the TwingleShop entity.
 */
class TwingleShop extends \CRM_Core_DAO {
  const EXT = E::LONG_NAME;
  const TABLE_ADDED = '';

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_twingle_shop';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = FALSE;

  /**
   * Unique TwingleShop ID
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $id;

  /**
   * Twingle Project Identifier
   *
   * @var string
   *   (SQL type: varchar(32))
   *   Note that values will be retrieved from the database as a string.
   */
  public $project_identifier;

  /**
   * Numerical Twingle Project Identifier
   *
   * @var int|string
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $numerical_project_id;

  /**
   * FK to Price Set
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $price_set_id;

  /**
   * name of the shop
   *
   * @var string
   *   (SQL type: varchar(64))
   *   Note that values will be retrieved from the database as a string.
   */
  public $name;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_twingle_shop';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE) {
    return $plural ? E::ts('Twingle Shops') : E::ts('Twingle Shop');
  }

  /**
   * Returns foreign keys and entity references.
   *
   * @return array
   *   [CRM_Core_Reference_Interface]
   */
  public static function getReferenceColumns() {
    if (!isset(\Civi::$statics[__CLASS__]['links'])) {
      \Civi::$statics[__CLASS__]['links'] = static::createReferenceColumns(__CLASS__);
      \Civi::$statics[__CLASS__]['links'][] = new \CRM_Core_Reference_Basic(self::getTableName(), 'price_set_id', 'civicrm_price_set', 'id');
      \CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'links_callback', \Civi::$statics[__CLASS__]['links']);
    }
    return \Civi::$statics[__CLASS__]['links'];
  }

  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  public static function &fields() {
    if (!isset(\Civi::$statics[__CLASS__]['fields'])) {
      \Civi::$statics[__CLASS__]['fields'] = [
        'id' => [
          'name' => 'id',
          'type' => \CRM_Utils_Type::T_INT,
          'title' => E::ts('ID'),
          'description' => E::ts('Unique TwingleShop ID'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_twingle_shop.id',
          'table_name' => 'civicrm_twingle_shop',
          'entity' => 'TwingleShop',
          'bao' => 'Civi\Twingle\Shop\DAO\TwingleShop',
          'localizable' => 0,
          'html' => [
            'type' => 'Number',
          ],
          'readonly' => TRUE,
          'add' => NULL,
        ],
        'project_identifier' => [
          'name' => 'project_identifier',
          'type' => \CRM_Utils_Type::T_STRING,
          'title' => E::ts('Project Identifier'),
          'description' => E::ts('Twingle Project Identifier'),
          'required' => TRUE,
          'maxlength' => 32,
          'size' => \CRM_Utils_Type::MEDIUM,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_twingle_shop.project_identifier',
          'table_name' => 'civicrm_twingle_shop',
          'entity' => 'TwingleShop',
          'bao' => 'Civi\Twingle\Shop\DAO\TwingleShop',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
          ],
          'add' => NULL,
        ],
        'numerical_project_id' => [
          'name' => 'numerical_project_id',
          'type' => \CRM_Utils_Type::T_INT,
          'title' => E::ts('Numerical Project ID'),
          'description' => E::ts('Numerical Twingle Project Identifier'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_twingle_shop.numerical_project_id',
          'table_name' => 'civicrm_twingle_shop',
          'entity' => 'TwingleShop',
          'bao' => 'Civi\Twingle\Shop\DAO\TwingleShop',
          'localizable' => 0,
          'html' => [
            'type' => 'Number',
          ],
          'add' => NULL,
        ],
        'price_set_id' => [
          'name' => 'price_set_id',
          'type' => \CRM_Utils_Type::T_INT,
          'title' => E::ts('Price Set ID'),
          'description' => E::ts('FK to Price Set'),
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_twingle_shop.price_set_id',
          'table_name' => 'civicrm_twingle_shop',
          'entity' => 'TwingleShop',
          'bao' => 'Civi\Twingle\Shop\DAO\TwingleShop',
          'localizable' => 0,
          'FKClassName' => 'CRM_Price_DAO_PriceSet',
          'add' => NULL,
        ],
        'name' => [
          'name' => 'name',
          'type' => \CRM_Utils_Type::T_STRING,
          'title' => E::ts('Name'),
          'description' => E::ts('name of the shop'),
          'required' => TRUE,
          'maxlength' => 64,
          'size' => \CRM_Utils_Type::BIG,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_twingle_shop.name',
          'table_name' => 'civicrm_twingle_shop',
          'entity' => 'TwingleShop',
          'bao' => 'CRM_Twingle_Shop_DAO_TwingleShop',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
          ],
          'add' => NULL,
        ],
      ];
      \CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', \Civi::$statics[__CLASS__]['fields']);
    }
    return \Civi::$statics[__CLASS__]['fields'];
  }

  /**
   * Return a mapping from field-name to the corresponding key (as used in fields()).
   *
   * @return array
   *   Array(string $name => string $uniqueName).
   */
  public static function &fieldKeys() {
    if (!isset(\Civi::$statics[__CLASS__]['fieldKeys'])) {
      \Civi::$statics[__CLASS__]['fieldKeys'] = array_flip(\CRM_Utils_Array::collect('name', self::fields()));
    }
    return \Civi::$statics[__CLASS__]['fieldKeys'];
  }

  /**
   * Returns the names of this table
   *
   * @return string
   */
  public static function getTableName() {
    return self::$_tableName;
  }

  /**
   * Returns if this table needs to be logged
   *
   * @return bool
   */
  public function getLog() {
    return self::$_log;
  }

  /**
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &import($prefix = FALSE) {
    $r = \CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'twingle_shop', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of fields that can be exported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &export($prefix = FALSE) {
    $r = \CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'twingle_shop', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of indices
   *
   * @param bool $localize
   *
   * @return array
   */
  public static function indices($localize = TRUE) {
    $indices = [];
    return ($localize && !empty($indices)) ? \CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}