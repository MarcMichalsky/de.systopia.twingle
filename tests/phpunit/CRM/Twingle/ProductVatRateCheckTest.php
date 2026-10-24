<?php

declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use Civi\Test\CiviEnvBuilder;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Tests for the system check about products without VAT rate.
 *
 * @group headless
 * @covers ::twingle_civicrm_check
 * @covers \CRM_Twingle_BAO_TwingleProduct::countProductsWithoutVatRate
 */
class CRM_Twingle_ProductVatRateCheckTest extends TestCase implements HeadlessInterface, TransactionalInterface {

  /**
   * {@inheritDoc}
   */
  public function setUpHeadless(): CiviEnvBuilder {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function testWarnsAboutProductsWithoutVatRate(): void {
    Civi::settings()->set('twingle_use_shop', TRUE);
    $product_id = $this->createProduct('Coffee Shop');

    $messages = $this->runCheck();
    self::assertCount(1, $messages);
    self::assertSame('twingle_product_vat_rate', $messages[0]->getName());
    self::assertStringContainsString('Coffee Shop: 1', $messages[0]->getMessage());

    // Products with a VAT rate (including 0 %) are fine.
    CRM_Core_DAO::executeQuery(
      'UPDATE civicrm_twingle_product SET vat_rate = 0 WHERE id = %1',
      [1 => [$product_id, 'Integer']]
    );
    self::assertSame([], $this->runCheck());
  }

  public function testNoWarningIfShopIntegrationIsDisabled(): void {
    Civi::settings()->set('twingle_use_shop', FALSE);
    $this->createProduct('Coffee Shop');

    self::assertSame([], $this->runCheck());
  }

  /**
   * @return array<\CRM_Utils_Check_Message>
   */
  private function runCheck(): array {
    $messages = [];
    twingle_civicrm_check($messages, ['twingle_product_vat_rate']);
    return $messages;
  }

  /**
   * Create a Twingle product without VAT rate with an active price field.
   *
   * @return int
   *   The ID of the Twingle product.
   */
  private function createProduct(string $shop_name): int {
    $price_set_id = \Civi\Api4\PriceSet::create(FALSE)
      ->addValue('name', 'twingle_test_shop')
      ->addValue('title', $shop_name)
      ->addValue('extends', [CRM_Core_Component::getComponentID('CiviContribute')])
      ->addValue('financial_type_id', 1)
      ->execute()
      ->single()['id'];
    $price_field_id = \Civi\Api4\PriceField::create(FALSE)
      ->addValue('price_set_id', $price_set_id)
      ->addValue('name', 'tw_product_2534')
      ->addValue('label', 'Coffee')
      ->addValue('html_type', 'Text')
      ->addValue('is_active', TRUE)
      ->execute()
      ->single()['id'];

    CRM_Core_DAO::executeQuery(
      'INSERT INTO civicrm_twingle_shop (project_identifier, numerical_project_id, price_set_id, name)
      VALUES ("tw123", 123, %1, %2)',
      [1 => [$price_set_id, 'Integer'], 2 => [$shop_name, 'String']]
    );
    $shop_id = (int) CRM_Core_DAO::singleValueQuery('SELECT LAST_INSERT_ID()');
    CRM_Core_DAO::executeQuery(
      'INSERT INTO civicrm_twingle_product (external_id, price_field_id, twingle_shop_id, updated_at)
      VALUES (2534, %1, %2, NOW())',
      [1 => [$price_field_id, 'Integer'], 2 => [$shop_id, 'Integer']]
    );
    return (int) CRM_Core_DAO::singleValueQuery('SELECT LAST_INSERT_ID()');
  }

}
