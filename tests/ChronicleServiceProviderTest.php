<?php

class ChronicleServiceProviderTest extends TestBase {

    /** @test */
    function it_registers_chronicle_helper()
    {
        $this->assertInstanceOf(
            'Kenarkose\Chronicle\Chronicle',
            chronicle()
        );
    }

    /** @test */
    function it_registers_chronicle_instance()
    {
        $this->assertInstanceOf(
            'Kenarkose\Chronicle\Chronicle',
            $this->app->make('chronicle')
        );
    }

}