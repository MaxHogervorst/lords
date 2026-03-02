<?php

namespace App\Models;

use App\Models\Concerns\HasManualAutoIncrement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    use HasFactory;
    use HasManualAutoIncrement;

    protected $table = 'group_member';
}
