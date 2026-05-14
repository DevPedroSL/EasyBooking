<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $this->prepareCompiledViewsPath();

        $app = parent::createApplication();

        $app['config']->set('view.compiled', $this->compiledViewsPath());

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareCompiledViewsPath();

        config(['view.compiled' => $this->compiledViewsPath()]);
    }

    private function prepareCompiledViewsPath(): void
    {
        $compiledViewsPath = $this->compiledViewsPath();

        if (! is_dir($compiledViewsPath)) {
            mkdir($compiledViewsPath, 0775, true);
        }

        putenv('VIEW_COMPILED_PATH='.$compiledViewsPath);
        $_ENV['VIEW_COMPILED_PATH'] = $compiledViewsPath;
        $_SERVER['VIEW_COMPILED_PATH'] = $compiledViewsPath;
    }

    private function compiledViewsPath(): string
    {
        return sys_get_temp_dir().'/easybooking-testing-views-'.substr(md5((string) realpath(__DIR__.'/..')), 0, 12);
    }
}
