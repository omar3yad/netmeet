<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanGatewayRelation extends Model
{
    use HasFactory;

    protected $table = 'payment_gateway_plan_relation';

    protected $fillable = ['plan_id', 'plan_id_gateway','payment_gateway'];

    public $timestamps = false;


}
