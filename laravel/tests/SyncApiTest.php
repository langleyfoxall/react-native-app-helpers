<?php

namespace JamesWildDev\ReactNativeAppHelpers\Tests;

use Illuminate\Support\Facades\Route;
use JamesWildDev\ReactNativeAppHelpers\SyncApi;
use PHPUnit\Framework\TestCase;
use Mockery;
use Hamcrest\Matchers;

final class SyncApiTest extends TestCase
{
  public function setUp(): void
  {
    parent::setUp();

    Route::spy();
  }

  public function testGenerateRoutes(): void
  {
    $syncApi = (new SyncApi())
      ->withMe(ExampleSingletonA::class, ExampleSingletonA::class)
      ->withMe(ExampleSingletonB::class, ExampleSingletonB::class)
      ->withCollection(
        ExampleCollectionAModel::class,
        'exampleTest',
        ExampleSingletonA::class,
        ExampleCollectionAController::class,
      )
      ->withCollection(
        ExampleCollectionBModel::class,
        'exampleTest',
        ExampleSingletonB::class,
        ExampleCollectionBController::class,
      );

    $syncApi->generateRoutes();

    Route::shouldHaveReceived('get')
      ->with('preflight', Matchers::callableValue())
      ->once();

    // TODO assert preflight response.

    Route::shouldHaveReceived('get')
      ->with('example-singleton-a', Matchers::callableValue())
      ->once();

    // TODO assert singleton responses.

    Route::shouldHaveReceived('get')
      ->with('example-singleton-b', Matchers::callableValue())
      ->once();

    Route::shouldHaveReceived('get')
      ->with(
        'example-collection-a-models/{uuid}',
        Matchers::callableValue()
      )
      ->once();

    Route::shouldHaveReceived('put')
      ->with(
        'example-collection-a-models/{uuid}',
        [ExampleCollectionAController::class, 'upsert']
      )
      ->once();

    Route::shouldHaveReceived('get')
      ->with(
        'example-collection-b-models/{uuid}',
        Matchers::callableValue()
      )
      ->once();

    Route::shouldHaveReceived('put')
      ->with(
        'example-collection-b-models/{uuid}',
        [ExampleCollectionBController::class, 'upsert']
      )
      ->once();

    Route::shouldHaveReceived('get')
      ->times(5);

    Route::shouldHaveReceived('put')
      ->times(2);

    $unexpectedRouteMethods = [
      'post',
      'patch',
      'delete',
      'options',
      'match',
      'any',
      'redirect',
      'permanentRedirect',
      'view',
      'pattern',
      'middleware',
      'group',
      'domain',
      'prefix',
      'name',
      'scopeBindings',
      'bind',
      'fallback',
      'current',
      'currentRouteName',
      'currentRouteAction',
    ];

    foreach ($unexpectedRouteMethods as $unexpectedRouteMethod) {
      Route::shouldNotReceive($unexpectedRouteMethod);
    }
  }

  public function tearDown(): void
  {
    parent::tearDown();

    if ($container = Mockery::getContainer()) {
      $this->addToAssertionCount($container->mockery_getExpectationCount());
    }

    Mockery::close();
  }
}

final class ExampleCollectionAModel {}

final class ExampleCollectionBModel {}

final class ExampleCollectionAController
{
  public function upsert(): void {}
}

final class ExampleCollectionBController
{
  public function upsert(): void {}
}
