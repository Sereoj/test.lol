<?php

namespace App\Utils;

use Illuminate\Support\Str;

class TextUtil
{
    public static function generateUniqueSlug($text, $count): string
    {
        $slug = Str::slug($text);

        if ($count > 0) {
            $slug .= '-'.($count + 1);
        }

        return $slug;
    }

    public static function defaultMeta()
    {
        return [
            'title' => [
                'ru' => '',
                'en' => '',
            ],
            'description' => [
                'ru' => '',
                'en' => '',
            ],
        ];
    }

    // Маппинг для кириллицы → латиница
    public static function transliterate(string $text): string
    {
        $map = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
            'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k',
            'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
            'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        ];

        return strtr($text, $map);
    }

    // Маппинг для латиницы → кириллица (ошибки раскладки)
    public static function keyboardMistake(string $text): string
    {
        $map = [
            'q' => 'й', 'w' => 'ц', 'e' => 'у', 'r' => 'к', 't' => 'е', 'y' => 'н', 'u' => 'г',
            'i' => 'ш', 'o' => 'щ', 'p' => 'з', '[' => 'х', ']' => 'ъ', 'a' => 'ф', 's' => 'ы',
            'd' => 'в', 'f' => 'а', 'g' => 'п', 'h' => 'р', 'j' => 'о', 'k' => 'л', 'l' => 'д',
            ';' => 'ж', '\'' => 'э', 'z' => 'я', 'x' => 'ч', 'c' => 'с', 'v' => 'м', 'b' => 'и',
            'n' => 'т', 'm' => 'ь', ',' => 'б', '.' => 'ю', '/' => '.',
        ];

        return strtr($text, $map);
    }

    // Обработка вариантов
    public static function generateVariants(string $text): array
    {
        $variants = [];

        // Оригинал
        $variants[] = $text;
        $variants[] = str($text)->slug();

        // Проверяем, содержит ли строка кириллические символы
        if (self::containsCyrillic($text)) {
            // Если строка на кириллице, то делаем транслитерацию (перевод кириллицы в латиницу)
            $transliterated = self::transliterate($text);
            $variants[] = $transliterated;

            // Проверяем ошибку раскладки для транслитерированной строки
            $mistake = self::keyboardMistake($transliterated);
            if ($mistake != $text) {
                $variants[] = $mistake;
            }
        } else {
            // Если строка на латинице, проверяем ошибку раскладки (перевод латиницы в кириллицу)
            $keyboardMistake = self::keyboardMistake($text);
            $variants[] = $keyboardMistake;

            // Применяем ошибочную раскладку для того случая, если мы получили латиницу, но ожидаем кириллицу
            $mistake = self::keyboardMistake(self::transliterate($text));
            if ($mistake != $text && $mistake != $keyboardMistake) {
                $variants[] = $mistake;
            }
        }

        // Удаляем дубликаты и пустые значения
        return array_unique(array_filter($variants));
    }

    // Метод для проверки, содержит ли строка кириллические символы
    public static function containsCyrillic(string $text): bool
    {
        return preg_match('/[а-яА-ЯёЁ]/u', $text) > 0;
    }
}
