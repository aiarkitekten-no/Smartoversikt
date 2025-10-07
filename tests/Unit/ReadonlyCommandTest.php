<?php

namespace Tests\Unit;

use App\Support\Sys\ReadonlyCommand;
use Tests\TestCase;

class ReadonlyCommandTest extends TestCase
{
    /**
     * Test at whitelisted kommandoer godkjennes
     */
    public function test_whitelisted_command_is_allowed(): void
    {
        $result = ReadonlyCommand::run('cat /proc/loadavg');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('output', $result);
        $this->assertArrayHasKey('error', $result);
    }
    
    /**
     * Test at ikke-whitelisted kommandoer blokkeres
     */
    public function test_non_whitelisted_command_is_blocked(): void
    {
        $result = ReadonlyCommand::run('rm -rf /');
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Command not whitelisted', $result['error']);
    }
    
    /**
     * Test at farlige mønstre blokkeres
     */
    public function test_dangerous_patterns_are_blocked(): void
    {
        $result = ReadonlyCommand::run('cat /proc/loadavg', ['>', '/tmp/test']);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Dangerous pattern detected', $result['error']);
    }
    
    /**
     * Test at whitelist kan hentes
     */
    public function test_whitelist_can_be_retrieved(): void
    {
        $whitelist = ReadonlyCommand::getWhitelist();
        
        $this->assertIsArray($whitelist);
        $this->assertNotEmpty($whitelist);
        $this->assertContains('cat /proc/loadavg', $whitelist);
    }
}
