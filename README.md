# PHP Coding Helper

Package to support your web development

## Features

- Uploaded file validator
- Malicious content validator in zip files
- Explode array while clearing its contents
- And stay tuned for other interesting features

## Installation

```composer require riyantobudi/support```

## Usage

First of all, import the package first to use it
```use Riyantobudi\Support\FileValidator;```

then use the available functions, for example
```FileValidator::validateFileEligibilityToUpload($fileObj, ['image'], true);```

## Thank you for using

Other interesting features will follow, stay tuned