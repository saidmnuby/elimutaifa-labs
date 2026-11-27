<?php

namespace App\Support;

use App\Models\PlatformSetting;
use Carbon\Carbon;

class PaymentConfiguration
{
    private const SETTING_KEY = 'payment_configuration';

    public static function settings(): array
    {
        $defaults = [
            'account_name' => config('platform.payment_account_name'),
            'pricing' => config('platform.plus_pricing'),
            'offer' => [
                'enabled' => false,
                'title' => '',
                'monthly' => null,
                'yearly' => null,
                'starts_at' => null,
                'ends_at' => null,
            ],
            'accounts' => collect(config('platform.payment_accounts', []))
                ->map(fn (array $account) => $account + ['enabled' => true])
                ->all(),
        ];

        $saved = PlatformSetting::query()->whereKey(self::SETTING_KEY)->value('value');
        $saved = is_string($saved) ? json_decode($saved, true) : null;
        if (! is_array($saved)) {
            return $defaults;
        }

        return array_replace_recursive($defaults, $saved);
    }

    public static function save(array $settings): void
    {
        PlatformSetting::query()->updateOrCreate(
            ['key' => self::SETTING_KEY],
            ['value' => json_encode($settings, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES)],
        );
    }

    public static function accounts(bool $activeOnly = false): array
    {
        $accounts = self::settings()['accounts'];

        return $activeOnly
            ? array_filter($accounts, fn (array $account) => (bool) ($account['enabled'] ?? true))
            : $accounts;
    }

    public static function accountName(): string
    {
        return self::settings()['account_name'];
    }

    public static function pricing(): array
    {
        $settings = self::settings();
        $base = $settings['pricing'];
        $offer = $settings['offer'];
        $active = (bool) ($offer['enabled'] ?? false)
            && self::withinOfferWindow($offer['starts_at'] ?? null, $offer['ends_at'] ?? null)
            && (($offer['monthly'] ?? null) || ($offer['yearly'] ?? null));

        return [
            'monthly' => $active && ! empty($offer['monthly']) ? (int) $offer['monthly'] : (int) $base['monthly'],
            'yearly' => $active && ! empty($offer['yearly']) ? (int) $offer['yearly'] : (int) $base['yearly'],
            'base_monthly' => (int) $base['monthly'],
            'base_yearly' => (int) $base['yearly'],
            'offer_active' => $active,
            'offer_title' => $active ? (string) ($offer['title'] ?: 'Special offer') : '',
        ];
    }

    public static function priceFor(string $billingCycle): int
    {
        $pricing = self::pricing();

        return $billingCycle === 'yearly' ? $pricing['yearly'] : $pricing['monthly'];
    }

    private static function withinOfferWindow(?string $startsAt, ?string $endsAt): bool
    {
        $now = now();

        return (! $startsAt || $now->greaterThanOrEqualTo(Carbon::parse($startsAt)))
            && (! $endsAt || $now->lessThanOrEqualTo(Carbon::parse($endsAt)));
    }
}
