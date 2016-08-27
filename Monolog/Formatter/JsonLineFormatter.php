<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Monolog\Formatter;

use Monolog\Formatter\LineFormatter;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JsonLineFormatter extends LineFormatter
{
    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $vars = parent::format($record);

        foreach ($vars['extra'] as $var => $val) {
            $vars['extra'][$var] = $this->stringify($val);
        }

        foreach ($vars['context'] as $var => $val) {
            $vars['context'][$var] = $this->stringify($val);
        }

        foreach ($vars as $var => $val) {
            $vars[$var] = $this->stringify($val);
        }

        return json_encode($record);
    }
}