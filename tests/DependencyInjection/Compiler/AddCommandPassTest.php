<?php
declare(strict_types=1);

namespace AlexMasterov\PsyshBundle\Tests\DependencyInjection\Compiler;

use AlexMasterov\PsyshBundle\DependencyInjection\Compiler\AddCommandPass;
use AlexMasterov\PsyshBundle\Tests\DependencyInjection\CanContainer;
use PHPUnit\Framework\TestCase;
use stdClass;

final class AddCommandPassTest extends TestCase
{
    use CanContainer;

    /** @test */
    public function it_valid_processed_when_no_shell(): void
    {
        // Stub
        $container = $this->getContainer();

        // Execute
        $container->compile();

        // Verify
        self::assertFalse($this->hasAddCommandsCall($container));
    }

    /** @test */
    public function it_valid_processed_when_no_tags(): void
    {
        // Stub
        $container = $this->getContainer();
        $container->register('psysh.shell', stdClass::class)->setPublic(true);

        // Execute
        $container->compile();

        // Verify
        self::assertFalse($this->hasAddCommandsCall($container));
    }

    /** @test */
    public function it_valid_processed_when_tagged(): void
    {
        // Stub
        $command = 'test_command';

        $container = $this->getContainer();
        $container->register('psysh.shell', stdClass::class)->setPublic(true);
        $container->register('psysh.config', stdClass::class)->setPublic(true);
        $container->register('test_command', stdClass::class)->setPublic(true)
            ->addTag('psysh.command');

        // Execute
        $container->compile();

        // Verify
        self::assertTrue($this->hasAddCommandsCall($container));
        self::assertContains($command, $this->getCommands($container));
    }

    private function getContainer()
    {
        $container = $this->container();
        $container->addCompilerPass(new AddCommandPass());

        return $container;
    }

    private function hasAddCommandsCall($container): bool
    {
        return $this->hasDefinitionMethodCall('psysh.config', 'addCommands', $container);
    }

    private function getCommands($container): array
    {
        $commands = $this->getDefinitionMethodArguments('psysh.config', 'addCommands', $container);

        return array_map(
            static function ($command) {
                return (string) $command;
            },
            $commands
        );
    }
}
