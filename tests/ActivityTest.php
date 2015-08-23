<?php

use Kenarkose\Chronicle\Activity;

class ActivityTest extends TestBase {

    public function setUp()
    {
        parent::setUp();

        config()->set('auth.model', 'User');
    }

    protected function getPost()
    {
        return Post::create([]);
    }

    protected function getUser()
    {
        return User::create(['email' => 'john@doe.com']);
    }

    protected function getActivity($user_id = null, $post = null)
    {
        $user_id = $user_id ?: 1;
        $post = $post ?: $this->getPost();

        return Activity::create([
            'subject_id'   => $post->getKey(),
            'subject_type' => get_class($post),
            'name'         => 'created_post',
            'user_id'      => $user_id
        ]);
    }

    /** @test */
    function it_has_user_relation()
    {
        $user = $this->getUser();

        $activity = $this->getActivity($user->getKey());

        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Relations\BelongsTo',
            $activity->user()
        );

        $this->assertInstanceOf(
            'User',
            $activity->user
        );

        $this->assertEquals(
            $user->getKey(),
            $activity->user->getKey()
        );
    }

    /** @test */
    function it_has_subject_relation()
    {
        $user = $this->getUser();
        $post = $this->getPost();

        $activity = $this->getActivity($user->getKey(), $post);

        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Relations\MorphTo',
            $activity->subject()
        );

        $this->assertInstanceOf(
            'Post',
            $activity->subject
        );

        $this->assertEquals(
            $post->getKey(),
            $activity->subject->getKey()
        );
    }

    /** @test */
    function it_has_belongs_to_user_scope()
    {
        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Builder',
            Activity::belongsToUser(1)
        );
    }

    /** @test */
    function it_has_older_than_scope()
    {
        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Builder',
            Activity::olderThan(1333333)
        );
    }

}