<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Shared helper for admin/clinic forms that edit Spatie-translatable
 * fields in every supported locale (config/clinicest.php). Each such
 * field is held on the component as a locale-keyed array (e.g.
 * $name = ['en' => '...', 'tr' => '...']) and bound in the view via the
 * <x-translatable-field> component.
 */
trait WithTranslations
{
    /**
     * The translatable component properties, mapped to the model attribute
     * they persist to. Usually identical, e.g. ['name' => 'name'].
     *
     * @return array<string, string>
     */
    abstract protected function translatableFields(): array;

    /**
     * Seed each locale-keyed property from the model, guaranteeing every
     * supported locale is present as a key so the tab inputs all bind.
     */
    protected function fillTranslations(Model $model): void
    {
        $locales = config('clinicest.locales.supported', ['en']);

        foreach ($this->translatableFields() as $property => $attribute) {
            $existing = $model->getTranslations($attribute);

            $this->{$property} = collect($locales)
                ->mapWithKeys(fn ($code) => [$code => $existing[$code] ?? ''])
                ->all();
        }
    }

    /**
     * Write the locale-keyed properties back onto the model. Empty locales
     * are dropped rather than stored as "" — Spatie only falls back to the
     * default locale when a translation key is absent, so persisting an
     * empty string would surface a blank instead of the fallback.
     */
    protected function applyTranslations(Model $model): void
    {
        foreach ($this->translatableFields() as $property => $attribute) {
            $model->setTranslations($attribute, array_filter($this->{$property}, fn ($value) => filled($value)));
        }
    }

    /**
     * Empty locale-keyed defaults for a fresh (create) form.
     *
     * @param  array<int, string>  $properties
     */
    protected function emptyTranslations(array $properties): void
    {
        $locales = config('clinicest.locales.supported', ['en']);
        $blank = collect($locales)->mapWithKeys(fn ($code) => [$code => ''])->all();

        foreach ($properties as $property) {
            $this->{$property} = $blank;
        }
    }

    /**
     * Build validation rules for locale-keyed fields across every
     * supported locale. Only the primary (default) locale of a
     * required field is required; all other locales are optional
     * translations.
     *
     * @param  array<string, array{required?: bool, max?: int|null}>  $fields
     * @return array<string, array<int, string>>
     */
    protected function translationRules(array $fields): array
    {
        $locales = config('clinicest.locales.supported', ['en']);
        $primary = $locales[0] ?? 'en';
        $rules = [];

        foreach ($fields as $field => $options) {
            $required = $options['required'] ?? false;
            $max = array_key_exists('max', $options) ? $options['max'] : 255;

            foreach ($locales as $code) {
                $rule = [($required && $code === $primary) ? 'required' : 'nullable', 'string'];

                if ($max !== null) {
                    $rule[] = 'max:'.$max;
                }

                $rules["{$field}.{$code}"] = $rule;
            }
        }

        return $rules;
    }
}
