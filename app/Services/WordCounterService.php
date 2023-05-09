<?php

namespace App\Services;

class WordCounterService {
    public function count(string $text): int
    {
        return str_word_count($text);
    }
}
