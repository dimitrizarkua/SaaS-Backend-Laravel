# Check code standards with php code sniffer

For checking code style [Code sniffer](https://github.com/squizlabs/PHP_CodeSniffer) is being using.
It also allows you to automatically fix some errors. This check included into CI pipeline.

##Installation

The package included as dev requirements in composer.json and it already exists in `vendor/bin` folder.
If it needed you may install it globally on local machine:
```bash
#Install package
composer global require squizlabs/php_codesniffer
#Add steamatic code standat to cs config
phpcs --config-set installed_paths misc/CodeStandard/
```
 
##Usage

###Check code style

- Docker usage:
```bash
docker-compose run backend-interpreter composer cs {fileName|SomeFolder} 
```
- Local usage
```bash
phpcs --colors --standard=CodeStandard -p {fileName|SomeFolder}
```

### Automatically fix errors
- Docker usage:
```bash
 docker-compose run backend-interpreter composer cbf {fileName|SomeFolder}
 ``` 
 - Local usage
 ```bash
 phpcbf --colors --standard=CodeStandard -p {fileName|SomeFolder}
 ```
 
## Tips
- It would be easy use bash alias (if you are using local installation)
```bash
alias cs='phpcs --colors --standard=CodeStandard -p'
alias cbf=s'phpcbf --colors --standard=CodeStandard -p'
```

- In order to inspect only VCS changed files before **commit** you may use this script (this tip unfortunately doesn't work with docker-compose)
```bash
git ls-files -m | xargs phpcs --colors --standard=CodeStandard -p
``` 

- In order to inspect only VCS changed files before **MR** you may use this script (this tip unfortunately doesn't work with docker-compose)
```bash
git diff --name-only --diff-filter=ACMR dev | xargs phpcs --colors --standard=CodeStandard -p
``` 
