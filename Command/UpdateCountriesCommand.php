<?php
/**
 * This file is part of NoiseLabs-CountryBundle
 *
 * NoiseLabs-CountryBundle is free software; you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * NoiseLabs-CountryBundle is distributed in the hope that it will be
 * useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with NoiseLabs-CountryBundle; if not, see
 * <http://www.gnu.org/licenses/>.
 *
 * Copyright (C) 2012 Vítor Brandão
 *
 * @category    NoiseLabs
 * @package     CountryBundle
 * @copyright   (C) 2013 Vítor Brandão <vitor@noiselabs.org>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL-3
 * @link        http://www.noiselabs.org
 */

namespace NoiseLabs\Bundle\CountryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Umpirsky\Country\Builder\Builder;

/**
 * Update countries.
 *
 * @author Vítor Brandão <vitor@noiselabs.org>
 */
class UpdateCountriesCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('noiselabs:countries:update')
            ->setDescription('Update or create the list of countries in the application cache using ICU and CLDR importers')
            ->setHelp(<<<EOF
The <info>noiselabs:countries:update</info> command updates or creates the list
of countries in the application cache using ICU and CLDR importers.

EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!class_exists('Zend_Locale') || !class_exists('Symfony\Component\Locale\Locale')) {
            throw new \RuntimeException("The procedure to create/update the countries cache requires extra libraries.\n".
            "To install them please re-run the composer script with --dev:\n\n".
            "  $ composer update --dev\n");
        }

        $cacheDir = $this->getContainer()->getParameter('kernel.cache_dir');
        if (!is_dir($cacheDir)) {
            if (false === @mkdir($cacheDir, 0777, true)) {
                throw new \RuntimeException(sprintf("Unable to create the countries cache directory (%s)\n", $cacheDir));
            }
        } elseif (!is_writable($cacheDir)) {
            throw new \RuntimeException(sprintf("Unable to write in the countries cache directory (%s)\n", $cacheDir));
        }

        $builder = new Builder($cacheDir.'/noiselabs/countries');
        $output->writeln(sprintf('Generating countries data in <comment>%s</comment>', $cacheDir));
        $builder->run();
    }
}
