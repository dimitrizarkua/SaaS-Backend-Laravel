<?php

namespace CodeStandard\Sniffs\Commenting;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Class TestClassCommentSniff
 *
 * @package PHP_CodeSniffer\Standards\CodeStandard\Sniffs\Commenting
 */
class TestClassCommentSniff extends ClassCommentSniff
{
    public $requiredTags = [
        '@package',
        '@group',
    ];

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_CLASS];
    }//end register()
}//end class
