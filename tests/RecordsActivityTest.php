<?php

use Kenarkose\Chronicle\Activity;

class RecordsActivityTest extends TestBase {

    public function setUp()
    {
        parent::setUp();

        // We need to login a default user for convenience
        $user = User::create(['email' => 'john@doe.com']);

        auth()->login($user);
    }

    protected function getCategory()
    {
        return Category::create([]);
    }

    protected function getComment($user_id = 1)
    {
        return Comment::create(['user_id' => $user_id]);
    }

    protected function getQuote($owner_id = 1)
    {
        return Quote::create(['owner_id' => $owner_id]);
    }

    /** @test */
    function it_records_activities_on_event()
    {
        $this->assertCount(
            0,
            chronicle()->getAllRecords()
        );

        $category = $this->getCategory();

        $this->assertCount(
            1,
            chronicle()->getAllRecords()
        );

        $this->assertNotNull(
            chronicle()->getRecord(
                $category->getKey()
            )
        );
    }

    /** @test */
    function it_records_different_events()
    {
        $this->assertCount(
            0,
            chronicle()->getAllRecords()
        );

        $quote = $this->getQuote();

        $this->assertCount(
            1,
            chronicle()->getAllRecords()
        );

        $quote->owner_id = 2;
        $quote->save();

        $this->assertCount(
            2,
            chronicle()->getAllRecords()
        );

        $quote->delete();

        $this->assertCount(
            3,
            chronicle()->getAllRecords()
        );
    }

    /** @test */
    function it_records_only_determined_events()
    {
        $this->assertCount(
            0,
            chronicle()->getAllRecords()
        );

        $comment = $this->getComment();

        $this->assertCount(
            1,
            $this->app->make('chronicle')->getAllRecords()
        );

        $comment->user_id = 2;
        $comment->save();

        $this->assertCount(
            1,
            chronicle()->getAllRecords()
        );

        $comment->delete();

        $this->assertCount(
            1,
            chronicle()->getAllRecords()
        );
    }

    /** @test */
    function it_associates_current_logged_in_user_if_no_user_key_is_specified_and_default_key_is_null()
    {
        $this->assertCount(
            0,
            chronicle()->getAllRecords()
        );

        $category = $this->getCategory();

        $this->assertCount(
            1,
            chronicle()->getAllRecords()
        );

        $this->assertEquals(
            Activity::where('subject_id', $category->id)->first()->user_id,
            auth()->user()->id
        );
    }

    /** @test */
    function it_associates_default_user_key_if_it_has_a_value()
    {
        $this->assertCount(
            0,
            chronicle()->getAllRecords()
        );

        $comment = $this->getComment(42);

        $this->assertCount(
            1,
            chronicle()->getAllRecords()
        );

        $this->assertEquals(
            Activity::where('subject_id', $comment->id)->first()->user_id,
            42
        );
    }

    /** @test */
    function it_associates_special_user_key_if_isset()
    {
        $this->assertCount(
            0,
            chronicle()->getAllRecords()
        );

        $quote = $this->getQuote(42);

        $this->assertCount(
            1,
            chronicle()->getAllRecords()
        );

        $this->assertEquals(
            Activity::where('subject_id', $quote->id)->first()->user_id,
            42
        );
    }

}