<?php

use Illuminate\Database\Eloquent\Model as Eloquent;
use Kenarkose\Chronicle\RecordsActivity;

class Comment extends Eloquent {

    use RecordsActivity;

    protected static $recordEvents = ['created'];

    protected $fillable = ['user_id'];

}