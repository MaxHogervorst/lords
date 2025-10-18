<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\SepaSequenceType;
use Tests\TestCase;

class SepaSequenceTypeTest extends TestCase
{
    /**
     * Test SEPA sequence type enum values
     */
    public function test_sepa_sequence_type_has_correct_values(): void
    {
        $this->assertEquals('FRST', SepaSequenceType::FIRST->value);
        $this->assertEquals('RCUR', SepaSequenceType::RECURRING->value);
    }

    /**
     * Test SEPA sequence type enum from string
     */
    public function test_sepa_sequence_type_can_be_created_from_string(): void
    {
        $first = SepaSequenceType::from('FRST');
        $recurring = SepaSequenceType::from('RCUR');

        $this->assertInstanceOf(SepaSequenceType::class, $first);
        $this->assertInstanceOf(SepaSequenceType::class, $recurring);
        $this->assertEquals(SepaSequenceType::FIRST, $first);
        $this->assertEquals(SepaSequenceType::RECURRING, $recurring);
    }

    /**
     * Test SEPA sequence type enum cases
     */
    public function test_sepa_sequence_type_has_all_cases(): void
    {
        $cases = SepaSequenceType::cases();

        $this->assertCount(2, $cases);
        $this->assertContains(SepaSequenceType::FIRST, $cases);
        $this->assertContains(SepaSequenceType::RECURRING, $cases);
    }

    /**
     * Test SEPA sequence type enum try from
     */
    public function test_sepa_sequence_type_try_from_valid_string(): void
    {
        $first = SepaSequenceType::tryFrom('FRST');
        $recurring = SepaSequenceType::tryFrom('RCUR');

        $this->assertNotNull($first);
        $this->assertNotNull($recurring);
        $this->assertEquals(SepaSequenceType::FIRST, $first);
        $this->assertEquals(SepaSequenceType::RECURRING, $recurring);
    }

    /**
     * Test SEPA sequence type enum try from invalid string
     */
    public function test_sepa_sequence_type_try_from_invalid_string(): void
    {
        $invalid = SepaSequenceType::tryFrom('INVALID');

        $this->assertNull($invalid);
    }

    /**
     * Test SEPA sequence type in method signature
     */
    public function test_sepa_sequence_type_in_type_hints(): void
    {
        $testFunction = function (SepaSequenceType $type): string {
            return $type->value;
        };

        $result = $testFunction(SepaSequenceType::FIRST);
        $this->assertEquals('FRST', $result);

        $result = $testFunction(SepaSequenceType::RECURRING);
        $this->assertEquals('RCUR', $result);
    }
}
