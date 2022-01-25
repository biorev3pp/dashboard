<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;use Illuminate\Database\Eloquent\Model;
class SearchCriteriaAccounts extends Model{    use HasFactory;    protected $table = 'account_search_criterias';    protected $guarded = [];
}