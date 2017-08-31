<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShootOrder extends Model
{
	protected $fillable = ["start_date","address","phone","contact","note"];

}
