<?php

declare(strict_types=1);

namespace Warehouse\Tests\Catalog;

use PHPUnit\Framework\TestCase;
use Warehouse\Catalog\Domain\Exception\InvalidSkuException;
use Warehouse\Catalog\Domain\Sku;

final class SkuTest extends TestCase
{
    public function testItNormalizesToUppercase(): void
    {
        $sku = Sku::fromString('ab-12');

        self::assertSame('AB-12', $sku->toString());
    }

    public function testItAcceptsBoundaryLengths(): void
    {
        self::assertSame('ABC', Sku::fromString('ABC')->toString());
        self::assertSame(str_repeat('A', 32), Sku::fromString(str_repeat('A', 32))->toString());
    }

    /** @dataProvider invalidSkus */
    public function testItRejectsInvalidValues(string $value): void
    {
        $this->expectException(InvalidSkuException::class);
        Sku::fromString($value);
    }

    /** @return array<string, array{0: string}> */
    public static function invalidSkus(): array
    {
        return [
            'empty' => [''],
            'too short' => ['AB'],
            'too long' => [str_repeat('A', 33)],
            'space' => ['AB C'],
            'underscore' => ['AB_C'],
            'lowercase special' => ['ab*c'],
        ];
    }

    public function testEqualsComparesNormalizedValue(): void
    {
        self::assertTrue(Sku::fromString('abc')->equals(Sku::fromString('ABC')));
    }
}
