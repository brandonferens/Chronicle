<?php

namespace Kenarkose\Chronicle;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Activity extends Eloquent {

    /**
     * Fillable fields for the model
     *
     * @param array
     */
    protected $fillable = ['subject_id', 'subject_type', 'name', 'user_id'];

    /**
     * User relation for the activity
     *
     * @return User
     */
    public function user()
    {
        return $this->belongsTo(
            $this->getUserModelName()
        )->withTrashed();
    }

    /**
     * Get the user model name
     *
     * @return string
     */
    protected function getUserModelName()
    {
        return config()->get('chronicle.user_model', 'App\User');
    }

    /**
     * Subject relation for the activity
     *
     * @return mixed
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include activities of a certain user
     *
     * @param \Illuminate\Database\Eloquent\Builder
     * @param int $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBelongsToUser($query, $user)
    {
        return $query->where('user_id', $user)->latest();
    }

    /**
     * Scope a query to only include activities older than a timestamp
     *
     * @param \Illuminate\Database\Eloquent\Builder
     * @param Carbon|int $time
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOlderThan($query, $time)
    {
        if ( ! $time instanceof Carbon)
        {
            $time = Carbon::createFromTimestamp($time);
        }

        return $query->where('created_at', '<=', $time->toDateTimeString())->latest();
    }

    /**
     * Returns the activity subject name
     *
     * @return string
     */
    public function getSubjectName()
    {
        return str_plural(strtolower(class_basename($this->subject_type)));
    }

}