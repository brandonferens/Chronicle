<?php

use Carbon\Carbon;
use Illuminate\Config\Repository;
use Kenarkose\Chronicle\Chronicle;

class ChronicleTest extends TestBase {

    protected function getChronicle()
    {
        $repository = new Repository([]);

        return new Chronicle($repository);
    }

    protected function getPost()
    {
        return Post::create([]);
    }

    protected function getOldActivity($subject, $created)
    {
        $activity = new Activity([
            'subject_id'   => $subject->getKey(),
            'subject_type' => get_class($subject),
            'user_id'      => 1,
            'name'         => 'created_post'
        ]);

        $time = Carbon::createFromTimestamp($created)->toDateTimeString();

        $activity->setCreatedAt($time);
        $activity->setUpdatedAt($time);

        $activity->save();

        return $activity;
    }

    /** @test */
    function it_records_an_activity()
    {
        $chronicle = $this->getChronicle();

        $post = $this->getPost();

        $record = $chronicle->record(
            $post,
            'created_post',
            1
        );

        $this->assertEquals(
            $record->user_id,
            1
        );

        $this->assertEquals(
            $record->name,
            'created_post'
        );

        $this->assertInstanceOf(
            'Kenarkose\Chronicle\Activity',
            $record
        );

        $this->assertEquals(
            $record->subject->getKey(),
            $post->getKey()
        );
    }

    /** @test */
    function it_records_an_activity_with_configured_model()
    {
        $chronicle = new Chronicle(
            new Repository(['chronicle' => ['model' => 'Activity']])
        );

        $record = $chronicle->record(
            $this->getPost(),
            'created_post',
            1
        );

        $this->assertInstanceOf(
            'Activity',
            $record
        );
    }

    /** @test */
    function it_auto_assigns_currently_logged_in_user()
    {
        $chronicle = $this->getChronicle();

        $post = $this->getPost();

        // Let's log a user in
        $user = User::create(['email' => 'john@doe.com']);
        auth()->login($user);

        $record = $chronicle->record(
            $post,
            'created_post'
        );

        $this->assertEquals(
            $record->user_id,
            $user->getKey()
        );
    }

    /** @test */
    function it_accepts_user_as_key_or_model()
    {
        $chronicle = $this->getChronicle();

        $post1 = $this->getPost();
        $post2 = $this->getPost();

        $user = User::create(['email' => 'john@doe.com']);

        $record1 = $chronicle->record(
            $post1,
            'created_post',
            $user->id
        );

        $this->assertEquals(
            $record1->subject_id,
            $post1->id
        );

        $this->assertEquals(
            $record1->user_id,
            $user->id
        );

        $record2 = $chronicle->record(
            $post2,
            'created_post',
            $user
        );

        $this->assertEquals(
            $record2->subject_id,
            $post2->id
        );

        $this->assertEquals(
            $record2->user_id,
            $user->id
        );
    }

    /** @test */
    function it_gets_a_certain_record()
    {
        $chronicle = $this->getChronicle();

        $post = $this->getPost();

        $record = $chronicle->record(
            $post,
            'created_post',
            1
        );

        $retrievedRecord = $chronicle->getRecord($record->getKey());

        $this->assertEquals(
            $record->getKey(),
            $retrievedRecord->getKey()
        );

        $this->assertNull(
            $chronicle->getRecord(42)
        );
    }

