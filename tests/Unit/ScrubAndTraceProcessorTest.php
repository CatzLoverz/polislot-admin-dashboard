<?php

namespace Tests\Unit;

use App\Logging\Processors\ScrubAndTraceProcessor;
use App\Models\IotDevice;
use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Processors\MySqlProcessor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class ScrubAndTraceProcessorTest extends TestCase
{
    public function test_it_extracts_method_name_from_closure_backtrace(): void
    {
        $processor = new ScrubAndTraceProcessor();

        // We run a closure. Inside the closure, we invoke the processor.
        $runClosure = function() use ($processor) {
            $record = [
                'message' => 'Test message',
                'context' => [],
                'extra' => [],
            ];
            return $processor($record);
        };

        $result = $runClosure();

        // The expected prefix should be [APP ScrubAndTraceProcessorTest@test_it_extracts_method_name_from_closure_backtrace]
        $this->assertStringContainsString(
            '[APP ScrubAndTraceProcessorTest@test_it_extracts_method_name_from_closure_backtrace]',
            $result['message']
        );
    }

    public function test_it_syncs_mqtt_status_to_offline_due_to_inactivity(): void
    {
        Event::fake();
        
        // Mock DB connection to avoid hitting actual database
        $mockConnection = Mockery::mock(Connection::class);
        $mockConnection->shouldReceive('query')->andReturnUsing(function() use ($mockConnection) {
            $grammar = new MySqlGrammar($mockConnection);
            $processor = new MySqlProcessor();
            return new QueryBuilder($mockConnection, $grammar, $processor);
        });
        $mockConnection->shouldReceive('select')->andReturn([]);
        $mockConnection->shouldReceive('getTablePrefix')->andReturn('');
        $mockConnection->shouldReceive('getName')->andReturn('mariadb');
        
        $resolver = Mockery::mock(ConnectionResolverInterface::class);
        $resolver->shouldReceive('connection')->andReturn($mockConnection);
        Model::setConnectionResolver($resolver);

        $mac = 'C0:35:32:17:EC:53';
        
        // Put in cache that device is online via mqtt
        Cache::forever("iot_status_{$mac}", 'online');
        Cache::forever("iot_connection_type_{$mac}", 'mqtt');
        
        // Set last seen to 70 seconds ago
        Cache::put("iot_last_seen_{$mac}", time() - 70);

        // Call syncStatus
        $status = IotDevice::syncStatus($mac);

        // It should return 'offline' and update the cache status to 'offline'
        $this->assertEquals('offline', $status);
        $this->assertEquals('offline', Cache::get("iot_status_{$mac}"));
    }

    public function test_it_keeps_mqtt_status_online_when_recently_seen(): void
    {
        Event::fake();
        $mac = 'C0:35:32:17:EC:53';
        
        // Put in cache that device is online via mqtt
        Cache::forever("iot_status_{$mac}", 'online');
        Cache::forever("iot_connection_type_{$mac}", 'mqtt');
        
        // Set last seen to 20 seconds ago
        Cache::put("iot_last_seen_{$mac}", time() - 20);

        // Call syncStatus
        $status = IotDevice::syncStatus($mac);

        // It should remain 'online'
        $this->assertEquals('online', $status);
        $this->assertEquals('online', Cache::get("iot_status_{$mac}"));
    }
}
