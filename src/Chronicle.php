<?php

namespace Kenarkose\Chronicle;

use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Model;

class Chronicle {

    /**
     * @var bool
     */
    protected $paused = false;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * Constructor
     *
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * Records an activity
     *
     * @param Model     $model
     * @param string    $name
     * @param Model|int $user
     *
     * @return Model
     */
    public function record(Model $model = null, $name, $user = null)
    {
        if ( ! $this->isEnabled())
        {
            return false;
        }

        $activity = $this->initActivity();

        // Auto determine user if none is supplied
        $user = $this->getUserId($user);

        $data = [
            'name'    => $name,
            'user_id' => $user
        ];

        if ($model)
        {
            $data['subject_id'] = $model->getKey();
            $data['subject_type'] = get_class($model);
        }

        $activity->fill($data);

        $activity->save();

        return $activity;
    }

    /**
     * Deletes an activity
     *
     * @param Model     $model
     * @param Model|int $user
     *
     * @return Model
     */
    public function delete(Model $model, $user = null)
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $activity = $this->initActivity();

        // Auto determine user if none is supplied
        $user = $this->getUserId($user);

        $data = [
            'user_id'      => $user,
            'subject_id'   => $model->getKey(),
            'subject_type' => get_class($model)
        ];

        $activity->where($data)->delete();
    }

    /**
     * Checks if recording is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return ( ! $this->paused && $this->config->get('chronicle.enabled', true));
    }

    /**
     * Returns the model name
     *
     * @return string
     */
    public function getModelName()
    {
        return $this->config->get('chronicle.model', 'Kenarkose\Chronicle\Activity');
    }

    /**
     * New up and return the Activity class
     *
     * @return mixed
     */
    protected function initActivity()
    {
        $modelName = $this->getModelName();

        return new $modelName;
    }

    /**
     * Determines current user
     *
     * @param Model|int $user
     * @return Model
     */
    protected function getUserId($user)
    {
        if (is_null($user))
        {
            $user = auth()->user();
        }

        if ($user instanceof Model)
        {
            $user = $user->getKey();
        }

        return $user;
    }

    /**
     * Getter for single record
     *
     * @param int $id
     * @return Model|null
     */
    public function getRecord($id)
    {
        $modelName = $this->getModelName();

        return $modelName::with('subject')->find($id);
    }

    /**
     * Getter for all records
     *
     * @return Collection
     */
    public function getAllRecords()
    {
        return $this->getRecords();
    }

    /**
     * Getter for records
     *
     * @param int|null $limit
     * @param array|string|null $name
     * @return Collection
     */
    public function getRecords($limit = null, $name = null)
    {
        $modelName = $this->getModelName();

        $activity = $modelName::with('subject');

        if ( ! is_null($limit))
        {
            $activity->limit($limit);
        }

        if ($name)
        {
            if ( ! is_array($name))
            {
                $name = [$name];
            }

            $activity->whereIn('name', $name);
        }

        return $activity->latest()->get();
    }

    /**
     * Getter for a users activity
     *
     * @param Model|int $user
     * @param int|null $limit
     * @param array|string|null $name
     * @return Collection
     */
    public function getUserActivity($user, $limit = null, $name = null)
    {
        $user = $this->getUserId($user);

        $modelName = $this->getModelName();

        $activity = $modelName::belongsToUser($user);

        if ( ! is_null($limit))
        {
            $activity->limit($limit);
        }

        if ($name)
        {
            if ( ! is_array($name))
            {
                $name = [$name];
            }

            $activity->whereIn('name', $name);
        }

        return $activity->latest()->get();
    }

    /**
     * Getter for records that are older than a time
     * relative to current time
     *
     * @param Carbon|int $time
     * @return Collection
     */
    public function getActivitiesOlderThan($time)
    {
        $modelName = $this->getModelName();

        return $modelName::olderThan($time)->get();
    }

    /**
     * Flushes all activities
     */
    public function flush()
    {
        $modelName = $this->getModelName();

        $modelName::truncate();
    }

    /**
     * Flushes activities older than a time
     *
     * @param Carbon|int $time
     */
    public function flushOlderThan($time)
    {
        $modelName = $this->getModelName();

        $modelName::olderThan($time)->delete();
    }

    /**
     * Pauses recording
     */
    public function pauseRecording()
    {
        $this->paused = true;
    }

    /**
     * Resumes auto-recording
     */
    public function resumeRecording()
    {
        $this->paused = false;
    }
}
