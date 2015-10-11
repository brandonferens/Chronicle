<?php

namespace Kenarkose\Chronicle;


use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Model;

class Chronicle {

    /**
     * Name of the activity class
     *
     * @var string
     */
    protected $modelName;

    /**
     * Constructor
     *
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->modelName = $config->get('chronicle.model', 'Kenarkose\Chronicle\Activity');
    }

    /**
     * Records an activity
     *
     * @param Model $model
     * @param string $name
     * @param Model|int $user
     * @return Model
     */
    public function record(Model $model, $name, $user = null)
    {
        $activity = new $this->modelName;

        // Auto determine user if none is supplied
        $user = $this->getUserId($user);

        $activity->fill([
            'subject_id'   => $model->getKey(),
            'subject_type' => get_class($model),
            'name'         => $name,
            'user_id'      => $user
        ]);

        $activity->save();

        return $activity;
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

        if($user instanceof Model)
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
        $modelName = $this->modelName;

        return $modelName::find($id);
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
     * @return Collection
     */
    public function getRecords($limit = null)
    {
        $modelName = $this->modelName;

        if(is_null($limit))
        {
            return $modelName::all();
        }

        return $modelName::limit($limit)->get();
    }

    /**
     * Getter for a users activity
     *
     * @param Model|int $user
     * @param int|null $limit
     * @return Collection
     */
    public function getUserActivity($user, $limit = null)
    {
        $user = $this->getUserId($user);

        $modelName = $this->modelName;

        $activity = $modelName::belongsToUser($user);

        if(!is_null($limit))
        {
            $activity->limit($limit);
        }

        return $activity->get();
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
        $modelName = $this->modelName;

        return $modelName::olderThan($time)->get();
    }

    /**
     * Flushes all activities
     */
    public function flush()
    {
        $modelName = $this->modelName;

        $modelName::truncate();
    }

    /**
     * Flushes activities older than a time
     *
     * @param Carbon|int $time
     */
    public function flushOlderThan($time)
    {
        $modelName = $this->modelName;

        $modelName::olderThan($time)->delete();
    }

}