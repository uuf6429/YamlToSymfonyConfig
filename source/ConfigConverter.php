<?php

class ConfigConverter
{
    /**
     * @var null|array
     */
    protected $config;

    /**
     * @var null|PhpWriter
     */
    protected $writer;

    /**
     * @param array $configs
     * @return $this
     */
    public function from(array $configs)
    {
        if (!is_null($this->config)) {
            throw new RuntimeException('Conversion already in progress!');
        }

        $this->config = $configs;

        return $this;
    }

    /**
     * @param PhpWriter $writer
     * @return $this
     */
    public function to(PhpWriter $writer)
    {
        if (is_null($this->config)) {
            throw new RuntimeException('Nothing to convert from yet!');
        }

        try {
            $this->writer = $writer;

            $this->convert();
        } finally {
            $this->config = null;
            $this->writer = null;
        }

        return $this;
    }

    protected function convert()
    {
        $this->writer
            ->useClass(\Symfony\Component\Config\Definition\Builder\TreeBuilder::class)
            ->line()
            ->line('$treeBuilder = new TreeBuilder();')
            ->line('$rootNode = $treeBuilder->root(\'default\');')
            ->line('$rootNode')
            ->incIndent()
                ->line('->children()')
                ->incIndent()
        ;

        foreach ($this->config as $yaml) {
            foreach ($yaml as $rootName => $rootContent) {
                $this->handleBranch($rootName, $rootContent);
            }

            break; // TODO Temporary: quit on first config, in the future we should enhance config detect by looking at parallel config
        }

        $this->writer
                ->decIndent()
                ->line('->end()')
            ->decIndent()
            ->line(';')
        ;
    }

    /**
     * @param array $value
     * @return bool
     */
    private function isAssoc(array $value)
    {
        if (!count($value)) {
            return false;
        }
        return array_keys($value) !== range(0, count($value) - 1);
    }

    /**
     * @param string $name
     * @param mixed $content
     */
    private function handleBranch($name, $content)
    {
        switch (true) {
            case is_array($content):
                if ($this->isAssoc($content)) {
                    $this->writer
                        ->line(sprintf('->arrayNode(%s)', var_export($name, true)))
                        ->incIndent()
                            ->line('->children()')
                            ->incIndent();

                    foreach ($content as $subName => $subContent) {
                        $this->handleBranch($subName, $subContent);
                    }

                    $this->writer
                            ->decIndent()
                            ->line('->end()')
                        ->decIndent()
                        ->line('->end()');

                    break;
                } else {
                    $this->writer
                        ->line(sprintf('->arrayNode(%s)', var_export($name, true)))
                        ->incIndent()
                            ->line('->prototype(\'array\')')
                            ->incIndent()
                                ->line('->children()')
                                ->incIndent();

                    foreach ($content as $subName => $subContent) {
                        $this->handleBranch($subName, $subContent);
                    }

                    $this->writer
                                ->decIndent()
                                ->line('->end()')
                            ->decIndent()
                            ->line('->end()')
                        ->decIndent()
                        ->line('->end()');

                    break;
                }
            case is_int($content):
                $this->writer
                    ->line(sprintf('->integerNode(%s)', var_export($name, true)))
                    ->incIndent();

                // TODO more stuff?

                $this->writer
                    ->decIndent()
                    ->line('->end()');
                break;
            case is_bool($content):
                $this->writer
                    ->line(sprintf('->booleanNode(%s)', var_export($name, true)))
                    ->incIndent();

                // TODO more stuff?

                $this->writer
                    ->decIndent()
                    ->line('->end()');
                break;
            case is_float($content):
                $this->writer
                    ->line(sprintf('->floatNode(%s)', var_export($name, true)))
                    ->incIndent();

                // TODO more stuff?

                $this->writer
                    ->decIndent()
                    ->line('->end()');
                break;
            /*case is_scalar($content):
                return new \PhpParser\Node\Expr\MethodCall(
                    new \PhpParser\Node\Expr\MethodCall(
                        $prevNode,
                        'scalarNode',
                        [
                            new \PhpParser\Node\Scalar\String_($name)
                        ]
                    ),
                    'end'
                );*/
        }
    }
}
