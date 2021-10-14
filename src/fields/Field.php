<?php

class Field {
    private bool $allow_null = false;
    private $default_value;
    protected ?string $export_as = null;

    public function __construct($config = []) {
        $this->allow_null = $config['allow_null'] ?? false;
        $this->default_value = $config['default_value'] ?? null;
        $this->export_as = $config['export_as'] ?? null;
    }

    public function getAllowNull() {
        return $this->allow_null;
    }

    public function getDefaultValue() {
        return $this->default_value;
    }

    public function getValidationErrors($value) {
        $validation_errors = [];
        if (!$this->allow_null) {
            if ($value === null) {
                if ($this->default_value === null) {
                    $validation_errors[] = "Feld darf nicht leer sein.";
                }
            }
        }
        return $validation_errors;
    }

    public function parse($string) {
        if ($string == '') {
            return null;
        }
        return $string;
    }

    public function getTypeScriptType($config = []) {
        $should_substitute = $config['should_substitute'] ?? true;
        if ($this->export_as !== null && $should_substitute) {
            return $this->export_as;
        }
        return 'any';
    }

    public function getExportedTypeScriptTypes() {
        if ($this->export_as !== null) {
            return [
                $this->export_as => $this->getTypeScriptType([
                    'should_substitute' => false,
                ]),
            ];
        }
        return [];
    }
}
