<?php

declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use Civi\Test\CiviEnvBuilder;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Tests for detecting outdated Twingle products.
 *
 * @group headless
 * @covers \CRM_Twingle_BAO_TwingleProduct::checkOutdated
 * @covers \CRM_Twingle_BAO_TwingleProduct::transformTwingleAttrs
 * @covers \CRM_Twingle_BAO_TwingleProduct::add
 * @covers \CRM_Twingle_BAO_TwingleProduct::createPriceField
 * @covers \CRM_Twingle_BAO_TwingleProduct::load
 */
class CRM_Twingle_ProductOutdatedTest extends TestCase implements HeadlessInterface, TransactionalInterface {

  private const EXTERNAL_ID = 2534;

  private const TW_UPDATED_AT = 1700000000;

  /**
   * {@inheritDoc}
   */
  public function setUpHeadless(): CiviEnvBuilder {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function testUnroundedNetPriceIsUpToDate(): void {
    $product = $this->createProduct(2.803738318, 7.0);
    $product->checkOutdated($this->getProductFromTwingle(3.0, 0.07));

    self::assertEmpty(get_object_vars($product)['is_outdated'] ?? NULL);
    self::assertSame(2.803738318, $product->price);
  }

  public function testRoundedNetPriceIsOutdated(): void {
    $product = $this->createProduct(2.80, 7.0);
    $product->checkOutdated($this->getProductFromTwingle(3.0, 0.07));

    self::assertTrue(get_object_vars($product)['is_outdated'] ?? NULL);
    self::assertSame(2.803738318, $product->price);
  }

  public function testPriceWithoutVatIsUpToDate(): void {
    $product = $this->createProduct(3.0, 0.0);
    $product->checkOutdated($this->getProductFromTwingle(3.0, 0));

    self::assertEmpty(get_object_vars($product)['is_outdated'] ?? NULL);
    self::assertSame(3.0, $product->price);
  }

  /**
   * A product whose price has been fixed in Twingle after its price field
   * has been created with a custom price.
   */
  public function testUpdateFixesPriceOfFormerCustomPriceProduct(): void {
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['all CiviCRM permissions and ACLs'];
    $product = $this->createProduct(1.0, 7.0, TRUE);
    self::assertNull($product->price);

    $product->checkOutdated($this->getProductFromTwingle(3.0, 0.07));
    self::assertTrue(get_object_vars($product)['is_outdated'] ?? NULL);

    // Update the product like the profile form does
    $values = get_object_vars($product);
    /** @phpstan-var array{values: array<string, mixed>} $result */
    $result = civicrm_api3('TwingleProduct', 'create', array_intersect_key($values, array_flip([
      'id',
      'name',
      'is_active',
      'price',
      'vat_rate',
      'external_id',
      'financial_type_id',
      'price_field_id',
      'tw_updated_at',
      'twingle_shop_id',
    ])));
    self::assertEquals(2.803738318, $result['values']['price']);

    $price_field = \Civi\Api4\PriceField::get(FALSE)
      ->addSelect('is_enter_qty', 'is_display_amounts')
      ->addWhere('id', '=', $values['price_field_id'])
      ->execute()
      ->single();
    self::assertFalse($price_field['is_enter_qty']);
    self::assertTrue($price_field['is_display_amounts']);

    $product = CRM_Twingle_BAO_TwingleProduct::findByExternalId(self::EXTERNAL_ID);
    self::assertNotNull($product);
    self::assertSame(2.803738318, $product->price);
    $product->checkOutdated($this->getProductFromTwingle(3.0, 0.07));
    self::assertEmpty(get_object_vars($product)['is_outdated'] ?? NULL);
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  private function getProductFromTwingle(float $price, float|int $included_tax): array {
    return [
      'id' => self::EXTERNAL_ID,
      'name' => 'Coffee',
      'price' => $price,
      'included_tax' => $included_tax,
      'updated_at' => self::TW_UPDATED_AT,
      'created_at' => self::TW_UPDATED_AT,
      'project_id' => 123,
    ];
  }

  /**
   * Create a Twingle product with a price field.
   */
  private function createProduct(
    float $amount,
    float $vat_rate,
    bool $custom_price = FALSE,
  ): CRM_Twingle_BAO_TwingleProduct {
    $price_set_id = \Civi\Api4\PriceSet::create(FALSE)
      ->addValue('name', 'twingle_test_shop')
      ->addValue('title', 'Coffee Shop')
      ->addValue('extends', [CRM_Core_Component::getComponentID('CiviContribute')])
      ->addValue('financial_type_id', 1)
      ->execute()
      ->single()['id'];
    $price_field_id = \Civi\Api4\PriceField::create(FALSE)
      ->addValue('price_set_id', $price_set_id)
      ->addValue('name', 'tw_product_' . self::EXTERNAL_ID)
      ->addValue('label', 'Coffee')
      ->addValue('html_type', 'Text')
      ->addValue('is_active', TRUE)
      ->addValue('is_enter_qty', $custom_price)
      ->execute()
      ->single()['id'];
    \Civi\Api4\PriceFieldValue::create(FALSE)
      ->addValue('price_field_id', $price_field_id)
      ->addValue('label', 'Coffee')
      ->addValue('description', 'Fair trade')
      ->addValue('amount', $amount)
      ->addValue('financial_type_id', 1)
      ->execute();

    CRM_Core_DAO::executeQuery(
      'INSERT INTO civicrm_twingle_shop (project_identifier, numerical_project_id, price_set_id, name)
      VALUES ("tw123", 123, %1, "Coffee Shop")',
      [1 => [$price_set_id, 'Integer']]
    );
    $shop_id = (int) CRM_Core_DAO::singleValueQuery('SELECT LAST_INSERT_ID()');
    CRM_Core_DAO::executeQuery(
      'INSERT INTO civicrm_twingle_product (external_id, price_field_id, twingle_shop_id, vat_rate, updated_at)
      VALUES (%1, %2, %3, %4, FROM_UNIXTIME(%5))',
      [
        1 => [self::EXTERNAL_ID, 'Integer'],
        2 => [$price_field_id, 'Integer'],
        3 => [$shop_id, 'Integer'],
        4 => [$vat_rate, 'Float'],
        5 => [self::TW_UPDATED_AT, 'Integer'],
      ]
    );

    $product = CRM_Twingle_BAO_TwingleProduct::findByExternalId(self::EXTERNAL_ID);
    self::assertNotNull($product);
    return $product;
  }

}
