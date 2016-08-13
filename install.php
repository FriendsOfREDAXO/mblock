<?php
/**
 * @copyright Copyright (c) 2015 by Joachim Doerr
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo5
 * @version 4.0.0
 * @license MIT
 */

// set default template
if (!$this->hasConfig()) {
    $this->setConfig('jblock_template', 'default_theme');
}

// copy data directory
rex_dir::copy($this->getPath('data'), $this->getDataPath());