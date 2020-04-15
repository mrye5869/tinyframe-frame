<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <55585190@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace og\console\Symfony\Component\Console\Helper;

use og\console\Symfony\Component\Console\Descriptor\DescriptorInterface;
use og\console\Symfony\Component\Console\Descriptor\JsonDescriptor;
use og\console\Symfony\Component\Console\Descriptor\MarkdownDescriptor;
use og\console\Symfony\Component\Console\Descriptor\TextDescriptor;
use og\console\Symfony\Component\Console\Descriptor\XmlDescriptor;
use og\console\Symfony\Component\Console\Exception\InvalidArgumentException;
use og\console\Symfony\Component\Console\Output\OutputInterface;

/**
 * This class adds helper method to describe objects in various formats.
 *
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
class DescriptorHelper extends Helper
{
    /**
     * @var DescriptorInterface[]
     */
    private $descriptors = [];

    public function __construct()
    {
        $this
            ->register('txt', new TextDescriptor())
            ->register('xml', new XmlDescriptor())
            ->register('json', new JsonDescriptor())
            ->register('md', new MarkdownDescriptor())
        ;
    }

    /**
     * Describes an object if supported.
     *
     * Available options are:
     * * format: string, the output format name
     * * raw_text: boolean, sets output type as raw
     *
     * @param object $object
     *
     * @throws InvalidArgumentException when the given format is not supported
     */
    public function describe(OutputInterface $output, $object, array $options = [])
    {
        $options = array_merge([
            'raw_text' => false,
            'format' => 'txt',
        ], $options);

        if (!isset($this->descriptors[$options['format']])) {
            throw new InvalidArgumentException(sprintf('Unsupported format "%s".', $options['format']));
        }

        $descriptor = $this->descriptors[$options['format']];
        $descriptor->describe($output, $object, $options);
    }

    /**
     * Registers a descriptor.
     *
     * @param string $format
     *
     * @return $this
     */
    public function register($format, DescriptorInterface $descriptor)
    {
        $this->descriptors[$format] = $descriptor;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'descriptor';
    }
}
