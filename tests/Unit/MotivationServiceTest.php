<?php

namespace Tests\Unit;

use App\Domain\Motivation\Contracts\MotivationProviderInterface;
use App\Domain\Motivation\Enums\MotivationType;
use App\Domain\Motivation\Services\MotivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MotivationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_generate_message_with_provider(): void
    {
        $mockProvider = \Mockery::mock(MotivationProviderInterface::class);
        $mockProvider->shouldReceive('generate')
            ->once()
            ->with(\Mockery::type(MotivationType::class))
            ->andReturn('Тестовое мотивационное сообщение');

        $service = new MotivationService($mockProvider);
        $message = $service->generateMessage();

        $this->assertIsString($message);
        $this->assertEquals('Тестовое мотивационное сообщение', $message);
    }

    public function test_uses_fallback_when_provider_returns_null(): void
    {
        $mockProvider = \Mockery::mock(MotivationProviderInterface::class);
        $mockProvider->shouldReceive('generate')
            ->once()
            ->andReturn(null);

        $service = new MotivationService($mockProvider);
        $message = $service->generateMessage();

        $this->assertIsString($message);
        $this->assertNotEmpty($message);
    }

    public function test_can_generate_message_with_specific_type(): void
    {
        $mockProvider = \Mockery::mock(MotivationProviderInterface::class);
        $mockProvider->shouldReceive('generate')
            ->once()
            ->with(MotivationType::JOKE)
            ->andReturn('Тестовая шутка');

        $service = new MotivationService($mockProvider);
        $message = $service->generateMessage(MotivationType::JOKE);

        $this->assertIsString($message);
        $this->assertEquals('Тестовая шутка', $message);
    }

    public function test_fallback_messages_are_different_for_each_type(): void
    {
        $mockProvider = \Mockery::mock(MotivationProviderInterface::class);
        $mockProvider->shouldReceive('generate')
            ->andReturn(null);

        $service = new MotivationService($mockProvider);

        $motivational = $service->generateMessage(MotivationType::MOTIVATIONAL);
        $joke = $service->generateMessage(MotivationType::JOKE);
        $fact = $service->generateMessage(MotivationType::FACT);
        $quote = $service->generateMessage(MotivationType::QUOTE);

        $this->assertIsString($motivational);
        $this->assertIsString($joke);
        $this->assertIsString($fact);
        $this->assertIsString($quote);
        $this->assertNotEmpty($motivational);
        $this->assertNotEmpty($joke);
        $this->assertNotEmpty($fact);
        $this->assertNotEmpty($quote);
    }
}

