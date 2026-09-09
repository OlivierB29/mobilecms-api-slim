<?php

namespace App\Infrastructure\Services;

use App\Infrastructure\Utils\StringUtils;

/**
 * Utilities used to generate record identifiers from metadata.
 */
class IdGeneratorUtils
{
    /**
     * @param array  $metadata type metadata
     * @param string $keyname  primary key name
     *
     * @return string[] field names used to build the id, empty if not generated
     */
    public static function getSources(array $metadata, string $keyname): array
    {
        foreach ($metadata as $field) {
            if (isset($field->name) && $field->name === $keyname && !empty($field->generated)) {
                $names = explode(',', (string) $field->generated);

                return array_values(array_filter(array_map('trim', $names)));
            }
        }

        return [];
    }

     public static function assignGeneratedId(string $type, string $keyname, \stdClass $record, array $metadata): string
    {
        if (!empty($record->{$keyname})) {
            return $record->{$keyname};
        }

        
        $sources = IdGeneratorUtils::getSources($metadata, $keyname);
        if ($sources === []) {
            throw new \Exception('No generated ID sources available');
        }

        $parts = [];
        $dateParts = [];
        foreach ($sources as $field) {
            if (empty($record->{$field})) {
                continue;
            }
            $slug = IdGeneratorUtils::slug($metadata, $field, (string) $record->{$field});
            if ($slug !== '') {
                $isDate = false;
                foreach ($metadata as $metadataField) {
                    if (isset($metadataField->name, $metadataField->editor)
                        && $metadataField->name === $field
                        && $metadataField->editor === 'date'
                    ) {
                        $isDate = true;
                        break;
                    }
                }

                if ($isDate) {
                    $dateParts[] = $slug;
                } else {
                    $parts[] = $slug;
                }
            }
        }

        $parts = array_merge($dateParts, $parts);

        if ($parts === []) {
            throw new \Exception('No valid sources for generated ID');
        }

        $baseId = implode('-', $parts);
        $id = $baseId;
        /*
        $suffix = 2;
        while (file_exists($this->getItemFileName($type, $id, $record))) {
            $id = $baseId.'-'.$suffix;
            ++$suffix;
        }
*/
        $record->{$keyname} = $id;
        return $id;
    }

    public static function slug(array $metadata, string $fieldName, string $value): string
    {
        foreach ($metadata as $field) {
            if (isset($field->name, $field->editor)
                && $field->name === $fieldName
                && $field->editor === 'date'
            ) {
                return self::slugify(substr($value, 0, 4));
            }
        }

        return self::slugify($value);
    }


    /**
     * Convert a value to a filename-safe slug.
     * eg: "Été kendo 2026" -> "ete-kendo-2026"
     */
    public static function slugify(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (function_exists('iconv')) {
            $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if ($transliterated !== false) {
                $value = $transliterated;
            }
        }

        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        if ($value === null) {
            return '';
        }

        return trim($value, '-');
    }
}