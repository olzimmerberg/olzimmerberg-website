<?php

declare(strict_types=1);

require_once __DIR__.'/../../../src/fields/ArrayField.php';
require_once __DIR__.'/../common/UnitTestCase.php';
require_once __DIR__.'/FakeItemField.php';

/**
 * @internal
 * @covers \ArrayField
 */
final class ArrayFieldTest extends UnitTestCase {
    public function testTypeScriptType(): void {
        $field = new ArrayField([
            'item_field' => new FakeItemField([]),
        ]);
        $this->assertSame('Array<ItemType>', $field->getTypeScriptType());
        $this->assertSame([], $field->getExportedTypeScriptTypes());
    }

    public function testTypeScriptTypeWithNullAllowed(): void {
        $field = new ArrayField([
            'item_field' => new FakeItemField([]),
            'allow_null' => true,
        ]);
        $this->assertSame('Array<ItemType>|null', $field->getTypeScriptType());
        $this->assertSame([], $field->getExportedTypeScriptTypes());
    }

    public function testTypeScriptTypeWithNullAllowedInItem(): void {
        $field = new ArrayField([
            'item_field' => new FakeItemField(['allow_null' => true]),
        ]);
        $this->assertSame('Array<ItemType|null>', $field->getTypeScriptType());
        $this->assertSame([], $field->getExportedTypeScriptTypes());
    }

    public function testTypeScriptTypeWithSubstitutionDisabled(): void {
        $field = new ArrayField([
            'export_as' => 'ExportedType',
            'item_field' => new FakeItemField([]),
        ]);
        $this->assertSame('Array<ItemType>', $field->getTypeScriptType([
            'should_substitute' => false,
        ]));
        $this->assertSame([
            'ExportedType' => 'Array<ItemType>',
        ], $field->getExportedTypeScriptTypes());
    }

    public function testSubstitutedTypeScriptType(): void {
        $field = new ArrayField([
            'export_as' => 'ExportedType',
            'item_field' => new FakeItemField([]),
        ]);
        $this->assertSame('ExportedType', $field->getTypeScriptType());
        $this->assertSame([
            'ExportedType' => 'Array<ItemType>',
        ], $field->getExportedTypeScriptTypes());
    }

    public function testSubstitutedItemTypeScriptType(): void {
        $field = new ArrayField([
            'item_field' => new FakeItemField(['export_as' => 'ExportedType']),
        ]);
        $this->assertSame('Array<ExportedType>', $field->getTypeScriptType());
        $this->assertSame([
            'ExportedType' => 'ItemType',
        ], $field->getExportedTypeScriptTypes());
    }

    public function testSubstitutedItemTypeScriptTypeWithNullAllowed(): void {
        $field = new ArrayField([
            'item_field' => new FakeItemField(['export_as' => 'ExportedType']),
            'allow_null' => true,
        ]);
        $this->assertSame('Array<ExportedType>|null', $field->getTypeScriptType());
        $this->assertSame([
            'ExportedType' => 'ItemType',
        ], $field->getExportedTypeScriptTypes());
    }

    public function testSubstitutedItemTypeScriptTypeWithNullAllowedInItem(): void {
        $field = new ArrayField([
            'item_field' => new FakeItemField([
                'allow_null' => true,
                'export_as' => 'ExportedType',
            ]),
        ]);
        $this->assertSame('Array<ExportedType>', $field->getTypeScriptType());
        $this->assertSame([
            'ExportedType' => 'ItemType|null',
        ], $field->getExportedTypeScriptTypes());
    }

    public function testParse(): void {
        $field = new ArrayField([
            'item_field' => new FakeItemField([]),
        ]);
        try {
            $field->parse('test');
            $this->fail('Error expected');
        } catch (\Exception $exc) {
            $this->assertSame("Unlesbares Feld: ArrayField", $exc->getMessage());
        }
    }

    public function testItemFieldDefault(): void {
        try {
            new ArrayField([]);
            $this->fail('Error expected');
        } catch (\Exception $exc) {
            $this->assertSame("`item_field` must be an instance of `Field`", $exc->getMessage());
        }
    }

    public function testItemFieldSet(): void {
        $item_field = new FakeItemField([]);
        $field = new ArrayField([
            'item_field' => $item_field,
        ]);
        $this->assertSame($item_field, $field->getItemField());
    }

    public function testValidatesItemField(): void {
        $field = new ArrayField([
            'item_field' => new FakeItemField([]),
        ]);
        $this->assertSame(
            [
                "Element '0': Wert muss 'foo' oder 'bar' sein.",
                "Element '2': Wert muss 'foo' oder 'bar' sein.",
                "Element '4': Feld darf nicht leer sein.",
            ],
            $field->getValidationErrors(['neither', 'foo', 'nor', 'bar', null])
        );
        $this->assertSame(
            ["Element '0': Wert muss 'foo' oder 'bar' sein."],
            $field->getValidationErrors(['not', 'foo'])
        );
        $this->assertSame(
            ["Element '0': Feld darf nicht leer sein."],
            $field->getValidationErrors([null])
        );
        $this->assertSame([], $field->getValidationErrors(['foo', 'bar', 'foo']));
        $this->assertSame([], $field->getValidationErrors([]));
        $this->assertSame(["Wert muss eine Liste sein."], $field->getValidationErrors('not_a_list'));
        $this->assertSame(["Feld darf nicht leer sein."], $field->getValidationErrors(null));
    }

    public function testValidatesNullableItemField(): void {
        $field = new ArrayField([
            'item_field' => new FakeItemField(['allow_null' => true]),
        ]);
        $this->assertSame(
            [
                "Element '0': Wert muss 'foo' oder 'bar' sein.",
                "Element '2': Wert muss 'foo' oder 'bar' sein.",
            ],
            $field->getValidationErrors(['neither', 'foo', 'nor', 'bar', null])
        );
        $this->assertSame(
            ["Element '0': Wert muss 'foo' oder 'bar' sein."],
            $field->getValidationErrors(['not', 'foo'])
        );
        $this->assertSame([], $field->getValidationErrors([null]));
        $this->assertSame([], $field->getValidationErrors(['foo', 'bar', 'foo']));
        $this->assertSame([], $field->getValidationErrors([]));
        $this->assertSame(["Wert muss eine Liste sein."], $field->getValidationErrors('not_a_list'));
        $this->assertSame(["Feld darf nicht leer sein."], $field->getValidationErrors(null));
    }

    public function testValidatesNullableArrayField(): void {
        $field = new ArrayField([
            'item_field' => new FakeItemField([]),
            'allow_null' => true,
        ]);
        $this->assertSame(
            [
                "Element '0': Wert muss 'foo' oder 'bar' sein.",
                "Element '2': Wert muss 'foo' oder 'bar' sein.",
                "Element '4': Feld darf nicht leer sein.",
            ],
            $field->getValidationErrors(['neither', 'foo', 'nor', 'bar', null])
        );
        $this->assertSame(
            ["Element '0': Wert muss 'foo' oder 'bar' sein."],
            $field->getValidationErrors(['not', 'foo'])
        );
        $this->assertSame(
            ["Element '0': Feld darf nicht leer sein."],
            $field->getValidationErrors([null])
        );
        $this->assertSame([], $field->getValidationErrors(['foo', 'bar', 'foo']));
        $this->assertSame([], $field->getValidationErrors([]));
        $this->assertSame(["Wert muss eine Liste sein."], $field->getValidationErrors('not_a_list'));
        $this->assertSame([], $field->getValidationErrors(null));
    }
}
