<?php

namespace App\Traits;

use Illuminate\Support\Str;
use App\Banner;

trait GenerateUniqueSlugBanner
{

    public function generateUniqueSlug(string $slug): string
    {
        // Check if the slug already has a number at the end
        $originalSlug = $slug;
        $slugNumber = null;
        $existingSlugs = $this->getExistingSlugs($slug);

        if (!in_array($slug, $existingSlugs)) {
            return Str::slug($slug);
        }

        return Str::slug($slug . '-' . mt_rand(1000, 9999));
    }

    private function getExistingSlugs(string $slug): array
    {
        return Banner::where('slug', 'LIKE', $slug . '%')
            ->where('banner_id', '!=', $this->banner_id ?? null) // Exclude the current model's ID
            ->pluck('slug')
            ->toArray();
    }
}
