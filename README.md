# Chronicle
User activity logging for Laravel 5.

---
[![Build Status](https://travis-ci.org/kenarkose/Chronicle.svg?branch=master)](https://travis-ci.org/kenarkose/Chronicle)
[![Total Downloads](https://poser.pugx.org/kenarkose/Chronicle/downloads)](https://packagist.org/packages/kenarkose/Chronicle)
[![Latest Stable Version](https://poser.pugx.org/kenarkose/Chronicle/version)](https://packagist.org/packages/kenarkose/Chronicle)
[![License](https://poser.pugx.org/kenarkose/Chronicle/license)](https://packagist.org/packages/kenarkose/Chronicle)

Chronicle provides a simple way to record user activity for Laravel 5 and is [based on a Laracasts lesson](https://github.com/laracasts/Build-An-Activity-Feed-in-Laravel). Chronicle extends the functionality that is offered by the lesson.

## Features
- Compatible with Laravel 5
- Clean API for recording user activity
- Auto-recording of activities by hooking up to model events
- Time and user based activity filtering
- Flushing activities for clean-up
- Enabling/disabling and pausing/resuming recording activities.
- A [phpunit](http://www.phpunit.de) test suite for easy development

## Installation
Installing Chronicle is simple.

1. Pull this package in through [Composer](https://getcomposer.org).

    ```js
    {
        "require": {
            "kenarkose/chronicle": "~1.4"
        }
    }
    ```

2. In order to register Chronicle Service Provider add `'Kenarkose\Chronicle\ChronicleServiceProvider'` to the end of `providers` array in your `config/app.php` file.
    ```php
    'providers' => array(
    
        'Illuminate\Foundation\Providers\ArtisanServiceProvider',
        'Illuminate\Auth\AuthServiceProvider',
        ...
        'Kenarkose\Chronicle\ChronicleServiceProvider',
    
    ),
    ```

3. You may configure the default behaviour of Chronicle by publishing and modifying the configuration file. Furthermore, you need to publish the migration for the Kenarkose\Chronicle\Activity model to be able to persist activities. To do so, use the following command.
    ```bash
    php artisan vendor:publish
    ```
    Than, you will find the configuration file on the `config/chronicle.php` path. Information about the options can be found in the comments of this file. All of the options in the config file are optional, and falls back to default if not specified; remove an option if you would like to use the default. Do not forget to migrate when the migration file is published.

4. You may access the services provided by Chronicle by using the supplied Facades or with the chronicle() helper method.
    ```php
    chronicle()->record($post, 'created_post', $user_id);

    Chronicle::getAllRecords();

    Chronicle::getRecords($limit);

    chronicle()->getUserActivity(Auth::user());
    // You can further limit activity to certain event types
    chronicle()->getUserActivity(Auth::user(), null, 'created_reply');
    chronicle()->getUserActivity(Auth::user(), null, ['created_reply', 'created_post']);

    Chronicle::getActivitiesOlderThan($carbon);

    chronicle()->flushOlderThan($unixTimestamp);
    
    // You can pause and resume recording if you like for the request
    chronicle()->pauseRecording();
    // --- non-recorded actions
    chronicle()->resumeRecording();
    ```

    In order to register the Facade add the following line to the end of `aliases` array in your `config/app.php` file.
    ```php
    'aliases' => array(
    
        'App'        => 'Illuminate\Support\Facades\App',
        'Artisan'    => 'Illuminate\Support\Facades\Artisan',
        ...
        'Chronicle'   => 'Kenarkose\Chronicle\ChronicleFacade'
    
    ),
    ```

    A good place to register chronicle recording actions are inside the 'App\Providers\EventServiceProvider' class that is provided by Laravel 5.

5. Additionally, you may use the 'Kenarkose\Chronicle\RecordsActivity' trait for automatically recording 'created', 'updated', and 'deleted' model events by default on any Eloquent model. Furthermore, you may specify which events are going to be recorded by defining the static `$recordEvents` property.
    ```php
    namespace App;

    use Kenarkose\Chronicle\RecordsActivity;

    class Post
    {
        use RecordsActivity;

        protected static $recordEvents = ['created'];
    }
    ```
    
6. Finally, you may find it necessary for a recorded activity to be removed if its subject has been removed. For example, a user replied to a post, but then decided to delete that reply. It may not be necessary to keep the activity record around, so the static property `$deleteActivityOnCascade` can be set to automatically remove the activity.
    ```php
    namespace App;

    use Kenarkose\Chronicle\RecordsActivity;

    class Post
    {
        use RecordsActivity;

        protected static $deleteActivityOnCascade = true;
    }
    ```

Please check the tests and source code for further documentation, as the source code of Chronicle is well tested and documented.

## License
Chronicle is released under [MIT License](https://github.com/kenarkose/Chronicle/blob/master/LICENSE).
