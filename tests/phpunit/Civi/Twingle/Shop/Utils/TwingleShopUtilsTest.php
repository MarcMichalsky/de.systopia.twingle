<?php

declare(strict_types = 1);

namespace Civi\Twingle\Shop\Utils;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Twingle\Shop\Utils\TwingleShopUtils
 */
class TwingleShopUtilsTest extends TestCase {

  /**
   * @dataProvider provideTaxCalculations
   */
  public function testTaxCalculation(float $gross_amount, float $tax_rate, float $tax_amount, float $net_amount): void {
    self::assertSame($tax_amount, TwingleShopUtils::calculate_included_tax($gross_amount, $tax_rate));
    self::assertSame($net_amount, TwingleShopUtils::calculate_net_amount($gross_amount, $tax_rate));
  }

  /**
   * @phpstan-return array<string, array{float, float, float, float}>
   */
  public function provideTaxCalculations(): array {
    return [
      'reduced rate' => [10.0, 7.0, 0.65, 9.35],
      'regular rate' => [10.0, 19.0, 1.6, 8.4],
      'no tax' => [10.0, 0.0, 0.0, 10.0],
      'zero price' => [0.0, 19.0, 0.0, 0.0],
    ];
  }

}
