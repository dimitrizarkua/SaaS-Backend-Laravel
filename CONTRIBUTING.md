## IDE
Recommended IDE - [PHPStorm](https://www.jetbrains.com/phpstorm/) (proprietary)

## Must have settings

### Editor -> General
- Strip trailing spaces on save for "Modified lines".
- Ensure line feed at file end of Save

### Editor -> Inspections -> PHP

**Code smell**
- Check Too many parameters in function declaration

**Code style**
- Import scheme from misc/steamatic.xml
- Uncheck Unnecessary fully qualified name (we need this for doc blocks)

**General**
- Static method called as dynamic

**PHPDoc**
- Missing PHPDoc comment

**Probable bugs**
- Assignment in condition

### Editor -> CodeStyle -> PHP

**PHPDOC**
- Blank line before the first tag

**Generated Doc Blocks**
- Use fully-qualified class names

**Other**
- Blank line before return statement
- Array Declaration Style: Force short declaration style
- Array Declaration Style: Add a comma after last element in multiline array

## Code style

[PSR-2](https://www.php-fig.org/psr/psr-2/) is the coding standard used on the project.
