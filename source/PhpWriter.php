<?php

class PhpWriter
{
    /**
     * @var string[]
     */
    protected $lines = [];

    /**
     * @var int
     */
    protected $indent = 0;

    /**
     * @return $this
     */
    public function incIndent()
    {
        $this->indent++;

        return $this;
    }

    /**
     * @return $this
     */
    public function decIndent()
    {
        $this->indent--;

        return $this;
    }

    /**
     * @param null|string $line
     * @return $this
     */
    public function line($line = null)
    {
        $this->lines[] = is_null($line) ? '' : ($this->indentation() . $line);

        return $this;
    }

    /**
     * @param string $fqcn
     * @param null|string $asFqcn
     * @return $this
     */
    public function useClass($fqcn, $asFqcn = null)
    {
        $line = $this->indentation();
        $line .= 'use ' . $fqcn;

        if ($asFqcn) {
            $line .= ' as ' . $asFqcn;
        }

        $this->lines[] = $line;

        return $this;
    }

    /**
     * @param string $className
     * @param bool $isAbstract
     * @param array $extends
     * @param array $implements
     * @return $this
     */
    public function beginClass($className, $isAbstract = false, $extends = [], $implements = [])
    {
        $line = $this->indentation();

        if ($isAbstract) {
            $line .= 'abstract ';
        }

        $line .= 'class ' . $className;

        if (count($extends)) {
            $line .= ' extends ' . implode(', ', $extends);
        }

        if (count($implements)) {
            $line .= ' implements ' . implode(', ', $implements);
        }

        $this->lines[] = $line . '{';

        return $this;
    }

    /**
     * @return $this
     */
    public function endClass()
    {
        $this->lines[] = $this->indentation() . '}';

        return $this;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return implode(PHP_EOL, $this->lines) . PHP_EOL;
    }

    /**
     * @return string
     */
    protected function indentation()
    {
        return str_repeat('    ', $this->indent);
    }
}
