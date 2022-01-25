<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GraphSearchFilters extends Model
{
    use HasFactory;

    protected $table = 'graph_search_criterias';

    protected $guarded = [];
}
