<?php

namespace App\Services;

use App\Models\Category;

class CategorySuggestionService
{
    private array $keywordMap = [
        'grocery' => 'Groceries',
        'supermarket' => 'Groceries',
        'market' => 'Groceries',
        'food' => 'Groceries',
        'restaurant' => 'Dining Out',
        'jollibee' => 'Dining Out',
        'mcdonald' => 'Dining Out',
        'kfc' => 'Dining Out',
        'dining' => 'Dining Out',
        'cafe' => 'Dining Out',
        'coffee' => 'Dining Out',
        'starbucks' => 'Dining Out',
        'grab' => 'Transport',
        'taxi' => 'Transport',
        'gas' => 'Transport',
        'fuel' => 'Transport',
        'petrol' => 'Transport',
        'fare' => 'Transport',
        'jeep' => 'Transport',
        'bus' => 'Transport',
        'shop' => 'Shopping',
        'mall' => 'Shopping',
        'clothing' => 'Shopping',
        'amazon' => 'Shopping',
        'lazada' => 'Shopping',
        'shopee' => 'Shopping',
        'online' => 'Shopping',
        'electric' => 'Utilities',
        'water' => 'Utilities',
        'bill' => 'Utilities',
        'internet' => 'Utilities',
        'phone' => 'Utilities',
        'rent' => 'Rent',
        'apartment' => 'Rent',
        'doctor' => 'Health',
        'hospital' => 'Health',
        'pharmacy' => 'Health',
        'medicine' => 'Health',
        'gym' => 'Health',
        'netflix' => 'Entertainment',
        'spotify' => 'Entertainment',
        'movie' => 'Entertainment',
        'game' => 'Entertainment',
        'concert' => 'Entertainment',
        'salary' => 'Salary',
        'paycheck' => 'Salary',
        'wage' => 'Salary',
        'freelance' => 'Freelance',
        'upwork' => 'Freelance',
        'fiverr' => 'Freelance',
        'consult' => 'Freelance',
        'dividend' => 'Investment',
        'stock' => 'Investment',
        'interest' => 'Investment',
    ];

    public function suggest(string $description, ?string $type = null): ?array
    {
        if (empty(trim($description))) {
            return null;
        }

        $lower = strtolower($description);
        $matchedKeywords = [];

        foreach ($this->keywordMap as $keyword => $categoryName) {
            if (str_contains($lower, $keyword)) {
                $matchedKeywords[] = [
                    'keyword' => $keyword,
                    'category' => $categoryName,
                ];
            }
        }

        if (empty($matchedKeywords)) {
            return null;
        }

        // If user has categories, match to their custom ones
        if (auth()->check()) {
            $userCategories = Category::where('user_id', auth()->id())
                ->when($type, fn ($q) => $q->where('type', $type))
                ->get()
                ->keyBy('name');

            $bestMatch = null;
            foreach ($matchedKeywords as $match) {
                if (isset($userCategories[$match['category']])) {
                    $bestMatch = $userCategories[$match['category']];
                    break;
                }
            }

            if ($bestMatch) {
                return [
                    'id' => $bestMatch->id,
                    'name' => $bestMatch->name,
                    'color' => $bestMatch->color,
                    'icon' => $bestMatch->icon,
                    'matched_keyword' => $matchedKeywords[0]['keyword'],
                ];
            }
        }

        // Fallback: return the first match
        return [
            'id' => null,
            'name' => $matchedKeywords[0]['category'],
            'color' => null,
            'icon' => null,
            'matched_keyword' => $matchedKeywords[0]['keyword'],
        ];
    }

    public function suggestFromText(string $text, ?string $type = null): ?array
    {
        return $this->suggest($text, $type);
    }
}
