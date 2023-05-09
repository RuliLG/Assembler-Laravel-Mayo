<?php

namespace Tests\Unit;

use App\Services\WordCounterService;
use PHPUnit\Framework\TestCase;

class WordCounterTest extends TestCase
{
    public function test_works_with_empty_string(): void
    {
        $counter = new WordCounterService();
        $this->assertEquals($counter->count(''), 0);
    }

    public function test_works_with_only_spaces(): void
    {
        $counter = new WordCounterService();
        $this->assertEquals($counter->count('      '), 0);
    }

    public function test_works_with_text(): void
    {
        $counter = new WordCounterService();
        $this->assertEquals($counter->count('hola'), 1);
        $this->assertEquals($counter->count('hola hola'), 2);
    }
}
