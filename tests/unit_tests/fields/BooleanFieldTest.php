<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../../../src/fields/BooleanField.php';

/**
 * @internal
 * @coversNothing
 */
final class BooleanFieldTest extends TestCase {
    public function testValidatesNullAllowed(): void {
        $field = new BooleanField('fake', ['allow_null' => true]);
        $this->assertSame([], $field->getValidationErrors(true));
        $this->assertSame([], $field->getValidationErrors(false));
        $this->assertSame([], $field->getValidationErrors(null));
    }

    public function testValidatesNullDisallowed(): void {
        $field = new BooleanField('fake', ['allow_null' => false]);
        $this->assertSame([], $field->getValidationErrors(true));
        $this->assertSame([], $field->getValidationErrors(false));
        $this->assertSame(['Feld darf nicht leer sein.'], $field->getValidationErrors(null));
    }

    public function testValidatesWeirdValues(): void {
        $field = new BooleanField('fake', []);
        $this->assertSame(['Wert muss Ja oder Nein sein.'], $field->getValidationErrors(1));
        $this->assertSame(['Wert muss Ja oder Nein sein.'], $field->getValidationErrors('test'));
        $this->assertSame(['Wert muss Ja oder Nein sein.'], $field->getValidationErrors([1]));
        $this->assertSame(['Wert muss Ja oder Nein sein.'], $field->getValidationErrors([1 => 'one']));
    }
}
