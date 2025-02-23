<?php

namespace App\Strategies\Posts;

interface SortingStrategy
{
    public function applySorting($query);
}
