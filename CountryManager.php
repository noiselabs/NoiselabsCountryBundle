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

namespace NoiseLabs\Bundle\CountryBundle;

/**
 * Country Manager.
 *
 * @author Vítor Brandão <vitor@noiselabs.org>
 */
class CountryManager
{
    /**
     * @var string
     * Path to the directory containing countries data.
     */
    protected $dataDir;

    /**
     * @var array
     * Cached data.
     */
    protected $dataCache = array();

    /**
     * @var array
     * Available data sources.
     */
    protected $dataSources = array('icu', 'cldr');

    /**
     * Constructor.
     *
     * @param string|null $dataDir Path to the directory containing countries data
     */
    public function __construct($dataDir = null)
    {
        if (!isset($dataDir)) {
            $r = new \ReflectionClass('Umpirsky\Country\Builder\Builder');
            $dataDir = sprintf('%s/../../../../country', dirname($r->getFileName()));
        }

        if (!is_dir($dataDir)) {
            throw new \RuntimeException(sprintf('Unable to locate the country data directory at "%s"', $dataDir));
        }

        $this->dataDir = realpath($dataDir);
    }

    /**
     * @return string The country data directory.
     */
    public function getDataDir()
    {
        return $this->dataDir;
    }

    /**
     * @param string $locale The locale
     * @param string $source Data source: "icu" or "cldr"
     *
     * @return array
     */
    public function getList($locale = 'en', $source = 'cldr')
    {
        return $this->loadData($locale, strtolower($source));
    }

    /**
     * @param string $locale The locale
     * @param string $source Data source.
     * @param array  $data   An array (list) with country data
     */
    public function setList($locale, $source, array $data)
    {
        $this->dataCache[$locale][strtolower($source)] = $data;

        return $this;
    }

    /**
     * A lazy-loader that loads data from a PHP file if it is not stored in
     * memory yet.
     *
     * @param string $locale The locale
     * @param string $source Data source.
     *
     * @return array An array (list) with country
     */
    protected function loadData($locale, $source)
    {
        if (!isset($this->dataCache[$locale][$source])) {
            if (!in_array($source, $this->dataSources)) {
                throw new \InvalidArgumentException(sprintf('Unknown data source "%s". The available ones are: "%s"',
                $source, implode('", "', $this->dataSources)));
            }

            $file = sprintf('%s/%s/%s/country.php', $this->dataDir, $source, $locale);
            if (!is_file($file)) {
                throw new \RuntimeException(sprintf('Unable to load the country data file "%s"', $file));
            }

            $this->dataCache[$locale][$source] = require_once $file;
        }

        return $this->sortData($locale, $this->dataCache[$locale][$source]);
    }

    /**
     * Sorts the data array for a given locale, using the locale translations.
     * It is UTF-8 aware if the Collator class is available (requires the intl
     * extension).
     *
     * @param string $locale The locale whose collation rules should be used.
     * @param array  $data   Array of strings to sort.
     *
     * @return array The $data array, sorted.
     */
    protected function sortData($locale, $data)
    {
        if (class_exists('Collator')) {
            $collator = new \Collator($locale);
            $collator->asort($data);
        } else {
            asort($data);
        }

        return $data;
    }
}
