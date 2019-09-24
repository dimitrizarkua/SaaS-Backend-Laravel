<?php
namespace CodeStandard\Sniffs\Commenting;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Class ClassCommentSniff
 * @package CodeStandard\Sniffs\Commenting
*/
class ClassCommentSniff implements Sniff
{
    public $requiredTags = [
        '@package',
    ];

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_CLASS, T_INTERFACE];
    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $find   = Tokens::$methodPrefixes;
        $find[] = T_WHITESPACE;

        $commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1), null, true);
        if ($tokens[$commentEnd]['code'] !== T_DOC_COMMENT_CLOSE_TAG && $tokens[$commentEnd]['code'] !== T_COMMENT) {
            $class = $phpcsFile->getDeclarationName($stackPtr);
            $fix   = $phpcsFile->addFixableError('Missing doc comment for class %s', $stackPtr, 'Missing', [$class]);
            $phpcsFile->recordMetric($stackPtr, 'Class has doc comment', 'no');

            $start = $phpcsFile->findNext([T_CLASS, T_INTERFACE], $stackPtr);
            $type  = ucfirst($tokens[$start]['content']);
            if (true === $fix) {
                $fixer = $phpcsFile->fixer;
                $fixer->beginChangeset();
                $fixer->addContent($stackPtr - 1, "/**\n");
                $fixer->addContent($stackPtr - 1, ' * ' . $type . ' ' . $class . "\n");
                $fixer->addContent($stackPtr - 1, " *\n");
                $fixer->addContent($stackPtr - 1, " * @package " . $this->getNamespace($phpcsFile, $tokens) . "\n");
                $fixer->addContent($stackPtr - 1, " */\n");
                $fixer->endChangeset();
            }

            return;
        }

        $phpcsFile->recordMetric($stackPtr, 'Class has doc comment', 'yes');

        if ($tokens[$commentEnd]['code'] === T_COMMENT) {
            $phpcsFile->addError('You must use "/**" style comments for a class comment', $stackPtr, 'WrongStyle');

            return;
        }

        if ($tokens[$commentEnd]['line'] !== ($tokens[$stackPtr]['line'] - 1)) {
            $error = 'There must be no blank lines after the class comment';
            $phpcsFile->addError($error, $commentEnd, 'SpacingAfter');
        }

        $commentStart = $tokens[$commentEnd]['comment_opener'];

        if ($this->checkClassDeclaration($phpcsFile, $stackPtr, $commentStart, $commentEnd, $tokens)) {
            $this->checkTags($phpcsFile, $tokens, $commentStart, $commentEnd);
        }
    }//end process()

    /**
     * Checks that required tags are existing and has correct format.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param array                       $tokens
     * @param                             $commentEnd
     */
    private function checkTags(File $phpcsFile, array $tokens, $commentStart, $commentEnd): void
    {
        $tagsToCheck = $this->requiredTags;


        $counter = 0;
        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
            $data = [$tokens[$tag]['content']];
            if (!isset($data[0])) {
                continue;
            }

            $tagName = $data[0];

            //Skip OpenApi tags
            if (false !== strpos($tagName, '@OA')) {
                continue;
            }

            //Check empty line before first tag.
            if ($counter === 0) {
                $prevComment = $phpcsFile->findPrevious(T_DOC_COMMENT_STRING, $tag);
                $lines       = $tokens[$tag]['line'] - $tokens[$prevComment]['line'] - 1;
                if (1 !== $lines) {
                    $fix = $phpcsFile->addFixableError(
                        'Must be one empty line between class comment and tags section, %d lines found',
                        $tag,
                        'MissingEmptyLine',
                        [$lines]
                    );
                    if (true === $fix) {
                        $phpcsFile->fixer->beginChangeset();
                        $phpcsFile->fixer->addNewline($tag - 2);
                        $phpcsFile->fixer->addContent($tag - 2, ' *');
                        $phpcsFile->fixer->endChangeset();
                    }
                }
            }
            $counter++;

            $keyTag = array_search($tagName, $tagsToCheck);
            if (false !== $keyTag) {
                unset($tagsToCheck[$keyTag]);
            }

            $commentString = $tokens[$tag + 2];
            if ($commentString['code'] !== T_DOC_COMMENT_STRING) {
                $phpcsFile->addError('Missed comment string', $tag + 2, 'MissedCommentString');
                continue;
            }

            switch ($keyTag) {
                case '@package':
                    //Get Namespace
                    $ns = $this->getNamespace($phpcsFile, $tokens);
                    if ($ns !== $commentString['content']) {
                        $fix = $phpcsFile->addFixableError(
                            'Wrong comment described in @package section: %s found but %s is being expected',
                            $tag + 2,
                            'WrongCommentString',
                            [$commentString['content'], $ns]
                        );
                        if (true === $fix) {
                            $phpcsFile->fixer->beginChangeset();
                            $phpcsFile->fixer->replaceToken($tag + 2, $ns);
                            $phpcsFile->fixer->endChangeset();
                        }
                    }
                    break;
            }
        }


        foreach ($tagsToCheck as $tag) {
            switch ($tag) {
                case '@package':
                    $fix = $phpcsFile->addFixableError(
                        'Missed required tag %s for class comment',
                        $commentEnd,
                        'MessedTag',
                        [$tag]
                    );
                    if (true === $fix) {
                        $phpcsFile->fixer->beginChangeset();
                        $phpcsFile->fixer->addContent(
                            $commentEnd - 1,
                            "* @package " . $this->getNamespace($phpcsFile, $tokens) . "\n"
                        );
                        $phpcsFile->fixer->endChangeset();
                    }

                    break;
                default:
                    $phpcsFile->addError('Missed required tag %s for class comment', $commentEnd, 'MessedTag', [$tag]);
                    break;
            }
        }
    }

    /**
     * Checks that class declaration exists and has correct format.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param                             $stackPtr
     * @param                             $commentStart
     * @param array                       $tokens
     * @param                             $match
     *
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException
     */
    private function checkClassDeclaration(
        File $phpcsFile,
        $stackPtr,
        $commentStart,
        $commentEnd,
        array $tokens
    ): bool {
        $classDeclarationIndex = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $commentStart, $commentEnd);
        if (false === $classDeclarationIndex) {
            $phpcsFile->addError('Missing class declaration', $commentStart, 'Missing');

            return false;
        }

        $classDeclaration = $tokens[$classDeclarationIndex];
        $expected         = $phpcsFile->getDeclarationName($stackPtr);
        $start            = $phpcsFile->findNext([T_CLASS, T_INTERFACE], $stackPtr);
        $type             = ucfirst($tokens[$start]['content']);
        if (false == preg_match('/(Class|Interface) (.*)/', $classDeclaration['content'], $match)) {
            $fix = $phpcsFile->addFixableError('Wrong class declaration.', $commentStart, 'WrongClassDeclaration');
            if (true === $fix) {
                $phpcsFile->fixer->beginChangeset();
                $phpcsFile->fixer->replaceToken($classDeclarationIndex, $type . ' ' . $expected);
                $phpcsFile->fixer->endChangeset();
            }
        } else {
            $found = $match[2];

            if ($found !== $expected) {
                $fix = $phpcsFile->addFixableError(
                    'Wrong class declaration. Expected: \'%s %s\' but \'%s\' found ',
                    $commentStart,
                    'WrongClassDeclaration',
                    [$type, $expected, $classDeclaration['content']]
                );
                if (true === $fix) {
                    $phpcsFile->fixer->beginChangeset();
                    $phpcsFile->fixer->replaceToken($classDeclarationIndex, $type . ' ' . $expected);
                    $phpcsFile->fixer->endChangeset();
                }
            }
        }

        $classCommentIndex = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $classDeclarationIndex + 1, $commentEnd);
        $tagIndex          = $phpcsFile->findNext(T_DOC_COMMENT_TAG, $classDeclarationIndex + 1, $commentEnd);

        //If tag comment found then skip.
        if ($tagIndex < $classCommentIndex) {
            return false;
        }

        $classComment = trim($tokens[$classCommentIndex]['content']);

        //There must no empty lines between Class declaration and Class comment
        $lines = $tokens[$classCommentIndex]['line'] - $tokens[$classDeclarationIndex]['line'] - 1;
        if (0 !== $lines) {
            $fix = $phpcsFile->addFixableError(
                'There must no empty lines between class declaration and class comment, %d lines found',
                $classCommentIndex,
                'MissingEmptyLine',
                [$lines]
            );
            if (true === $fix) {
                try {
                    $phpcsFile->fixer->beginChangeset();
                    $phpcsFile->fixer->replaceToken($classCommentIndex - 2, '');
                    $phpcsFile->fixer->replaceToken($classCommentIndex - 3, '');
                    $phpcsFile->fixer->replaceToken($classCommentIndex - 4, '');
                    $phpcsFile->fixer->endChangeset();
                } catch (\PHP_CodeSniffer\Exceptions\RuntimeException $e) {
                    $phpcsFile->addError('The error can not be automatically fixed', 0, '');
                }
            }
        }

        //Comment must start with capital char.
        if (!is_numeric($classComment[0]) && false === ctype_upper($classComment[0])) {
            $fix = $phpcsFile->addFixableError(
                'The comment must start with capital char.',
                $classCommentIndex,
                'WrongClassDeclaration'
            );
            if (true === $fix) {
                $phpcsFile->fixer->beginChangeset();
                $phpcsFile->fixer->replaceToken($classCommentIndex, ucfirst($classComment));
                $phpcsFile->fixer->endChangeset();
            }
        }

        //Get last line of comment if last one is multiline.
        $lastCommentLineIndex = $classCommentIndex;
        $index                = $classCommentIndex;
        while (false !== $index) {
            $index = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $index + 1, $commentEnd);
            if (false !== $index && $index < $tagIndex) {
                $lastCommentLineIndex = $index;
            }
        }

        $classComment = trim($tokens[$lastCommentLineIndex]['content']);
        if ($classComment[strlen($classComment) - 1] !== '.') {
            $fix = $phpcsFile->addFixableError(
                'The point must be placed at the end of comment.',
                $lastCommentLineIndex,
                'WrongClassDeclaration'
            );
            if (true === $fix) {
                $phpcsFile->fixer->beginChangeset();
                $phpcsFile->fixer->replaceToken($lastCommentLineIndex, $classComment . '.');
                $phpcsFile->fixer->endChangeset();
            }
        }

        return true;
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param array                       $tokens
     *
     * @return string
     */
    private function getNamespace(File $phpcsFile, array $tokens): string
    {
        $index = $phpcsFile->findNext(T_NAMESPACE, 0);
        $end   = $phpcsFile->findEndOfStatement($index);
        $ns    = '';
        for ($i = $index + 2; $i < $end; $i++) {
            $ns .= $tokens[$i]['content'];
        }

        return $ns;
    }
}//end class
