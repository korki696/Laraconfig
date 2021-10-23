<?php

namespace DarkGhostHunter\Laraconfig\Eloquent\Casts;

use DarkGhostHunter\Laraconfig\Eloquent\SettingMetadata;
use DarkGhostHunter\Laraconfig\Eloquent\Setting;
use DateTimeInterface;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @internal
 */
class DynamicCasting implements CastsAttributes
{
    /**
     * Transform the attribute from the underlying model values.
     *
     * @param  \DarkGhostHunter\Laraconfig\Eloquent\Setting|\DarkGhostHunter\Laraconfig\Eloquent\SettingMetadata  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     *
     * @return array|bool|float|int|string|\Illuminate\Support\Collection|\DateTimeInterface|null
     * @throws \JsonException
     */
    public function get(
        $model,
        string $key,
        $value,
        array $attributes
    ): array|bool|float|int|string|Collection|DateTimeInterface|null {
        if (null === $value) {
            return null;
        }

        if ($model instanceof Setting && !isset($attributes['type'], $attributes['metadata_id'])) {
            return $value;
        }

        return match ($attributes['type'] ??= SettingMetadata::whereKey($attributes['metadata_id'])->value('type')) {
            SettingMetadata::TYPE_ARRAY => Arr::wrap(json_decode($value, true, 512, JSON_THROW_ON_ERROR)),
            SettingMetadata::TYPE_BOOLEAN => (bool) $value,
            SettingMetadata::TYPE_DATETIME => Carbon::parse($value),
            SettingMetadata::TYPE_COLLECTION => new Collection(Arr::wrap(json_decode($value, true, 512, JSON_THROW_ON_ERROR))),
            SettingMetadata::TYPE_FLOAT => (float) $value,
            SettingMetadata::TYPE_INTEGER => (int) $value,
            default => $value,
        };
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param  \DarkGhostHunter\Laraconfig\Eloquent\Setting|\DarkGhostHunter\Laraconfig\Eloquent\SettingMetadata  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     *
     * @return null|array|int|bool|float|string|\Illuminate\Support\Collection|\DateTimeInterface
     * @throws \JsonException
     */
    public function set(
        $model,
        string $key,
        $value,
        array $attributes
    ): null|array|int|bool|float|string|Collection|DateTimeInterface {
        if (null === $value) {
            return null;
        }

        if ($model instanceof Setting && !isset($attributes['type'], $attributes['metadata_id'])) {
            return $value;
        }

        return match ($attributes['type'] ??= SettingMetadata::whereKey($attributes['metadata_id'])->value('type')) {
            SettingMetadata::TYPE_COLLECTION,
            SettingMetadata::TYPE_ARRAY => json_encode(is_array($value) ? Arr::wrap($value) : $value, JSON_THROW_ON_ERROR),
            SettingMetadata::TYPE_BOOLEAN => (bool) $value,
            SettingMetadata::TYPE_DATETIME => Carbon::parse($value),
            SettingMetadata::TYPE_STRING => (string) $value,
            SettingMetadata::TYPE_INTEGER => (int) $value,
            SettingMetadata::TYPE_FLOAT => (float) $value,
            default => $value,
        };
    }
}
