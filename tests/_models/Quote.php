<?php

use Illuminate\Database\Eloquent\Model as Eloquent;
use Kenarkose\Chronicle\RecordsActivity;

class Quote extends Eloquent {

    use RecordsActivity;

    protected $userKey = 'owner_id';

    protected $fillable = ['owner_id'];

}