    /** @test */
    function it_gets_all_activity()
    {
        $chronicle = $this->getChronicle();

        $this->assertCount(
            0,
            $chronicle->getAllRecords()
        );

        $post = $this->getPost();

        $chronicle->record(
            $post,
            'created_post',
            1
        );

        $this->assertCount(
            1,
            $chronicle->getAllRecords()
        );

        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Collection',
            $chronicle->getAllRecords()
        );
    }

    /** @test */
    function it_gets_activity_of_user()
    {
        $chronicle = $this->getChronicle();

        $post = $this->getPost();

        $user = User::create(['email' => 'john@doe.com']);

        // Testing with model input here
        $this->assertCount(
            0,
            $chronicle->getUserActivity($user)
        );

        $record = $chronicle->record(
            $post,
            'created_post',
            $user
        );

        // Testing with id here
        $this->assertCount(
            1,
            $chronicle->getUserActivity($user->getKey())
        );

        $this->assertEquals(
            $record->user_id,
            $user->getKey()
        );

        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Collection',
            $chronicle->getUserActivity($user->getKey())
        );
    }

    /** @test */
    function it_gets_activities_older_than_a_time()
    {
        $chronicle = $this->getChronicle();

        $current = time();

        $created = $current - 133742;

        $post1 = $this->getPost();
        $post2 = $this->getPost();

        // We manually create an activity so that we can enter
        // a custom created date
        $activityOld = $this->getOldActivity($post1, $created);

        // We than create a current activity to be able to compare
        $activityNew = $chronicle->record(
            $post2,
            'created_post',
            1
        );

        $this->assertCount(
            2,
            $chronicle->getAllRecords()
        );

        // Testing with carbon object, directly at created time, since '<='
        $oldActivities = $chronicle->getActivitiesOlderThan(
            Carbon::createFromTimestamp($created)
        );

        $this->assertCount(
            1,
            $oldActivities
        );

        $this->assertNotNull(
            $oldActivities->find($activityOld->getKey())
        );

        $this->assertNull(
            $oldActivities->find($activityNew->getKey())
        );

        // Testing with timestamp object, just a minute more current
        $oldActivities = $chronicle->getActivitiesOlderThan($created + 60);

        $this->assertCount(
            1,
            $oldActivities
        );

        $this->assertNotNull(
            $oldActivities->find($activityOld->getKey())
        );

        $this->assertNull(
            $oldActivities->find($activityNew->getKey())
        );

        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Collection',
            $chronicle->getActivitiesOlderThan(time())
        );
    }

    /** @test */
    function it_flushes_all_activity()
    {
        $chronicle = $this->getChronicle();

        $post = $this->getPost();

        $chronicle->record(
            $post,
            'created_post',
            1
        );

        $chronicle->record(
            $post,
            'updated_post',
            1
        );

        $chronicle->record(
            $post,
            'deleted_post',
            1
        );

        $this->assertCount(
            3,
            $chronicle->getAllRecords()
        );

        $chronicle->flush();

        $this->assertCount(
            0,
            $chronicle->getAllRecords()
        );
    }

    /** @test */
    function it_flushes_activities_older_than_a_time()
    {
        $chronicle = $this->getChronicle();

        $current = time();

        $created = $current - 133742;

        $post1 = $this->getPost();
        $post2 = $this->getPost();

        // We manually create an activity so that we can enter
        // a custom created date
        $activityOld = $this->getOldActivity($post1, $created);

        // We than create a current activity to be able to compare
        $activityNew = $chronicle->record(
            $post2,
            'created_post',
            1
        );

        $this->assertCount(
            2,
            $chronicle->getAllRecords()
        );

        // Testing with carbon object, directly at created time, since '<='
        $oldActivities = $chronicle->getActivitiesOlderThan(
            Carbon::createFromTimestamp($created)
        );

        $this->assertCount(
            1,
            $oldActivities
        );

        $chronicle->flushOlderThan(
            Carbon::createFromTimestamp($created)
        );

        $oldActivities = $chronicle->getActivitiesOlderThan(
            Carbon::createFromTimestamp($created)
        );

        $this->assertCount(
            0,
            $oldActivities
        );

        $this->assertNull(
            $chronicle->getRecord($activityOld->getKey())
        );

        $this->assertNotNull(
            $chronicle->getRecord($activityNew->getKey())
        );

        // We manually create another one
        $activityOld = $this->getOldActivity($post1, $created);

        $this->assertCount(
            2,
            $chronicle->getAllRecords()
        );

        // Testing with timestamp object, just a minute more current
        $oldActivities = $chronicle->getActivitiesOlderThan($created + 60);

        $this->assertCount(
            1,
            $oldActivities
        );

        $chronicle->flushOlderThan($created + 60);

        $oldActivities = $chronicle->getActivitiesOlderThan($created + 60);

        $this->assertCount(
            0,
            $oldActivities
        );

        $this->assertNull(
            $chronicle->getRecord($activityOld->getKey())
        );

        $this->assertNotNull(
            $chronicle->getRecord($activityNew->getKey())
        );
    }

}